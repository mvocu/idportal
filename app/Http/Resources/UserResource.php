<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use App\Models\Database\Contact;
use App\Models\Database\UserExt;
use App\Models\Database\User;

class UserResource extends JsonResource
{
    protected $parent;
    
    /**
     * Transform the resource into an array:
     *  - keys are names of the core attributes (as per ext_sources_attributes.core_name)
     *  - values are assigned from the respective resource properties:
     *      - for UserExt resource the mapping is done by ext_sources_attributes name -> core_name
     *      - for User the mapping is taken as is from the object properties
     *      - for arrays the mapping is taken as is 
     *
     * @param  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource instanceof UserExt) {
            $result = array();
            
            foreach($this->resource->attributes as $attr) {
                $name = $attr->attrDesc->core_name;
                $value = trim($attr->value);
                if(empty($name) || empty($value))
                    continue;
                $this->_mapAttribute($name, $value, $result);
            }
            $result['parent'] = $this->parent;
            return $result;
        } elseif ($this->resource instanceof User) {
            $this->resource->load('phones', 'emails', 'addresses', 'birthPlace', 'residency', 'address', 'addressTmp', 
                'dataBox', 'uris', 'accounts');
            return parent::toArray($request);
        } else {
            return parent::toArray($request);
        }
    }


    /**
     * For a given User resource, return mapping of attr_name => [ values ] 
     * for attribute definitions passed in parameter $attrDefs, belonging to particular ExtSource,
     * where keys are names of external attributes and values are taken from the database model.
     * 
     * This is 'inverse' to the toArray(), which takes ExtUser and produces core attributes
     * 
     * @param array $attrDefs
     * @return array
     */
    public function getExtAttributes($attrDefs)
    {
        $attributes = [];
        if($this->resource instanceof User) {
            $data = $this->toArray(null); 
            foreach($attrDefs as $attr_def) {
                $names = explode(".", $attr_def->core_name);
                if(count($names) == 1) {
                    if(array_key_exists($attr_def->core_name, $data)) {
                        $attributes[$attr_def->name] = $data[$attr_def->core_name];
                    }
                } else {
                    if(preg_match("/([_a-zA-Z]*)\[\d*\]/", $names[0], $matches)) {
                        $attr_name = $matches[1];
                        if(array_key_exists($attr_name, $data)) {
                            $attributes[$attr_def->name] = Arr::pluck($data[$attr_name], $names[1]);
                        }
                    }
                }
            }
        }
        return $attributes;
    }

    public function setParent($data) 
    {
        $result = [];
        foreach($data as $attr) {
            $this->_mapAttribute($attr['core_name'], $attr['value'], $result);
        }
        $this->parent = $result;
    }
    
    protected function _mapAttribute($name, $value, &$result)
    {
        $names = explode(".", $name);
        if (count($names) == 1) {
            // single top-level attribute
            $result[$name] = $value;
        } else {
            if($names[1] == 'phone')
                $value = $this->_normalizePhone($value);
                // attribute of some relation in the form: relation[index].name
                if (preg_match("/([_a-zA-Z]*)\[(\d+)\]/", $names[0], $matches)) {
                    // hasMany relation: get relation name and index
                    $attr_name = $matches[1];
                    $index = $matches[2];
                    if (in_array($attr_name, Contact::$contactTypes)) {
                        // if it is a known contact relation, store the value in sub-subarray
                        $result[$attr_name][$index][$names[1]] = $value;
                    }
                    // attribute of some relation in the form: relation[].name
                } elseif (preg_match("/([_a-zA-Z]*)\[\]/", $names[0], $matches)) {
                    // hasMany relation with one multivalued attribute
                    $attr_name = $matches[1];
                    if(in_array($attr_name, Contact::$contactTypes)) {
                        // known contact relation, add the value into sub-subarray
                        if(empty($result[$attr_name])) {
                            $result[$attr_name] = array();
                        }
                        $result[$attr_name][] = [ $names[1] => $value ];
                    }
                } else {
                    // hasOne relation: store the value in subarray
                    $result[$names[0]][$names[1]] = $value;
                }
        }
    }
    
    protected function _normalizePhone($value) 
    {
        $contact = new Contact();
        $contact->setAttribute('phone', $value);
        return $contact->phone;
    }
}
