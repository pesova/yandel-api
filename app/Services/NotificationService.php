<?php

namespace App\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\NotificationServiceException;
use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use Carbon\Carbon;
use Mail;



/**
 * Inherits basic CRUD functionalities from BaseService
 */
class NotificationService extends BaseService implements NotificationServiceInterface
{
    /**
     * Class constants
     */
    const INVALID_CHANNEL = 'Invalid channel.';

    /**
     * @var Notification $notification
     */
    private $notification;

    /**
     * @var string $from
     * @var string to
     * @var string $subject
     * @var array|string $message
     * @var string $attachment
     * @var string $channel
     * @var string $template
     * @var string $class
     * @var string $notifiable
     */
    private $from, $to, $subject, $message, $data, $attachment,
            $channel, $template, $notifiable, $class, $shouldQueue;

    /**
     * Inject Dependencies
     */
    public function __construct(
        Notification $notification
    )
    {
        $this->model = $notification;
    }

    /**
     * Validates that required configurations
     * for sending a notification are set
     *
     * @param array $configs[]
     * @return bool
     */
    public function validateConfig( array $params )
    {
        foreach($params as $param){

            // handle case of option params using a pipe
            $optional = explode('|', $param) ;

            if( ! empty($optional) ){
                for($i = 0; $i < count($optional); $i++){
                    if($i === count($optional)-1 &&  ! $optional[$i] ){
                        throw new NotificationServiceException(
                            "At least one of the following paramters is required: " . implode('or', $optional)
                        );
                    }
                }
            }

            if( ! $param && ! in_array($param, $optional) ) throw new NotificationServiceException(
                "$param is a required parameter."
            );
        }
        return true;
    }

    /**
     * Sets the sender of the message (if applicable)
     *
     * @param string $from
     * @return self
     */
    public function from( string $from ) :self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Sets the reciever of the message
     *
     * @param string $to
     * @return self
     */
    public function withTemplate( string $template ) :self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Sets the reciever of the message
     *
     * @param object|array $data
     * @return self
     */
    public function withData( $data ) :self
    {
        // TODO: validate for array or object data type
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the reciever of the message
     *
     * @param string $to
     * @return self
     */
    public function to( string $to ) :self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Sets the subject of the message (if applicable)
     *
     * @param string $subject
     * @return self
     */
    public function subject( string $subject ) :self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Sets the message to be sent
     *
     * @param string $message
     * @return self
     */
    public function message( $message ) :self
    {
        if( ! in_array(gettype($message), ['string', 'array']) )
            throw new NotificationServiceException(
                "Message must be of type string or array ".gettype($message)." passed."
            );

        $this->message = $message;

        return $this;
    }

    /**
     * Sets the attachement to be sent
     *
     * @param string $attachement
     * @return self
     */
    public function attach( string $attachement ) :self
    {
        $this->attachement = $attachement;

        return $this;
    }

    /**
     * Sets the css class to be tied to a notification on the frontend
     *
     * @param string $class
     * @return self
     */
    public function class( string $class ) :self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Sets the notifiable to be used for morph columns
     *
     * @param Illuminate\Database\Eloquent\Model $notifiable
     * @return self
     */
    public function notifiable( \Illuminate\Database\Eloquent\Model $notifiable ) :self
    {
        $this->notifiable = $notifiable;

        return $this;
    }

    /**
     * Stores the message on the database
     *
     * @param string $channel
     * @return self
     */
    public function save()
    {
        $user = \App\Models\User::whereEmail($this->to)->firstOrFail();

        $notification = $user->notifications()->updateOrCreate([
            "title" => $this->subject,
            "message" => $this->message,
            "class" => $this->class
        ]);

        if($this->notifiable) $notification->notifiable()->save($this->notifiable);
    }

    /**
     * Queues the message to be sent in a later time
     *
     * @param string $channel
     * @return self
     */
    public function queue()
    {
        $this->shouldQueue = true;

        return $this->send();
    }

    /**
     * Sends the message to the reciever given other configs
     *
     * @param string $channel
     * @return self
     */
    public function send()
    {
        switch ($this->channel){
            case 'sms';
                return $this->sendViaSms();

            case 'email';
                return $this->sendViaEmail();

            default:
                throw new NotificationServiceexception(self::INVALID_CHANNEL);
        }
    }

    /**
     * Set channel through which message should be sent
     *
     * @param string $channel
     * @return self
     */
    public function via( string $channel ) :self
    {
        if( strtolower($channel) === 'phone') $channel = 'sms';
        $this->channel = $channel;

        return $this;
    }

    /**
     * Sends the message via sms
     *
     * @param string $channel
     * @return self
     */
    public function sendViaSms()
    {
        $this->channel = 'sms';

        $this->validateConfig(['to', 'message']);

        // TODO: add send sms implementation
    }

    /**
     * Sends the message via email
     *
     * @param string $channel
     * @return self
     */
    public function sendViaEmail()
    {
        $this->channel = 'email';

        $this->validateConfig(['from', 'to', 'subject', 'message|template', ]);

        $messageHanlder = function( $message ){
            $message->to($this->to);
            $message->subject($this->subject);

            if( ! empty($this->from) )
                $message->from($this->from, config('app.APP_NAME'));

            if( empty($this->template) && ! empty($this->message) )
                $message->setBody($this->message);

            if($this->pdf && $this->pdfName) $message->attachData($this->pdf->output(), $this->pdfName);
        };

        $this->shouldQueue
            ? Mail::queue( $this->template ?? [], $this->data ?? [], $messageHanlder)
            : Mail::send( $this->template ?? [], $this->data ?? [], $messageHanlder);

        return true;
    }

    /**
     * Returns all saved notifications
     *
     * @param array $params
     * @return array|object|collection
     */
    public function getNotifications( array $params = null )
    {
        return auth()->user()->notifications()
                ->when( isset($params['is_read']), function($query) use ($params) {
                    $query->whereIsRead( $params['is_read'] );
                })
                ->when( $params['type'] ?? false, function($query, $type) {
                    $query->whereNotifiableType( $type );
                })
                ->paginate( $params['limit'] ?? config('custom.app.PAGE_LIMIT'));
    }

    /**
     * Changes status of a notification to read
     *
     * @param array|int $notifications
     * @return array|object|collection
     */
    public function markAsRead( $notifications )
    {
        $notifications = is_array($notifications) ? $notifications : [$notifications];
        $notifications = auth()->user()->notifications()->whereIn('id', $notifications);

        if(! $notifications->exists()) throw new ModelNotFoundException();

        $notifications->update(['is_read' => true]);

        return $notifications->get();
    }

    /**
     * Deletes single or multiple notifications
     *
     * @param array|int $notifications
     * @return null
     */
    public function deleteNotifications( $notifications = null )
    {
        if($notifications && ! is_array($notifications) && ! is_numeric($notifications))
        throw new \InvalidArgumentException(
            "Notifications must be of type array, numeric or integer. ".gettype($notifications).' given.'
        );

        if(! is_array($notifications)) $notifications =  [$notifications];

        $notifications = auth()->user()->notifications()
                        ->when($notifications ?? false, function($query, $notifications){
                            $query->whereIn('id', $notifications);
                        })
                        ->delete();

        return;
    }
}
