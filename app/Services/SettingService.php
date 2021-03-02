<?php 

namespace App\Services;

use DB;
use Auth;
use App\Models\User;
use App\Models\Setting;
use App\Events\ContactSupport;
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
        // $setting = Setting::first()->pluck('terms');
        $setting = Setting::select('terms','privacy')->first();

        return $setting;
    }

    public function contactSupport($subject, $message){
        $user = Auth::user();
            
        // trigger contact support event
        event( new ContactSupport($subject, $message) );

        return ["subject"=> $message, "message"=>$message ];
        
    }


}

