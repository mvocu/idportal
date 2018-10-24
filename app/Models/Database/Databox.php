<?php

namespace App\Models\Database;

class Databox extends Contact
{
    //
    protected $fillable = ['databox' ];

    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_DATABOX;
    }
    
}
