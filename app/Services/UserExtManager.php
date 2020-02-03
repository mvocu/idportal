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
use App\Events\UserExtRemovedEvent;

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
     * @see \App\Interfaces\UserExtManager::getExtUserResource()
     */
    public function getExtUserResource(UserExt $user_ext): ExtUserResource
    {
        return new ExtUserResource($user_ext);
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
        $user->trust_level = $data->getTrustLevel($source);
        
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
        
        if($data->isActive()) event(new UserExtCreatedEvent($user->id));
        
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
                
            if($user->trust_level != $data->getTrustLevel($source)) {
                $user->trust_level = $data->getTrustLevel($source);
                $modified = true;
            }
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
            
            // check for modified login
            if($user->login != $data->getId()) {
                $user->login = $data->getId();
                $user->save();
                $modified = true;
            }
            
        });

        if ($modified) {
            event(new UserExtUpdatedEvent($user->id));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::syncUsers()
     */
    public function syncUsers(ExtSource $source, \Illuminate\Support\Collection $users, bool $complete = false)
    {
        $result = array();
        foreach($users as $user_resource) {
            if(empty($user_resource->getId())) continue;
            $user = $this->getUser($source, $user_resource);
            if($user == null) {
                $result[$user_resource->getId()] = $this->createUserWithAttributes($source, $user_resource);
            } else {
                $result[$user_resource->getId()] = $this->updateUserWithAttributes($source, $user, $user_resource);
            }
        }
        if($complete) {
            $deleting = UserExt::where('ext_source_id', $source->id)->whereNotIn('login', array_keys($result))->get();
            UserExt::where('ext_source_id', $source->id)->whereNotIn('login', array_keys($result))->delete();
            foreach($deleting as $user_ext) {
                if(!empty($user_ext->user_id)) {
                    event(new UserExtRemovedEvent($user_ext->user_id, $user_ext->id, $source->id));
                }
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
        // do we have attribute identifier?
        $attrdef = $source->attributes()->where('core_name', 'identifier')->first();
        $ext_data = $data->toArray(null);
        if(!empty($attrdef) && array_key_exists('identifier', $ext_data)) {
            $attr = $attrdef->values()->where('value', $ext_data['identifier'])->first();
            if(!empty($attr)) {
                $user = $attr->user()->first();
            }
            if(!empty($user)) 
                return $user;
        }

        return UserExt::where('login', $data->getId())->where('ext_source_id', $source->id)->first();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::activateUser()
     */
    public function activateUser(UserExt $user_ext): UserExt
    {
        $user_ext->active = true;
        $user_ext->save();
        
        event(new UserExtUpdatedEvent($user_ext->id));
        
        return $user_ext;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::activateUser()
     */
    public function activateUserByData(ExtSource $source, ExtUserResource $data): ?UserExt
    {
        $user = $this->getUser($source, $data);
        if($user == null) return null;
        
        return $this->activateUser($user);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::mapUSerAttributes()
     */
    public function mapUserAttributes(ExtSource $source, ExtUserResource $data)
    {
        $attrDefs = $this->getAttrDefs($source);

        $result = array();
        
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
                    $result[$name] = [ 
                        'name' => $name, 
                        'display' => $attrDef->display_name, 
                        'order' => $attrDef->display_order, 
                        'value' => $value 
                    ];
                }
        }
        return collect($result);    
    }

    public function removeUser(ExtSource $source, UserExt $euser)
    {
        $user = $euser->user;
        $euser->delete();
        if(!is_null($user)) {
            event(new UserExtRemovedEvent($user->id, $euser->extSource->id));
        }
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

