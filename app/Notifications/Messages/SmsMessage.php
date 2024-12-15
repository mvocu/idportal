<?php
namespace App\Notifications\Messages;

use Illuminate\Notifications\Notification;

class SmsMessage extends Notification
{
    public $content;
    
    public function __construct($content) {
        $this->content = $content;
    }
    
}

