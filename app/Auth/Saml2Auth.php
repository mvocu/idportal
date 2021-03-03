<?php
namespace App\Auth;

use Aacotroneo\Saml2\Saml2Auth as Saml2AuthBase;
use Aacotroneo\Saml2\Events\Saml2LogoutEvent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\URL;
use OneLogin\Saml2\Auth as Onelogin_Saml2Auth;

class Saml2Auth extends Saml2AuthBase
{
    public function __construct(Application $app, $idpName, $config)
    {
        // only default supported by now
        $idpName = 'default';
        
        $saml2_config = config('saml2.default_idp_settings');
        if(empty($idpName)) {
            $idpName = 'default';
        }
        
        $idp_config = array_replace_recursive($saml2_config['idp'], $config);
        $saml2_config['idp'] = $idp_config;
        
        if (empty($saml2_config['sp']['entityId'])) {
            $saml2_config['sp']['entityId'] = URL::route('saml2_metadata', $idpName);
        }
        if (empty($saml2_config['sp']['assertionConsumerService']['url'])) {
            $saml2_config['sp']['assertionConsumerService']['url'] = URL::route('saml2_acs', $idpName);
        }
        if (! empty($saml2_config['sp']['singleLogoutService']) && empty($saml2_config['sp']['singleLogoutService']['url'])) {
            $saml2_config['sp']['singleLogoutService']['url'] = URL::route('saml2_sls', $idpName);
        }
        if (strpos($saml2_config['sp']['privateKey'], 'file://') === 0) {
            $saml2_config['sp']['privateKey'] = static::extractPkeyFromFile($saml2_config['sp']['privateKey']);
        }
        if (strpos($saml2_config['sp']['x509cert'], 'file://') === 0) {
            $saml2_config['sp']['x509cert'] = static::extractCertFromFile($saml2_config['sp']['x509cert']);
        }
        if (strpos($saml2_config['idp']['x509cert'], 'file://') === 0) {
            $saml2_config['idp']['x509cert'] = static::extractCertFromFile($saml2_config['idp']['x509cert']);
        }

        parent::__construct(new Onelogin_Saml2Auth($saml2_config)); 
    }
    
    /**
     * {@inheritDoc}
     * @see \Aacotroneo\Saml2\Saml2Auth::sls()
     */
    public function sls($idp, $retrieveParametersFromServer = false)
    {
        $auth = $this->auth;
        
        // destroy the local session by firing the Logout event
        $keep_local_session = false;
        $session_callback = function () use ($idp) {
            event(new Saml2LogoutEvent($idp));
        };
        
        $auth->processSLO($keep_local_session, null, $retrieveParametersFromServer, $session_callback);
        
        $errors = $auth->getErrors();
        
        if (!empty($errors)) {
            return array('error' => $errors, 'last_error_reason' => $auth->getLastErrorReason());
        }
        
        return null;
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Aacotroneo\Saml2\Saml2Auth::logout()
     */
    public function logout($returnTo = null, $nameId = null, $sessionIndex = null, $nameIdFormat = null, $stay = false, $nameIdNameQualifier = null)
    {
        return parent::logout($returnTo, $nameId, $sessionIndex, $nameIdFormat, $stay, $nameIdNameQualifier);
    }
    
    public function getAuthImpl() : Onelogin_Saml2Auth
    {
        return $this->auth;
    }
}

