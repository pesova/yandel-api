<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Exceptions\UserServiceException;
use App\Events\UserUpdated;
use App\Contracts\UserServiceInterface;


class UserService extends BaseService implements UserServiceInterface{
    private $user, $logChannel = 'auth';

    const USER_NOT_FOUND = "User not found";
    
    public function __construct( 
        User $user
    )
    {
        $this->model = $user;
    }

    /**
     * Returns a selected user
     *
     * @param int|string $user
     */
    public function find($user = null, bool $chainable = false): User
    {
        if(is_numeric($user)) $user = (int) $user;

        if(is_int($user) || is_string($user)){
            $user = $this->model
                    ->when(is_int($user), function($query) use ($user){
                        $query->where('id', $user);
                    })
                    ->when(!is_int($user) && is_string($user), function($query) use ($user){
                        $query->whereColumns(['username', 'email', 'phone'], $user);
                    })
                    ->firstOrFail();
        }

        if(!$user instanceof User) throw new UserServiceException(self::USER_NOT_FOUND);

        return $user;
    }

    public function updateUser( array $request){
        $user = $this->find(Auth::user()->id)->firstOrFail();

        DB::beginTransaction();
        try {
            if (empty($request)) throw new UserServiceException('No Parameter specified');
            
            $user->update($request);
            
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollbacl();
            handleThrowable($e);

            throw $e;
        }

        // trigger user updated event
        event( new UserUpdated($user) );
        return $user;
    }
    
    public function updateProfilePicture( array $request ){
        $user = $this->find(Auth::user()->id)->firstOrFail();

        DB::beginTransaction();
        try {
            
            if ( empty($request['avatar']) ) throw new UserServiceException('Image file Not found');

            $image = $request['avatar'];
            $fileNameToStore = saveImage($image, encrypt($user->email), 'profile_pics');        
            $user->avatar_url = $fileNameToStore;

            $user->save();

            DB::commit();
        } catch(\Throwable $e){
            DB::rollback();
            handleThrowable($e, $this->logChannel);
            throw $e;
        }
        
        return $user;
    }
    
    public function getUserInfo( $user_id = null ){
        if($user_id && ! is_numeric($user_id))
        throw new \InvalidArgumentException(
            "user $user_id must be  numeric or integer. ".gettype($user_id).' given.'
        );

        $user = $this->find($user_id)->firstOrFail();

        return $user;
    }

}

