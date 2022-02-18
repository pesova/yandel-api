<?php

namespace App\Contracts;
use App\Models\User;

interface AuthServiceInterface 
{
    /**
     * Register a new user
     * 
     * @param array $request
     * 
     * @return \App\Models\User
     */
    public function register(array $request);

    /**
     * Login an existing user
     * 
     * @param array $request
     * 
     * @return array
     */
    public function login(array $request);

    /**
     * Logout a user and invalidate access tokens
     * 
     * @return void
     */
    public function logout():void;

    /**
     * Sends a password reset email to user
     * 
     * @param string $userId - user id
     * 
     * @return array
     */
    public function requestPasswordReset(string $email):array;

    /**
     * Checks if a password reset token is valid
     * 
     * @param string $token
     * 
     * @return array
     */
    public function findPasswordResetToken(string $token):array;

    /**
     * Complete password reset process by changing user password
     * 
     * @param string $token
     * @param string $newPassword
     * 
     * @return void
     */
    public function resetPassword(string $token, string $newPassword):void;

    /**
     * Sets authenticated user password to the supplied string
     * 
     * @param string $currentPassword
     * @param string $newPassword
     * 
     * @return void
     */
    public function updatePassword(string $currentPassword, string $newPassword):void;

}