<?php
namespace App\Models\Cas;

use App\Interfaces\CasServer;
use Illuminate\Support\Facades\App;

abstract class Model
{
    protected static $booted = false;
    
    protected static CasServer $cas;
    
    protected static function boot() {
        self::$cas = App::make(CasServer::class);
        self::$booted = true;
    }
}

