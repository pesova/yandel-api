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

    public function __construct( 
        User $user
    )
    {
        $this->model = $user;
    }

    public function updateUser( array $request){
        $user = $this->find(Auth::user()->id)->firstOrFail();

        try {
            
            if (empty($request['name']) && empty($request['email']) && empty($request['phone']) && empty($request['bvn'])) {
                throw new UserServiceException('No Parameter specified');
            }
            if ($user && $user->update($request)) {
                // trigger user updated event
                event( new UserUpdated($user) );
    
                return ["user"=> $user];
            }else {
                throw new UserServiceException('No Parameter specified');
            } 
        } catch (\Throwable $e) {
            \Log::channel($this->logChannel)->error($e->getMessage());
            throw $e;
        }
            

    }
    
    public function updateProfilePicture( array $request ){
        $user = $this->find(Auth::user()->id)->firstOrFail();

        if ($user) {
            DB::beginTransaction();
            try {
                if ($request->file('avatar_url') !== null) {

                    $image = $request->file('avatar_url');
                    $fileName = saveImage($image, 'profile_pics');
    
                    $user->avatar_url = $fileName;

                    if ($user->save()) {
                        
                        DB::commit();
                        
                        return ["user"=> $user];
                    } else {
                        DB::rollback();
                        throw new UserServiceException('Something went wrong, try again later');
                    }

                }else {
                    throw new UserServiceException('Image file Not found');

                }

            } catch(\Throwable $e){
                DB::rollback();
                \Log::channel($this->logChannel)->error($e->getMessage());
                throw $e;
            }
        }

    }
    
    public function getUserInfo( $user_id = null ){
        if($user_id && ! is_numeric($user_id))
        throw new \InvalidArgumentException(
            "user$user_id must be  numeric or integer. ".gettype($user_id).' given.'
        );

        $user = $this->find($user_id)->firstOrFail();
        $bvn = $user->bvn;
        $user->setAttribute('userbvn', $bvn);

        return $user;
    }

}

