<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    protected $table = 'user';
    
    protected $fillable = [ 'first_name', 'last_name', 'middle_name', 'title_before', 'title_after', 'birth_date',
        'birth_code', 'gender', 'country' ];
    
    public function birthPlace() {
        return $this->belongsTo('App\Models\Database\Contact', 'birth_place_id');
    }
    
    public function residency() {
        return $this->belongsTo('App\Models\Database\Contact', 'residency_id');
    }
    
    public function address() {
        return $this->belongsTo('App\Models\Database\Contact', 'address_id');
    }
    
    public function addressTmp() {
        return $this->belongsTo('App\Models\Database\Contact', 'address_tmp_id');
    }
    
    public function contacts() {
        return $this->hasMany('App\Models\Database\Contact', 'user_id');
    }
}
