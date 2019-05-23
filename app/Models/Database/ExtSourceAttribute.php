<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class ExtSourceAttribute extends Model
{
    //
    protected $table = 'ext_sources_attributes';
    
    protected $fillable = [ 'name', 'core_name', 'display_name' ];
    
    public function extSource() {
        return $this->belongsTo('App\Models\Database\ExtSource', 'ext_source_id');
    }
    
    public function values() {
        return $this->hasMany('App\Models\Database\UserExtAttribute', 'ext_source_attribute_id');
    }
}
