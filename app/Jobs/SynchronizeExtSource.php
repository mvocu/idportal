<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Database\ExtSource;

class SynchronizeExtSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $ext_source;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExtSource $es)
    {
        $this->ext_source = $es;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $s_mgr = resolve('App\Interfaces\SynchronizationManager');
            $s_mgr->synchronizeExtSource($this->ext_source);
        } catch (\Exception $e) {
            Log::error('Error synchronizing ext source', ['source' => $this->ext_source, 'exception' => $e ]);
        }
    }
}
