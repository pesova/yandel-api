<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Contracts\NotificationServiceInterface as NotificationService;


class NotificationController extends Controller
{
    /**
     * @var TransactionService $transactionService
     */
    private $notificationService;

    /**
     * Inject Dependencies
     */
    public function __construct( 
        NotificationService $notificationService
    )
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Returns all saved notifications
     */
    public function getNotifications( Request $request )
    {
        DB::beginTransaction();
        try{
            $req = $this->notificationService->getNotifications( $request->all() );
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success( 'success', $req );
    }

    /**
     * Changes status of a notification to read
     */
    public function markAsRead( Request $request )
    {
        DB::beginTransaction();
        try{
            $req = $this->notificationService->markAsRead( $request->notifications );
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success( 'success', $req );
    }

    /**
     * Deletes single or multiple notifications
     */
    public function deleteNotifications( Request $request, $notification = null )
    {
        DB::beginTransaction();
        try{
            $req = $this->notificationService->deleteNotifications( 
                $notification ??  $request->notifications ?? null
            );
            
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }

        return success( 'success', $req );
    }
}
