<?php

namespace App\Services;

use App\Auth\Saml2Auth;
use App\Auth\Saml2User;
use App\Interfaces\IdentityProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\URL;
use OneLogin\Saml2\Response;
use Illuminate\Auth\AuthenticationException;

class Saml2Connector implements IdentityProvider
{
    const SUPPORTED_NAMEID_FORMATS = array(
        "urn:oasis:names:tc:SAML:1.1:nameid-format:persistent", 
        "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"
    );
    
    protected $config;
    protected $auth;

    private  $_validation_error;
    
    public function __construct(Application $app, $name, $config)
    {
        $this->config = $config;
        $this->auth = $app->makeWith(Saml2Auth::class, ['name' => $name, 'config' => $config]);
    }

    public function authenticate() : Authenticatable
    {
        // saml2_nameid is set in SamlEventListener, called from ACS endpoint
        $nameid = session()->pull('saml2_nameid');
        if(empty($nameid)) {
            $this->auth->login(URL::full());
            // not reached - the above call will redirect
            return null;
        }
                    
        $user = $this->validate($nameid, session()->pull('saml2_assertion'));
        if($user == null) {
            throw new AuthenticationException("SAML2 authentication failed: " . $this->_validation_error);
        }
        
        return $user;
    }

    public function validate($id_token, $ac_token)
    {
        // $id_token - nameid
        // $ac_token - raw assertion
        
        if(empty($ac_token)) {
            return null;
        }
        
        $settings = $this->auth->getAuthImpl()->getSettings();
        $response = new Response($settings, $ac_token);
        if(!in_array($response->getNameIdFormat(), self::SUPPORTED_NAMEID_FORMATS)) {
            $this->_validation_error = "Unsupported nameid format";
            return null;
        }
        
        if(time() < $response->getSessionNotOnOrAfter()) {
            return new Saml2User($response->getNameId(), $ac_token, $response->getAttributes());
        } else {
            $this->_validation_error = "Session expired";
            return null;
        }
    }

    public function logout()
    {
        $previous_url = session()->pull('saml2_logout_previous');
        $logout = session()->pull('saml2_logout');
        if(empty($previous_url) && empty($logout)) {
            session()->put('saml2_logout_previous', URL::previous());
            $this->auth->logout(URL::full());
        } else {
            // redirect()->setIntendedUrl($previous_url);
	    session()->put('url.intended', $previous_url);
        }
    }
    
}

