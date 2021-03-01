<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Setting;
use App\Events\ContactSupport;
use App\Exceptions\ContactSupportException;
use App\Contracts\SettingServiceInterface;



class SettingService extends BaseService implements SettingServiceInterface{


    private $setting;

    public function __construct( 
        Setting $setting
    )
    {
        $this->model = $setting;
    }

    public function getLegal(){
        $setting = Setting::first();

        return $setting;
    }

    public function contactSupport(array $request){
        $user = Auth::user();
        try {
            
            if (empty($request['subject']) || empty($request['message'])) {
                
                throw new UserServiceException('No Subject or message specified');
            }        
            
            if ($user){
                
                // trigger contact support event
                event( new ContactSupport() );
    
                return ["subject"=> $request['subject'], "message"=>$request['message'] ];
               
            }
        } catch (\Throwable $e) {
            handleThrowable($e);
        }
    }


}

