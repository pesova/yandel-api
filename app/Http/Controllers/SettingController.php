<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ContactSupportException;
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
        
        $req = $this->settingService->getLegal();

        return success( 'success', $req );
    }

    public function contactSupport(Request $request){

        if (empty($request['subject']) || empty($request['message'])) {
            
            throw new ContactSupportException('No Subject or message specified');
        }
        try{
            $req = $this->settingService->contactSupport($request['subject'], $request['message']);            
        }
        catch(\Throwable $e){
            throw $e;
        }
        $message = 'Thanks for contacting Us, We will get back to you';
        
        return success( $req['messages'] ?? $message, $req['data'] ?? $req );
    }
    
}
