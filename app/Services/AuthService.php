<?php

namespace App\Services;

use DB;
use Auth;
use Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Events\LoginSuccess;
use App\Models\PasswordReset;
use App\Events\PinUpdated;
use App\Events\UserRegistered;
use App\Events\PasswordResetRequest;
use App\Events\PasswordResetSuccess;
use App\Contracts\AuthServiceInterface;
use App\Contracts\TokenServiceInterface;
use App\Exceptions\AuthServiceException;
use App\Exceptions\TokenServiceException;

// for passport login implementation overide
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthService extends BaseService implements AuthServiceInterface
{
    use AuthenticatesUsers;

    /**
     * @var App\Models\User $user
     * @var TokenService $tokenService
     * @var string $logChannel
     */
    private $user, $tokenService, $logChannel = 'auth';

    /**
     * Inject Dependencies
     */
    public function __construct( 
        User $user, 
        TokenService $tokenService
    )
    {
        $this->model = $user;
        $this->tokenService = $tokenService;
    }

    /**
     * 
     * Register a new user
     * 
     * @param array $request
     * 
     * @return \App\Models\User
     */
    public function register(array $request)
    {
        DB::beginTransaction();
        try{
            // update other required fields and create user account
            $request['password'] = Hash::make( $request['password'] );

            $user = $this->create( $request );

            // generate auth token for user
            $token = $user->createToken('Password Grant Client');

            DB::commit();

        }
        catch(\Throwable $e){
            DB::rollback();
            throw new AuthServiceException('Registeration failed', $request, $e, 400);
        }

        // trigger user registered event
        event( new UserRegistered($user) );

        return ["user"=> $user, "access_token"=>$token->accessToken, "expires_in"=>$token->token['expires_at']];
    }

    /**
     * Login an existing user
     * 
     * @param array $request
     * 
     * @return array
     */
    public function login($request)
    {
        // check if user has reached the max number of login attempts
        if (config('custom.app.THROTTLE_LOGIN') && $this->hasTooManyLoginAttempts($request)){
            $this->fireLockoutEvent($request);
            throw new AuthServiceException("Too many login attempts. Please retry after 60 seconds");
        }

        // confirm user credetials is valid
        $fieldType = filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if(!Auth::attempt([
            $fieldType=>$request->identifier, 
            "password"=>$request->password
        ])){
            $this->incrementLoginAttempts($request);
            throw new AuthServiceException("Invalid login credentials. Please try again.");
        }

        // revoke all previously active tokens
        $this->clearSession( Auth::user() );
        
        // authentication passed so reset failed login attemps
        $this->clearLoginAttempts($request);

        // $oauthRequest = Request::create('/oauth/token', 'POST', [
        //     'grant_type' => 'password',
        //     'client_id' => $request->client_id,
        //     'client_secret' => $request->client_secret,
        //     'username' => $request->identifier,
        //     'password' => $request->password,
        //     'scope' => '*',
        // ]);
        
        $token = Auth::user()->createToken('Password Grant Client');
        
        // since we are creating a mock request, set the origin of the mock request to match that of the original
        // request. the logic to determine whether this origin is allowed is handled by the CORS middleware
        // $oauthRequest->headers->add(['Origin' => $request->headers->get('Origin') ?? '']);

        // $response = app()->handle($oauthRequest);
        

        // trigger login success event
        event( new LoginSuccess(Auth::user(), $request->header('user-agent')) );

        return ["access_token"=>$token->accessToken, "expires_in"=>$token->token['expires_at']];
        

        // return array_merge(
        //     ['statusCode'=>$response->getStatusCode()],
        //     json_decode($response->getContent(), true)
        // );
    }

    /**
     * Logout a user and invalidate access tokens
     * 
     * @return void
     */
    public function logout():void
    {
        DB::beginTransaction();
        try{
            $accessToken = Auth::user()->token();
            DB::table('oauth_refresh_tokens')
                ->where('access_token_id', $accessToken->id)
                ->update([
                    'revoked' => true
                ]);
    
            $this->clearSession(Auth::user());
            $accessToken->revoke();
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            \Log::channel($this->logChannel)->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Clear all user login sessions
     *
     * @return boolean
     */
    public function clearSession($user = null)
    {
        $user = $user ?? Auth::user();
        DB::table('oauth_access_tokens AS OAT')
            ->join('oauth_refresh_tokens AS ORT', 'OAT.id', 'ORT.access_token_id')
            ->where('OAT.user_id', $user->id)
            ->when($user, function($query, $user){
                if(isset($user->token()->id)) $query->where('OAT.id', '!=', $user->token()->id);
            })
            ->update([
                'OAT.revoked' => true,
                'ORT.revoked' => true
            ]);

        return true;
    }

    /**
     * Sends a password reset email to user
     * TODO: make this more dynamic to allow passing of phone/email
     * 
     * @param string $userId - user id
     * 
     * @return array
     */
    public function requestPasswordReset(string $identifier):array
    {
        DB::beginTransaction();
        try{
            $user = $this->findByColumns(['email', 'phone'], $identifier)->firstOrFail();
    
            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                ['email' => $user->email,  'token' => random_strings(6)]
            );
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            handleThrowable($e, 'auth');

            throw $e;
        }

        event(new PasswordResetRequest($passwordReset, $user));

        return $passwordReset->toArray();
    }

    /**
     * Checks if a password reset token is valid
     * 
     * @param string $token
     * 
     * @return array
     */
    public function findPasswordResetToken(string $token):array
    {
        $passwordReset = PasswordReset::where('token', $token)->firstOrFail();
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            throw new AuthServiceException('This token has expired!');
        }

        return ['token'=>$passwordReset->token];
    }

    /**
     * Complete password reset process by changing user password
     * 
     * @param string $token
     * @param string $newPassword
     * 
     * @return void
     */
    public function resetPassword(string $token, string $newPassword):void
    {
        DB::beginTransaction();
        try{
            $passwordReset = PasswordReset::where([['token', $token]])->firstOrFail();
            $user = $this->findByColumn('email', $passwordReset->email)->firstOrFail();
            
            $passwordReset->delete();
            $user->password = bcrypt($newPassword);
            $user->save();

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            \Log::channel($this->logChannel)->error($e->getMessage());
            throw $e;
        }
                
        event(new PasswordResetSuccess($user));
    }

    /**
     * Sets authenticated user password to the supplied string
     * 
     * TODO: consider throttling attempts here as well
     * 
     * @param string $currentPassword
     * @param string $newPassword
     * 
     * @return void
     */
    public function updatePassword(string $currentPassword, string $newPassword):void
    {
        $user = $this->find(Auth::user()->id)->firstOrFail();
        if (!Hash::check($currentPassword, $user->password)) {
            throw new AuthServiceException('Current password does not match');
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        event(new PasswordResetSuccess($user));
    }
}