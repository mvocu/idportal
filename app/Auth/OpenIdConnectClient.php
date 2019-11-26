<?php
namespace App\Auth;

use Jumbojett\OpenIDConnectClient as OpenIdConnectClientBase;

class OpenIdConnectClient extends OpenIDConnectClientBase
{
    
    public function validateToken($id_token) 
    {
        $claims = $this->decodeJWT($id_token, 1);
        
        // If this is a valid claim
        if ($this->verifyClaims($claims)) {
            
            // Save the verified claims
            $this->verifiedClaims = $claims;

            // Success!
            return $claims;
            
        } else {
            throw new \Exception ("Unable to verify JWT claims");
        }
        
    }
    
    private function decodeJWT($jwt, $section = 0) {
        
        $parts = explode(".", $jwt);
        return json_decode(\Jumbojett\base64url_decode($parts[$section]));
    }
    
    private function verifyClaims($claims) {
        return (($claims->iss == $this->getIssuer() || $claims->iss == $this->getWellKnownIssuer() || $claims->iss == $this->getWellKnownIssuer(true))
            && (($claims->aud == $this->getClientID()) || (in_array($this->getClientID(), $claims->aud)))
            && ( !isset($claims->exp) || $claims->exp >= time() - 300)
            && ( !isset($claims->nbf) || $claims->nbf <= time() + 300)
            );
    }
    
}

