<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class UserExt extends Model
{
    //

    protected $table = 'user_ext';
    
    protected $fillable = ['login', 'active'];
    
    protected $hidden = ['user', 'extSource'];
    
    public function attributes() {
        return $this->hasMany('App\Models\Database\UserExtAttribute', 'user_ext_id');    
    }
    
    public function extSource() {
        return $this->belongsTo('App\Models\Database\ExtSource', 'ext_source_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\Database\User', 'user_id');
    }

    public function parent() {
        return $this->belongsTo('App\Models\Database\UserExt', 'parent_id');
    }
    
}
