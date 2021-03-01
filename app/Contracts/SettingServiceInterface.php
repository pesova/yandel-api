<?php 

namespace App\Contracts;


interface SettingServiceInterface{

    public function getLegal();

    public function contactSupport(array $params);

}