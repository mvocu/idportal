<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\Notifications\Messages\SmsMessage;

class AwegSmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('sms', $notification)) {
            return;
        }
        
        $message = $notification->toSms($notifiable);
        
        if (is_string($message)) {
            $message = new SmsMessage($message);
        }
        
        return($this->callAweg($this->formatUrl($to, $message)));
    }

    protected function formatUrl($phone, $message) {
        $config = Config::get('aweg');
        
        // service url
        $url = $config['url'];
        if(!Str::endsWith($url, '/')) $url .= '/';
        // authentication
        $url .= '?' . 'auth=' . $config['login'] . ':' . $config['password'];
        // recipient
        $url .= '&' . 'receiver=' . $phone;
        // text
        $url .= '&' . 'smstext=' . urlencode($message->content);
        
        return $url;
    }
    
    protected function callAweg($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $url);
        $output = curl_exec($ch);
        curl_close($ch);
        if(preg_match("/(\d+)/", $output, $matches)) {
            $code = (int)$matches[1];
            return $code < 300;
        } else {
            return false;
        }
    }
}
