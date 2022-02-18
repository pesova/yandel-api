<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Contracts\UserServiceInterface as UserService;


class UserController extends Controller
{
     /**
     * @var UserService
     */
    private $userService;

    /**
     * Inject Dependencies
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateUser(Request $request)
    {
        $req = $this->userService->updateUser(
            $request->only(['email', 'phone', 'bvn']),
        );

        return success( $req['message'] ?? 'User Updated Successfully', $req['data'] ?? $req );
    }

    public function updateProfilePicture( Request $request ){
        $req = $this->userService->updateProfilePicture(
            $request->all(),
        );

        return success( $req['message'] ?? 'You successfully changed your profile pics', $req['data'] ?? $req );
    }
    
    public function getUserInfo($user_id = null){
        try{
            $user_id = $user_id ?? auth()->user()->id;
            $req = $this->userService->getUserInfo( $user_id );
            
        }
        catch(\Throwable $e){
            throw $e;
        }

        return success( 'success', $req );
    }
}
