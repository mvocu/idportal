<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\ChallengeStore;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SmsAuthorizationCode;
use App\Interfaces\ChallengeManager;
use Illuminate\Contracts\Notifications\Dispatcher;
use App\Notifications\Channels\SmsChannel;

class ChallengeController extends Controller
{
    
    protected $store = null;
    protected $mgr = null;
    
    public function __construct(ChallengeStore $store, ChallengeManager $mgr) {
        $this->store = $store;
        $this->mgr = $mgr;
    }

    public function createPhoneChallenge(Request $request) {
        $rules = [ 'address' => 'required|phone' ];
        if(!Auth::check()) {
            $rules['recaptcha'] = 'required|recaptcha';
        }
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return json_encode(['error' => $validator->errors()->first(), "reason" => $validator->errors() ]);
        }
        $data = $validator->validated();
        $token = $this->mgr->createToken(ChallengeManager::PHONE_CHALLENGE_KEY, $this->store);
        $phone = $this->_sanitizePhone($data['address']);
        Notification::route('sms', $phone)->notifyNow(new SmsAuthorizationCode($token));
        // we have to get the delivery status directly from SmsChannel object managed by notifications dispatcher
        $channel = app(Dispatcher::class)->driver(SmsChannel::class);
        if(!empty($channel->status_msg)) {
            return json_encode(['error' => __('Sending SMS failed: ').$channel->status_msg, "reason" => [] ]);
        }
    }
    
    public function createMailChallenge(Request $request) {
        
    }
    
    protected function _sanitizePhone($number) {
        if($number[0] == '+') {
            return $number;
        }
        if(strlen($number) == 9) {
            return "+420$number";
        }
        return "+$number";
    }
}

