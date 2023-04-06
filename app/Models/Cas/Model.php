<?php
namespace App\Models\Cas;

use App\Interfaces\CasServer;
use Illuminate\Support\Facades\App;
use stdClass;

abstract class Model
{
    protected static $booted = false;
    
    protected static $cas;
    
    protected static function boot() {
        self::$cas = App::make(CasServer::class);
        self::$booted = true;
    }
    
    public static function from($source) {
        $instance = new static;
        if($source instanceof stdClass) {
            $instance->fill(get_object_vars($source));
        } else {
            $instance->fill($source);
        }
        return $instance;
    }
    
    public function fill(array $attributes) {
        foreach(get_object_vars($this) as $name => $dummy) {
            $this->$name = array_key_exists($name, $attributes) ? $attributes[$name] : null;
        }
    }
    
}

