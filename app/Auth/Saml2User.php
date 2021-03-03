<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use App\Http\Resources\ExtUserResource;

class Saml2User implements Authenticatable
{
    protected $nameid;
    protected $attributes;
    protected $assertion;
    
    public function __construct($nameid, $assertion, $attributes)
    {
        $this->nameid = $nameid;
        $this->assertion = $assertion;
        $this->attributes = $attributes;
    }
    
    public function getAuthIdentifier()
    {
        return $this->nameid;
    }

    public function getRememberToken()
    {
        return $this->nameid;
    }

    public function getAuthPassword()
    {
        return $this->assertion;
    }

    public function getRememberTokenName()
    {
        return 'assertion';   
    }

    public function setRememberToken($value)
    {
        $this->assertion = $value;
    }

    public function getAuthIdentifierName()
    {
        return 'nameid';
    }

    public function getAttributes() 
    {
        $data = $this->attributes;
        $result = array();
        foreach($data as $name => $value) {
            switch($name) {
                case 'CurrentAddress':
                case 'TRadresaID':
                    $this->parseXMLValue($result, $name, base64_decode($value[0]));
                    break;

                case 'CurrentFamilyName':
                case 'CurrentGivenName':
                    $result[$name] = Str::title($value[0]);
                    break;
                    
                default:
                    if(is_array($value)) {
                        $result[$name] = $value[0];
                    } else {
                        $result[$name] = $value;
                    }
            }
        }
        return $result;    
    }
    
    public function getResource($active = true)
    {
        return new ExtUserResource([
            'id' => $this->getAuthIdentifier(),
            'parent' => null,
            'active' => $active,
            'trust_level' => 0,
            'attributes' => $this->getAttributes()]);
    }

    public function getValidatorRules()
    {
        return [
            'CurrentGivenName' => 'required|string|max:255',
            'CurrentFamilyName' => 'required|string|max:255',
            'eMail' => 'required|string|email|max:255|unique:contact,email',
            'PhoneNumber' => 'required|string|phone|max:255|unique:contact,phone',
        ];
    }
    
    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    private function parseXMLValue(&$result, $name, $value)
    {
        $doc = new \DOMDocument();
        $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><root xmlns:eidas="http://eidas.europa.eu/">' . $value . '</root>');
        $parent = $doc->documentElement;
        while($parent->childNodes->length == 1) {
            $parent = $parent->firstChild;
        }
        foreach($parent->childNodes as $node) {
            if(empty($node->localName)) continue;
            $result[$name . '_' . $node->localName] = $node->textContent;
        }
        return;
    }
}

