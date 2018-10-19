<?php

namespace App\Models\Database;

class Uri extends Contact
{
    //
    protected $fillable = ['uri'];
    
    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_URI;
    }

}
