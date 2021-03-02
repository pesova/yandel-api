<?php 

namespace App\Contracts;


interface SettingServiceInterface{

    public function getLegal();

    public function contactSupport(string $subject, string $message);

}