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
}
