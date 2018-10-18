<?php

namespace App\Models\Database;

class BankAccount extends Contact
{
    //
    protected $fillable = [ 'bank_account' ];
    
    public function __construct($attributes = []) {
        parent::__construct($attributes);
        $this->type = Contact::TYPE_BANK;
    }

}
