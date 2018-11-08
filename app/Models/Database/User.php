<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class User extends Model
{
    //
    protected $table = 'user';
    
    protected $fillable = [ 'first_name', 'last_name', 'middle_name', 'title_before', 'title_after', 'birth_date',
        'birth_code', 'gender', 'country' ];
    
    protected $dates = ['birth_date'];
    
    public function setFirstNameAttribute($value) {
        $this->attributes['first_name'] = Str::title($value);    
    }
    
    public function setLastNameAttribute($value) {
        $this->attributes['last_name'] = Str::title($value);    
    }
    
    public function setMiddleNameAtribute($value) {
        $this->attributes['middle_name'] = Str::title($value);
    }
    
    public function setBirthDateAttribute($value) {
        $this->attributes['birth_date'] = new Carbon($value);    
    }
    
    public function birthPlace() {
        return $this->belongsTo('App\Models\Database\Address', 'birth_place_id');
    }
    
    public function residency() {
        return $this->belongsTo('App\Models\Database\Address', 'residency_id');
    }
    
    public function address() {
        return $this->belongsTo('App\Models\Database\Address', 'address_id');
    }
    
    public function addressTmp() {
        return $this->belongsTo('App\Models\Database\Address', 'address_tmp_id');
    }
    
    public function contacts() {
        return $this->hasMany('App\Models\Database\Contact', 'user_id');
    }
    
    public function phones() {
        return $this->hasMany('App\Models\Database\Phone', 'user_id')->where('type', Contact::TYPE_PHONE);
    }

    public function emails() {
        return $this->hasMany('App\Models\Database\Email', 'user_id')->where('type', Contact::TYPE_EMAIL);
    }

    public function bankAccounts() {
        return $this->hasMany('App\Models\Database\BankAccount', 'user_id')->where('type', Contact::TYPE_BANK);
    }

    public function dataBox() {
        return $this->hasMany('App\Models\Database\DataBox', 'user_id')->where('type', Contact::TYPE_DATABOX);
    }

    public function uris() {
        return $this->hasMany('App\Models\Database\Uri', 'user_id')->where('type', Contact::TYPE_URI);
    }

    public function addresses() {
        return $this->hasMany('App\Models\Database\Address', 'user_id')->where('type', Contact::TYPE_ADDRESS);
    }
    
    public function accounts() {
        return $this->hasMany('App\Models\Database\UserExt', 'user_id');
    }
    
    public function createdBy() {
        return $this->belongsTo('App\Models\Database\UserExt', 'created_by');
    }
    
    public function modifiedBy() {
        return $this->belongsTo('App\Models\Database\UserExt', 'modified_by');
    }
}
