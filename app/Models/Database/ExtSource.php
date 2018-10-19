<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class ExtSource extends Model
{
    //
    
    protected $table = "ext_sources";
    
    protected $fillable = ['name', 'type', 'trust_level'];
    
    public function users() {
        return $this->hasMany('App\Models\Database\UserExt', 'ext_source_id');
    }
    
    public function attributes() {
        return $this->hasMany('App\Models\Database\ExtSourceAttribute', 'ext_source_id');
    }
}
