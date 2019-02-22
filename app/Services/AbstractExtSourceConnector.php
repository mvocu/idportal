<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ExtUserResource;

abstract class AbstractExtSourceConnector implements ExtSourceConnector
{
    protected $lastStatus;
    
    // map attributes from core names to ext source names
    public function getExtUserResource(ExtSource $source, UserResource $user) {
        
        $result = [];
        
        foreach($source->attributes as $attrDesc) {
            $name = $attrDesc->core_name;
            $user_data = $user->toArray(null);
            if(empty($name))
                continue;
            
            $names = explode(".", $name);
            
            if (count($names) == 1) {
                // single top-level attribute
                if(array_key_exists($name, $user_data) && !empty($user_data[$name])) 
                    $result[$attrDesc->name] = $user_data[$name];
            } else {
                // attribute of some relation in the form: relation[index].name
                if (preg_match("/([a-zA-Z]*)\[(\d+)\]/", $names[0], $matches)) {
                    $attr_name = $matches[1];
                    $index = $matches[2];
                    if(!empty($user_data[$attr_name][$index][$names[1]]))
                        $result[$attrDesc->name] = $user_data[$attr_name][$index][$names[1]];
                } else {
                    if(!empty(array_get($user_data, $name)))
                        $result[$attrDesc->name] = array_get($user_data, $name);
                }
            }
        }

        return new ExtUserResource($result);
    }
    
    public function getLastStatus() {
        return $this->lastStatus;
    }
    
    protected function makeResource($data, $key) {
        $id = $data[$key];
        unset($data[$key]);
        return new ExtUserResource([ 'id' => $id, 'attributes' => $data ]);
    }
}
