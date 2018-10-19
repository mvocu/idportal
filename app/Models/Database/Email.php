<?php

namespace App\Models\Database;

class Email extends Contact
{
    //
    protected $fillable = ['email'];

    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_EMAIL;
    }

}
