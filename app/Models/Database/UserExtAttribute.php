<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class UserExtAttribute extends Model
{
    //

    protected $table = 'user_ext_attributes';
    
    protected $fillable = ['value'];

    public function user() {
        return $this->belongsTo('App\Models\Database\UserExt', 'user_ext_id');
    }
    
    public function attrDesc() {
        return $this->belongsTo('App\Models\Database\ExtSourceAttribute', 'ext_source_attribute_id');
    }
}
