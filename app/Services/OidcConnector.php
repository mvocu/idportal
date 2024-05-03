<?php
namespace App\Services;

use App\Interfaces\IdentityProvider;
use Exception;
use Jumbojett\OpenIDConnectClient as OidcClient;
use App\Auth\OidcUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use GuzzleHttp\Psr7\Uri;

class OidcConnector implements IdentityProvider
{
    protected $config;
    protected $oidc;
    protected $last_error;
    
    public function __construct($config) {
        $this->config = $config;
        $this->oidc = new OidcClient(
            $this->config['url'],
            $this->config['client_id'],
            $this->config['client_secret']
        );
        $this->oidc->setResponseTypes(['code']);
        $this->oidc->addScope(['openid', 'phone', 'email', 'profile', 'address', 'nia', 'cuni']);
        $this->oidc->providerConfigParam(['token_endpoint_auth_methods_supported' => ['client_secret_post']]);
    }

    public function authenticate(array $params) {
        $this->last_error = "";
        if(is_array($params) && isset($params['mfa'])) {
            $this->oidc->addAuthParam([
                'acr_values' => $params['mfa'],
                'prompt' => 'login'
            ]);
        }
        if(is_array($params) && isset($params['delegate'])) {
            $this->oidc->addAuthParam([
                'acr_values' => $params['delegate'],
                'prompt' => 'login'
            ]);
        }
	   try {
        	if($this->oidc->authenticate()) {
            		$claims = $this->oidc->getVerifiedClaims(); // claims from id_token
            		$info = $this->oidc->requestUserInfo();
            		$accessToken = $this->oidc->getAccessToken();
            		$idToken = $this->oidc->getIdToken();
            		return new OidcUser($idToken, $accessToken, $claims, get_object_vars($info));
        	}
	   } catch (Exception $e) {
	       $this->last_error = $e->getMessage();
	       Log::error("Error when authenticating: " . $e->getTraceAsString());
       }
       return null;
    }
    
    public function validate($id_token, $ac_token) {
        $this->last_error = "";
        if(empty($id_token)) {
            if(isset($_REQUEST['code']))
                unset($_REQUEST['code']);
                return null;
        }
        $header = $this->decodeJWT($id_token);
        $claims = $this->decodeJWT($id_token, 1);
        $this->oidc->setAccessToken($ac_token);
        try {
            $info = $this->oidc->requestUserInfo();
        } catch(\Exception $e) {
            $this->last_error = $e->getMessage();
            return null;
        }
        // TODO: check audience, expiration, nbf
        if(empty($claims->sub)) {
            if(isset($_REQUEST['code']))
                unset($_REQUEST['code']);
            return null;
        }
        return new OidcUser($id_token, $ac_token, get_object_vars($claims), get_object_vars($info));
    }
    
    public function introspect($ac_token) {
        $this->last_error = "";
        $token_info = $this->oidc->introspectToken($ac_token);
        if(empty($token_info->active)) {
            return null;
        }
        if(!empty($token_info->exp)) {
            $exp = new Carbon($token_info->exp);
            if($exp->isBefore(Carbon::now())) {
                return null;
            }
        }
        Log::debug("token info: " . print_r($token_info, true));
        return new OidcUser(null, $ac_token, $token_info, null);
    }
    
    public function logout($id_token, $redirect = null) {
        $url = new Uri($this->config['url']);
        $logout_url = "https://" . $url->getHost() . "/cas/logout?service=" . urlencode($redirect);
        return $this->oidc->redirect($logout_url);
        //return $this->oidc->signOut($id_token, $redirect);
    }
    
    public function getLastError() {
        return $this->last_error;
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
    
    protected function decodeJWT($jwt, $section = 0) {
        
        $parts = explode('.', $jwt);
        return json_decode(\JumboJett\base64url_decode($parts[$section]));
    }
    
}

