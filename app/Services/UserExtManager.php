<?php

namespace App\Services;

use App\Models\Database\Contact;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Support\Facades\DB;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Models\Database\UserExtAttribute;

class UserExtManager implements UserExtManagerInterface
{
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::extractUserWithAttributes()
     */
    public function extractUserWithAttributes(\App\Models\Database\UserExt $user_ext): array
    {
        $result = array();
        
        foreach($user_ext->attributes as $attr) {
            $name = $attr->attrDesc->core_name;
            $names = explode(".", $name);

            if(count($names) == 1) {
                $result[$name] = $attr->value;
            } else {
                if(preg_match("/([a-zA-Z]*)\[(\d+)\]/", $names[0], $matches)) {
                    $attr_name = $matches[1];
                    $index = $matches[2];
                    if(in_array($attr_name, Contact::$contactTypes)) {
                        $result[$attr_name][$index][$names[1]] = $attr->value;
                    }
                } else {
                    $result[$names[0]][$names[1]] = $attr->value;
                }
            }
        }
        
        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::createUserWithAttributes()
     */
    public function createUserWithAttributes(ExtSource $source, array $data): UserExt
    {
        $user = new UserExt();
        $user->fill($data);
        $user->extSource()->associate($source);
        $attrDefs = array();
        
        foreach($source->attributes as $attrDef) {
            $attrDefs[$attrDef->name] = $attrDef;    
        }
        
        DB::transaction(function() use ($source, $data, $user, $attrDefs) {
            
            $user->save();
            
            if(array_key_exists('attributes', $data) && is_array($data['attributes'])) {
                foreach($data['attributes'] as $name => $value) {
                    if(array_key_exists($name, $attrDefs)) {
                        $attrDef = $attrDefs[$name];    
                        $attr = new UserExtAttribute(['value' => $value]);
                        $attr->attrDesc()->associate($attrDef);
                        $user->attributes()->save($attr);
                    }
                }
            }
                
        });
        return $user; 
    }


            
}

