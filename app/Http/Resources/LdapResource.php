<?php
namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use App\Traits\MapsToIdentity;

class LdapResource extends CasResource implements IdentityResource
{
    use MapsToIdentity;

    protected $_attributeMap = [
        'cunipersonalid' => IdentityResource::CUNIPERSONALID,
        'givenname' => IdentityResource::ATTR_GIVEN_NAME,
        'sn' => IdentityResource::ATTR_FAMILY_NAME,
        'cunibirthdate' => IdentityResource::ATTR_BIRTHDATE,
        'cunibirthcode' => IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
        'cunigender' => IdentityResource::ATTR_GENDER,
        'st' => IdentityResource::ATTR_NATIONALITY,
        'pager' => IdentityResource::ATTR_PHONE_NUMBER,
        'mail' => IdentityResource::ATTR_EMAIL,
        'cuniidcardnumber' => IdentityResource::CARD_NUMBER,
        // TODO: add emails and phones
    ];
    
    public function translateCunipersonalid($value) {
        return $this->_translateSingleValue($value);
    }
    public function translateGivenname($value) {
        return $this->_translateSingleValue($value);
    }
    
    public function translateSn($value) {
        return $this->_translateSingleValue($value);
    }
    
    public function translateCunibirthdate($value) {
        return $this->_translateSingleValue($value);
    }
    
    public function translateCunibirthcode($value) {
        return $this->_translateSingleValue($value);
    }
    
    public function translateCunigender($value) {
        return $this->_translateSingleValue($value);
    }
    
    public function translateSt($value) {
        return $this->_translateSingleValue($value);
    }
    
    protected function _translateSingleValue($value) {
        if(is_array($value) && count($value) == 1) {
            return $value[0];
        } else {
            return $value;
        }
    }
}

