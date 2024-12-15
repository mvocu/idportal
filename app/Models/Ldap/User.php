<?php

namespace App\Models\Ldap;

use LdapRecord\Models\Entry;

class User extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'cuniPerson'
    ];
    
    public function toFlatArray() {
        $data = $this->getAttributes();
        $result = [];
        
        foreach($data as $key => $value) {
            if(is_array($value) && count($value) == 1) {
                $result[$key] = $value[0];
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
