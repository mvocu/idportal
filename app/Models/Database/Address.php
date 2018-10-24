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
}
