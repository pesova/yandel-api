<?php 

namespace App\Contracts;


interface UserServiceInterface{

    public function updateUser( array $params );

    public function updateProfilePicture( array $params );
    
    public function getUserInfo( $user_id = null );

}
