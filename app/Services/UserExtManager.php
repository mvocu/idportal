<?php

namespace App\Services;

use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Support\Facades\DB;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Models\Database\UserExtAttribute;
use App\Http\Resources\ExtUserResource;
use App\Http\Resources\UserResource;
use App\Events\UserExtUpdatedEvent;
use App\Events\UserExtCreatedEvent;

class UserExtManager implements UserExtManagerInterface
{
    protected $attrDefs;
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::getUserResource()
     */
    public function getUserResource(UserExt $user_ext): UserResource
    {
        return new UserResource($user_ext);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::createUserWithAttributes()
     */
    public function createUserWithAttributes(ExtSource $source, ExtUserResource $data): UserExt
    {
        $user = new UserExt();
        $user->fill([ 'login' => $data->getId(), 'active' => $data->isActive() ]);
        $user->extSource()->associate($source);

        $attrDefs = $this->getAttrDefs($source);
        
        DB::transaction(function() use ($source, $data, $user, $attrDefs) {
            
            $user->save();
                
            foreach($data->toArray(null) as $name => $values) {
                if (! array_key_exists($name, $attrDefs))
                    continue;
                $attrDef = $attrDefs[$name];
                if (! is_array($values)) {
                    $values = [
                        $values
                    ];
                }
                foreach ($values as $value) {
                    $value = trim($value);
                    if (empty($value))
                        continue;
                    $attr = new UserExtAttribute([
                        'value' => $value
                    ]);
                    $attr->attrDesc()->associate($attrDef);
                    $user->attributes()->save($attr);
                }
            }
                
        });
        
        if($data->isActive()) event(new UserExtCreatedEvent($user));
        
        return $user; 
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::updateUserWithAttributes()
     */
    public function updateUserWithAttributes(\App\Models\Database\ExtSource $source, UserExt $user, ExtUserResource $data): UserExt
    {
        $attrDefs = $this->getAttrDefs($source);
        $modified = false;
                
        DB::transaction(function() use ($source, $user, $data, $attrDefs, &$modified) {
                
            // remove all current attributes that are not in new data
            $present_attr_ids = array_values(array_map(function ($val) {
                                    return $val->id;
                                }, array_intersect_key($attrDefs, $data->toArray(null))));
            $deleted = $user->attributes()
                ->whereNotIn('ext_source_attribute_id', $present_attr_ids)
                ->delete();
            $modified |= $deleted > 0;
                
            // go through the new data, possibly add/modify attributes
            foreach($data->toArray(null) as $name => $values) {
                if (! array_key_exists($name, $attrDefs))
                    continue;
                $attrDef = $attrDefs[$name];
                if (!is_array($values)) {
                    $values = [
                        $values
                    ];
                }
                $values = array_map(function($val) { return trim($val); }, $values);
                // remove values not present anymore
                $deleted = $user->attributes()
                    ->where('ext_source_attribute_id', $attrDef->id)
                    ->whereNotIn('value', $values)
                    ->delete();
                $modified |= $deleted > 0;
                
                // add values not present yet
                foreach(collect($values)->diff($user->attributes()
                                                ->where('ext_source_attribute_id', $attrDef->id)
                                                ->get()->pluck('value')) as $value) {
                    $attr = new UserExtAttribute([
                        'value' => $value
                    ]);
                    $attr->attrDesc()->associate($attrDef);
                    $user->attributes()->save($attr);
                    $modified = true;
                }
            }
        });

        if ($modified) {
            event(new UserExtUpdatedEvent($user->refresh()));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::syncUsers()
     */
    public function syncUsers(ExtSource $source, \Illuminate\Support\Collection $users)
    {
        $result = array();
        foreach($users as $user_resource) {
            $user = $this->getUser($source, $user_resource);
            if($user == null) {
                $result[] = $this->createUserWithAttributes($source, $user_resource);
            } else {
                $result[] = $this->updateUserWithAttributes($source, $user, $user_resource);
            }
        }
        return collect($result);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::getUser()
     */
    public function getUser(ExtSource $source, ExtUserResource $data): ?UserExt
    {
        return UserExt::where('login', $data->getId())->where('ext_source_id', $source->id)->first();
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

