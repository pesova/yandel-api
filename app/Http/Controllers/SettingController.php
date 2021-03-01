<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\SettingServiceInterface as SettingService;


class SettingController extends Controller
{
    /**
     * @var SettingService
     */
    private $settingService;

    /**
     * Inject Dependencies
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function getLegal(){
        try{
            $req = $this->settingService->getLegal();
            
        }
        catch(\Throwable $e){
            throw $e;
        }

        return success( 'success', $req );
    }

    public function contactSupport(Request $request){
       
        try{
            $req = $this->settingService->contactSupport(
                $request->all(),
            );            
        }
        catch(\Throwable $e){
            throw $e;
        }
        
        return success( $req['messages'] ?? 'Thanks for contacting Us, We will get back to you', $req['data'] ?? $req );
    }
    
}
