<?php

namespace App\Traits;

trait RemembersPassword {

    protected $password;

    /**
     * @param mixed $password
     */
    public function rememberPassword($password)
    {
        $this->password = $password;
    }
    
}

