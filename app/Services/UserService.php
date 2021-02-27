<?php 

namespace App\Services;

use Auth;
use App\Models\User;
use App\Exceptions\UserServiceException;
use App\Events\UserUpdated;
use App\Contracts\UserServiceInterface;


class UserService extends BaseService implements UserServiceInterface{

    public function __construct( 
        User $user
    )
    {
        $this->model = $user;
    }

    public function updateUser( array $request){
        // dd($request['name'], Auth::user()->id);
        $user = $this->find(Auth::user()->id)->firstOrFail();
        // dd($user);

        if (isset($request['name'])) {
            $name = $request['name'];
            $user->name = $name;
        }

        if (isset($request['bvn'])) {
            $bvn = $request['bvn'];
            $user->bvn = $bvn;
        }

        if (isset($request['email'])) {
            $email = $request['email'];
            $user->email = $email;
        }

        if (isset($request['phone'])) {
            $phone = $request['phone'];
            $user->phone = $phone;
        }
        if (!empty($name)||!empty($email)||!empty($phone)||!empty($bvn)) {
            $user->save();
            // trigger user updated event
            event( new UserUpdated($user) );

        return ["user"=> $user];
        }else {
            throw new UserServiceException('No Parameter specified');
        }
     

    }
    
    public function updateProfile_picture( array $params ){
    
    }
    
    public function getUserInfo( $user_id = null ){
        if($user_id && ! is_numeric($user_id))
        throw new \InvalidArgumentException(
            "user$user_id must be  numeric or integer. ".gettype($user_id).' given.'
        );

        if($user_id);

        $user = $this->find($user_id)->firstOrFail();
        if (!empty($user->bvn)) {
            $bvn = $this->maskbvn($user->bvn);
            $user->setAttribute('userbvn', $bvn);
        }


        return $user;
    }

    public function maskbvn($number){
    
        $mask_number =  str_repeat("*", strlen($number)-4) . substr($number, -4);
        
        return $mask_number;
    }
}

