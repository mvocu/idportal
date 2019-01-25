<?php

namespace App\Services;

use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Support\Facades\DB;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Models\Database\UserExtAttribute;
use App\Http\Resources\UserResource;

class UserExtManager implements UserExtManagerInterface
{
    protected $attrDefs;
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::extractUserWithAttributes()
     */
    public function getUserResource(UserExt $user_ext): array
    {
        return new UserResource($user_ext);
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
            
            // TODO: handle multi-valued attributes
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

