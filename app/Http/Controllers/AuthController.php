<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Contracts\AuthServiceInterface as AuthService;
use App\Contracts\UserServiceInterface as UserService;
use App\Contracts\TokenServiceInterface as TokenService;
use App\Contracts\WalletServiceInterface as WalletService;

class AuthController extends Controller
{

    /**
     * @var AuthService
     * @var TokenService
     */
    private $authService, $userService, $tokenService, $walletService;

    /**
     * Inject Dependencies
     */
    public function __construct(
        AuthService $authService,
        UserService $userService,
        TokenService $tokenService,
        WalletService $walletService
    )
    {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->tokenService = $tokenService;
        $this->walletService = $walletService;
    }

    /**
     * send otp to phone number during registeration
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendRegisterationOtp( Request $request )
    {
        $req = $this->tokenService->generateToken(
            'phone',
            $request->phone,
            'registration'
        );

        // TODO: remove this before go-live
        dd(app()->environment(), $req);
        return app()->environment() !== 'production'
                ? success('OTP sent', ['token' => $req])
                : success('OTP sent');
    }

    /**
     * @OA\Post(
     * path="/register",
     *   tags={"Authentication"},
     *   summary="Register",
     *   operationId="Register",
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"phone","email", "password", "token},
     *         @OA\Property(property="phone", type="string", format="text", example="07030233033"),
     *         @OA\Property(property="email", type="string", format="email", example="test@email.com"),
     *         @OA\Property(property="password", type="string", format="password", example="secret"),
     *         @OA\Property(property="token", type="string", format="text", example="1234abc"),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Register a new user
     *
     * @param App\Http\Requests\AuthRequest
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        try{
            $this->tokenService->verifyToken(
                'phone',
                $request['phone'],
                'registration',
                $request['otp'] ?? null
            );

            $req = $this->authService->register( $request->only(['email', 'phone', 'password', 'otp']) );

            // setup user wallets
            $user = $this->userService->find($req['user']);
            $this->walletService->initializeWallets( $user );

            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }

    /**
     * @OA\Post(
     * path="/login",
     *   tags={"Authentication"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"identifier","password","client_secret", "client_id", "grant_type"},
     *         @OA\Property(property="identifier", type="string", format="text", example="test@mail.com"),
     *         @OA\Property(property="password", type="string", format="password", example="password"),
     *         @OA\Property(property="client_secret", type="string", format="password", example="z5Kyye5gjTCAM5BZDJwV5ezmOR8OCMAbkeIlYt32"),
     *         @OA\Property(property="client_id", type="string", format="text", example="2"),
     *         @OA\Property(property="grant_type", type="string", format="text", example="password"),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Login an existing user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $req = $this->authService->login( $request );
        
        return isset($req['status']) && $req['status'] === 'failed'
                ? error($req['message'] ?? 'failed', $req['statusCode'] ?? 400)
                : success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }


    /**
     * @OA\Post(
     * path="/logout",
     *   tags={"Authentication"},
     *   summary="Logout",
     *   operationId="logout",
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Logout a user and invalidate access tokens
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $req = $this->authService->logout();
        
        return success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }

    /**
     * @OA\Post(
     * path="/password/reset",
     *   tags={"Authentication"},
     *   summary="Password Reset",
     *   operationId="request-password-reset",
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"identifier"},
     *         @OA\Property(property="identifier", type="string", format="email", example="test@mail.com")
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Sends a password reset email to user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function requestPasswordReset(Request $request)
    {
        $req = $this->authService->requestPasswordReset( $request->email );
        $data = ['email' => $req['email']];
        
        // TODO: remove in production
        if(app()->environment() !== 'production') $data['token'] = $req['token'];

        return success( $req['message'] ?? 'success', $data );
    }

    /**
     * @OA\Get(
     * path="/password/reset/{token}",
     *   tags={"Authentication"},
     *   summary="Verify Reset Token",
     *   operationId="verify-reset-token",
     *
     *   @OA\Parameter(
     *      name="token",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Checks if a password reset token is valid
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function findPasswordResetToken(string $token)
    {
        $req = $this->authService->findPasswordResetToken( $token );

        return success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }

    /**
     * @OA\Put(
     * path="/password/reset",
     *   tags={"Authentication"},
     *   summary="Reset Password",
     *   operationId="reset-password",
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"email","password","password_confirmation", "token"},
     *         @OA\Property(property="email", type="string", format="email", example="test@mail.com"),
     *         @OA\Property(property="password", type="string", format="password", example="password"),
     *         @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *         @OA\Property(property="token", type="string", format="text", example="xazsw3"),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Complete password reset process by changing user password
     *
     * @param Request $request
     * @return bool
     * @throws \App\Exceptions\AppException
     */
    public function resetPassword(Request $request)
    {
        $req = $this->authService->resetPassword(
            $request->token, $request->password
        );

        return success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }

    /**
     * @OA\Put(
     * path="/password/update",
     *   tags={"Authentication"},
     *   summary="Update Password",
     *   operationId="update-password",
     *   security={ {"passport": {}}},
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"current_password","new_password", "password_confirmation"},
     *         @OA\Property(property="current_password", type="string", format="password", example="password"),
     *         @OA\Property(property="new_password", type="string", format="password", example="secret"),
     *         @OA\Property(property="password_confirmation", type="string", format="text", example="secret"),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    /**
     * Sets authenticated user password to the supplied string
     *
     * TODO: consider throttling attempts here as well
     *
     * @param string $currentPassword
     * @param string $newPassword
     *
     * @return bool
     */
    public function updatePassword(Request $request)
    {
        $req = $this->authService->updatePassword(
            $request->current_password, $request->new_password
        );

        return success( $req['message'] ?? 'success', $req['data'] ?? $req );
    }
}
