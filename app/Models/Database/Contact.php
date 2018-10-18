<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    protected $table = 'contact';
    
    protected $fillable = ['state', 'city', 'street', 'org_number', 'ev_number', 'post_number', 'email', 'phone', 
        'uri', 'databox', 'bank_account'];
    
    public function user() {
        return $this->belongsTo('App\Models\Database\User', 'user_id');
    }

}
