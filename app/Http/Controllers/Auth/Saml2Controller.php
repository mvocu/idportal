<?php
namespace App\Http\Controllers\Auth;

use App\Auth\Saml2Auth;
use Aacotroneo\Saml2\Http\Controllers\Saml2Controller as Saml2ControllerBase;
use Illuminate\Http\Request;

class Saml2Controller
{
    protected $saml2Controller;
    
    public function __construct()
    {
        $this->saml2Controller = new Saml2ControllerBase();
    }
    
    public function acs(Saml2Auth $saml2Auth, $idpName)
    {
        $result =  $this->saml2Controller->acs($saml2Auth, $idpName);

        return $result;
    }

    public function sls(Request $request, Saml2Auth $saml2Auth, $idpName)
    {
        $result = $this->saml2Controller->sls($saml2Auth, $idpName);
        
        $returnTo = $request->input('RelayState', null);
        
        return empty($returnTo) ? $result : redirect($returnTo);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->saml2Controller, $name), $arguments);
    }
}

