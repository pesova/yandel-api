<?php

namespace App\Http\Controllers;

use DB;
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
            $request->all(),
        );

        return success( $req['message'] ?? 'User Updated Successfully', $req['data'] ?? $req );
    }

    public function updateProfile_picture( Request $request ){
    
    }
    
    public function getUserInfo($user_id){
        DB::beginTransaction();
        try{
            $req = $this->userService->getUserInfo( $user_id );
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success( 'success', $req );
    }
}
