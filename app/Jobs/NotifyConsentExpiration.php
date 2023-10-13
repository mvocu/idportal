<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Interfaces\ConsentManager;
use App\Models\Database\User;
use App\Notifications\ConsentExpiresNotification;
use App\Interfaces\LdapConnector;
use Carbon\Carbon;

class NotifyConsentExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $ldap;
    protected $consent_mgr;

    public function __construct() 
    {
        $this->ldap = resolve(LdapConnector::class);
        $this->consent_mgr = resolve(ConsentManager::class);
    }
    
    public function handle()
    {
        #$users = User::all();
        $users = [ User::find(2611) ] ;
        foreach($users as $dbuser) {
            if($this->consent_mgr->expiresSoon($dbuser)) {
                $requested = new Carbon($dbuser->consent_requested);
                if($requested->diffInDays(Carbon::now(), true) < 5) {
                    continue;
                }
                $ldapuser = $this->ldap->findUser($dbuser);
                $user = new \App\User([], $ldapuser->getQuery());
                $user->setRawAttributes($ldapuser->getAttributes());
                $user->notify(new ConsentExpiresNotification($user));
                
                $this->consent_mgr->setConsentRequested($user, true);
                
                Log::info('Consent expiration warning sent to ' + $user->getEmail());
            }
        }
    }
    
}

