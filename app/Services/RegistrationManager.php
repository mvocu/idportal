<?php
namespace App\Services;

use App\Interfaces\RegistrationManager as RegistrationManagerInterface;
use App\User;
use App\Http\Resources\ExtUserResource;
use App\Interfaces\UserExtManager;
use App\Interfaces\LdapConnector;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegistrationManager implements RegistrationManagerInterface
{
    protected $user_ext_mgr;
    protected $ldap_mgr;
    
    public function __construct(
        UserExtManager $user_ext_mgr,
        LdapConnector $ldap_mgr
        )
    {
        $this->user_ext_mgr = $user_ext_mgr;
        $this->ldap_mgr = $ldap_mgr;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\RegistrationManager::activateUser()
     */
    public function activateUser(\App\Models\Database\UserExt $user)
    {
        return $this->user_ext_mgr->activateUser($user);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\RegistrationManager::createUserExt()
     */
    public function createUserExt(\App\Http\Resources\ExtUserResource $resource)
    {
        $source = ExtSource::where('type', 'Internal')->get()->first();
        if($source == null) throw new ModelNotFoundException();

        $user = $this->user_ext_mgr->getUser($source, $resource);
        if($user != null) {
            if($user->active) {
                return false;
            } else {
                $this->user_ext_mgr->updateUserWithAttributes($source, $user, $resource);
                return $user;
            }
        }
        $user = $this->user_ext_mgr->createUserWithAttributes($source, $resource);
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\RegistrationManager::getExtUserResource()
     */
    public function getExtUserResource($data)
    {
        if(!empty($data['birth_year'])) {
            $data['birth_date'] = '01/01/' . $data['birth_year'];
        }
        
        if(empty($data['email'])) {
            $default_mail = config('registration.default_email');
            if(!empty($data['phone'])) {
                $data['email'] = $data['phone'] . '@' . $default_mail;
            } else {
                $data['email'] = $data['firstname'] . '.' . $data['lastname'] . '.' . $data['birth_year'] . '@' . $default_mail;
            }
        }
        #$source = ExtSource::where('type', 'Internal')->get()->first();
        #if($source == null) throw new ModelNotFoundException();
        $resource = new ExtUserResource([ 'id' => $data['email'], 'active' => false, 'attributes' => $data ]);
        
        return $resource;
    }

    public function getRegisteredUser(UserExt $user)
    {
        
        // wait for the async user creation
        $ldap_user = null;
        for($count = 0; $count < 30 && $ldap_user == null; $count++) {
            sleep(1);
            $new_user = $this->checkAccount($user->refresh());
            if(!empty($new_user)) {
                $ldap_user = $new_user;
            }
        }
        
        if(empty($ldap_user)) {
            return null;
        }
        
        $user = new User([], $ldap_user->getQuery());
        $user->setRawAttributes($ldap_user->getAttributes());
     
        return $user;
    }
    
    protected function checkAccount(UserExt $user_ext)
    {
        $user = $user_ext->user;
        if(empty($user)) return null;
        return $this->ldap_mgr->findUser($user);
    }
    
    
}

