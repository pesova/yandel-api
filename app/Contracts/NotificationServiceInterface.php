<?php

namespace App\Contracts;

interface NotificationServiceInterface 
{
    public function validateConfig( array $params );
    
    public function from( string $from ) :self;
    
    public function withTemplate( string $template ) :self;
    
    public function withData( $data ) :self;
    
    public function to( string $to ) :self;
    
    public function subject( string $subject ) :self;
    
    public function message( $message ) :self;
    
    public function attach( string $attachement ) :self;
    
    public function class( string $class ) :self;
    
    public function notifiable( \Illuminate\Database\Eloquent\Model $notifiable ) :self;
    
    public function save();
    
    public function queue();
    
    public function send();
    
    public function via( string $channel ) :self;
    
    public function sendViaSms();
    
    public function sendViaEmail();
    
    public function getNotifications( array $params = null );
    
    public function markAsRead( $notifications );
    
    public function deleteNotifications( $notifications = null );
    
}