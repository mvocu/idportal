<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class ExtSource extends Model
{
    //
    
    protected $table = "ext_sources";
    
    protected $fillable = ['name', 'type', 'configuration', 'trust_level', 'consent_required', 'identity_provider'];
    
    protected $hidden = [ 'users', 'attributes' ];
    
    public function users() {
        return $this->hasMany('App\Models\Database\UserExt', 'ext_source_id');
    }
    
    public function attributes() {
        return $this->hasMany('App\Models\Database\ExtSourceAttribute', 'ext_source_id');
    }
}
