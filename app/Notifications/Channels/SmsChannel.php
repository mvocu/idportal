<?php
namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class SmsChannel
{
    private $service_host;
    private $service_url;
    
    public $status = "";                                    // kod posledni operace
    public $status_msg = "";                                // hlaska od posledni operace
    public $msg_id = "";                                    // ID zpravy
    
    public function send($destination, Notification $message) {
        $to = $destination->routeNotificationFor('sms');
        $data = $message->toSms($destination);
        
        $this->status_msg = "";
       
        return $this->sendSms($to, $data->content);
    }
    
    protected function sendSms($cell, $msg) {
        $this->service_host = Config::get('sms.service_host');
        $this->service_url = Config::get('sms.service_url');
        
        if (substr($cell, 0, 1) != '+') {
            $cell = '+' . $cell;
        }
        $xml = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= '<batch id="">';
        $xml .= '<request>textSMS</request>';
        $xml .= '<recipient>' . $cell . '</recipient>';
        $xml .= '<content>' . $msg . '</content>';
        $xml .= '<refid />';
        $xml .= '<udh />';
        $xml .= '<delivery_report>20</delivery_report>';
        $xml .= '</batch>';
    
        $sess = curl_init($this->service_url . '/receiver.asp');
        curl_setopt($sess, CURLOPT_HEADER, 0);
        curl_setopt($sess, CURLOPT_HTTPHEADER, array('Host: ' . $this->service_host, 'Content-type: text/xml' ));
        curl_setopt($sess, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($sess, CURLOPT_POST, 1);
        curl_setopt($sess, CURLOPT_POSTFIELDS, "$xml");
        curl_setopt($sess, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sess, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sess, CURLOPT_SSL_VERIFYHOST, 0);
        $ret = curl_exec($sess);
        curl_close($sess);
        
        $result = @simplexml_load_string($ret);
                
        $res = false;
        if (is_object($result)) {
            $this->status = trim($result->status);
            $this->status_msg = trim($result->message);
            $this->msg_id = trim($result->smsid);
            if (trim($result->status) == "200") {
                $res = true;
            }
        } else {
            $this->status_msg = $this->service_url.":".$ret;
        }
               
        return $res;
    }
}

