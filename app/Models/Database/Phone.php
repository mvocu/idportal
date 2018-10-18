<?php

namespace App\Models\Database;

class Phone extends Contact
{
    //
    protected $fillable = ['phone'];
    
    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_PHONE;
    }

}
