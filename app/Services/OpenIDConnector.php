<?php
namespace App\Services;

use App\Interfaces\IdentityProvider;
use App\Auth\OpenIdConnectClient;
use App\Auth\ExternalUser;
use Illuminate\Contracts\Auth\Authenticatable;

class OpenIDConnector implements IdentityProvider
{
    protected $config;
    protected $oidc;
    
    public function __construct($config) {
        $this->config = $config;
        $this->oidc = new OpenIDConnectClient(
            $this->config['url'],
            $this->config['client_id'],
            $this->config['client_secret']
        );
        $this->oidc->setResponseTypes(['code']);
        $this->oidc->addScope(['openid', 'openid2', 'phone', 'email', 'profile', 'address']);
        $this->oidc->providerConfigParam(['token_endpoint_auth_methods_supported' => ['client_secret_post']]);
    }

    public function authenticate() : Authenticatable {
        if($this->oidc->authenticate()) {
            $claims = $this->oidc->getVerifiedClaims();
            $info = array();
            $this->parseInfo($this->oidc->requestUserInfo(), $info, "");
            $accessToken = $this->oidc->getAccessToken();
            $idToken = $this->oidc->getIdToken();
            return new ExternalUser($idToken, $accessToken, $claims, $info);
        }
        return null;
    }
    
    public function validate($id_token, $ac_token) {
        if(empty($id_token)) {
            if(isset($_REQUEST['code']))
                unset($_REQUEST['code']);
            return null;
        }
        $claims = $this->oidc->validateToken($id_token);
        $this->oidc->setAccessToken($ac_token);
        $info = array();
        $this->parseInfo($this->oidc->requestUserInfo(), $info, "");
        if(isset($info['error']) or empty($info['name'])) {
            if(isset($_REQUEST['code']))
                unset($_REQUEST['code']);
            return null;
        }
        return new ExternalUser($id_token, $ac_token, $claims, $info);
    }
    
    protected function parseInfo($info, &$result, $prefix) {
        if(!is_array($info) && !is_object($info)) {
            return;
        }
        foreach((array)$info as $key => $value) {
            if(strstr($key, "phone")) {
                $value = str_replace(".", "", $value);
            }
            if(is_object($value) || is_array($value)) {
                $this->parseInfo($value, $result, $key . "_");
            } else {
                $result[$prefix.$key] = $value;
            }
        }
    }
}

