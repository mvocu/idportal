<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class Address extends Contact
{
    //
    protected $fillable = ['state', 'city', 'street', 'org_number', 'ev_number', 'post_number' ];
    
    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_ADDRESS;
    }
    
    public function getHouseNumber() {
        $value = "";
        if(!empty($this->attributes['org_number'])) {
            $value = $this->attributes['org_number'];
            if(!empty($this->attributes['ev_number'])) {
                $value .= '/' . $this->attributes['ev_number'];
            }
        } elseif (!empty($this->attributes['ev_number'])) {
            $value = $this->attributes['ev_number'];
        }
        return $value;
    }
    
    public function getFormattedAddress() {
        $value = $this->attributes['street'];
        $value .= ' ' . $this->getHouseNumber();
        $value .= ', ' . $this->attributes['post_number'];
        $value .= ' ' . $this->attributes['city'];
        if(!empty($this->attributes['state'])) $value .= ', ' . $this->attributes['state'];  
        return $value;
    }
    
}
