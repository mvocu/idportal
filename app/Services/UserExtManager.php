<?php

namespace App\Services;

use App\Interfaces\UserExtManager as UserExtManagerInterface;

class UserExtManager implements UserExtManagerInterface
{
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::extractUserWithAttributes()
     */
    public function extractUserWithAttributes(\App\Models\Database\UserExt $user_ext): array
    {
        $result = array();
        $result['contacts'] = array();
        
        foreach($user_ext->attributes as $attr) {
            $name = $attr->attrDesc->core_name;
            $names = explode(".", $name);
            
            if(count($names) == 1) {
                $result[$name] = $attr->value;
            } else {
                if(preg_match("/contacts\[(\d+)\]/", $names[0], $matches)) {
                    $index = $matches[1];
                    $result['contacts'][$index][$names[1]] = $attr->value;
                    switch($names[1]) {
                        case 'phone': 
                            $result['contacts'][$index]['type'] = 'phone';
                            break;
                            
                        case 'email':
                            $result['contacts'][$index]['type'] = 'email';
                            break;
                            
                        case 'street':
                            $result['contacts'][$index]['type'] = 'address';
                            break;
                            
                        case 'databox':
                            $result['contacts'][$index]['type'] = 'databox';
                            break;
                            
                        case 'bank_account':
                            $result['contacts'][$index]['type'] = 'bank';
                            break;
                            
                        case 'uri':
                            $result['contacts'][$index]['type'] = 'uri';
                            break;
                            
                        default:
                    }
                } else {
                    $result[$names[0]][$names[1]] = $attr->value;
                    $result[$names[0]]['type'] = 'address';
                }
            }
        }
        
        return $result;
    }

        
}

