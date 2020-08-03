<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Database\UserExt;

class BuildIdentities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        try {
            $idmgr = resolve('App\Interfaces\IdentityManager');
            $eusers = UserExt::with('attributes')->whereDoesntHave('user')->get();
            foreach($eusers as $euser) {
                $result = $idmgr->buildIdentityForUser($euser);
            }
        } catch (\Exception $e) {
            Log::error('Error building identities', ['exception' => $e ]);
        }
        
    }
    
}

