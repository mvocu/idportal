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
    protected $attrDefs;
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::extractUserWithAttributes()
     */
    public function extractUserWithAttributes(UserExt $user_ext): array
    {
        $result = array();
        
        foreach($user_ext->attributes as $attr) {
            $name = $attr->attrDesc->core_name;
            $value = trim($attr->value);
            if(empty($name) || empty($value))
                continue;
            $names = explode(".", $name);

            if(count($names) == 1) {
                // single top-level attribute
                $result[$name] = $value;
            } else {
                // attribute of some relation in the form: relation[index].name
                if(preg_match("/([a-zA-Z]*)\[(\d+)\]/", $names[0], $matches)) {
                    // hasMany relation: get relation name and index
                    $attr_name = $matches[1];
                    $index = $matches[2];
                    if(in_array($attr_name, Contact::$contactTypes)) {
                        // if it is a known contact relation, store the value in sub-subarray  
                        $result[$attr_name][$index][$names[1]] = $value;
                    }
                } else {
                    // hasOne relation: store the value in subarray
                    $result[$names[0]][$names[1]] = $value;
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

        $attrDefs = $this->getAttrDefs($source);
        
        DB::transaction(function() use ($source, $data, $user, $attrDefs) {
            
            $user->save();
            
            if(array_key_exists('attributes', $data) && is_array($data['attributes'])) {
                foreach($data['attributes'] as $name => $value) {
                    $value = trim($value);
                    if(empty($value)) continue;
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

    protected function getAttrDefs(ExtSource $source) {
        if(!is_array($this->attrDefs)) {
            $this->attrDefs = array();
        }
        if(!array_key_exists($source->id, $this->attrDefs)) {
            $this->attrDefs[$source->id] = array();
            foreach($source->attributes as $attrDef) {
                $this->attrDefs[$source->id][$attrDef->name] = $attrDef;
            }
        }
        return $this->attrDefs[$source->id];
    }
            
}

