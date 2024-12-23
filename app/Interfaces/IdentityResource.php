<?php
namespace App\Interfaces;

interface IdentityResource
{
    
    /* OIDC claims */ 
    const ATTR_EXTERNAL_ID = "external_id";
    
    const ATTR_GIVEN_NAME = "given_name";
    const ATTR_FAMILY_NAME = "family_name";
    const ATTR_MIDDLE_NAME = "middle_name";
    const ATTR_NAME = "name";
    const ATTR_NICKNAME = "nickname";
    const ATTR_GENDER = "gender";
    const ATTR_NATIONALITY = "nationality";
    const ATTR_DOCUMENT_TYPE = "document_type";
    const ATTR_DOCUMENT_NUMBER = "document_number";
    const ATTR_ADMINISTRATIVE_NUMBER = "administrative_number";
    const ATTR_BIRTHDATE = "birthdate";
    const ATTR_EMAIL = "email";
    const ATTR_EMAIL_VERIFIED = "email_verified";
    const ATTR_PHONE_NUMBER = "phone_number";
    const ATTR_PHONE_NUMBER_VERIFIED = "phone_number_verified";
    const ATTR_ADDRESS = "address"; /* array */

    
    const ATTR_ADDRESS_STREET = "street";
    const ATTR_ADDRESS_EV_NUMBER = "evidence_number";
    const ATTR_ADDRESS_STREET_NUMBER = "street_number";
    const ATTR_ADDRESS_CITY = "city";
    const ATTR_ADDRESS_CITY_PART = "city_part";
    const ATTR_ADDRESS_COUNTRY = "country";
    const ATTR_ADDRESS_POSTAL_CODE = "postal_code";
    
    /* CAS cuni claims */
    const LOA = "loa";
    const CUNIPERSONALID = "cuni_personalid";
    const CARD_NUMBER = "cuni_card_id";
    const CARD_CHIP = "cuni_card_chip";
    
    const IDENTITY_ATTR_KEYS = [
        self::LOA,
        self::CUNIPERSONALID,
        self::ATTR_GIVEN_NAME,
        self::ATTR_FAMILY_NAME,
        self::ATTR_GENDER,
        self::ATTR_BIRTHDATE,
        self::ATTR_NATIONALITY,
        self::ATTR_ADMINISTRATIVE_NUMBER,
        self::ATTR_PHONE_NUMBER,
        self::ATTR_EMAIL,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_STREET,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_EV_NUMBER,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_STREET_NUMBER,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_POSTAL_CODE,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_CITY_PART,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_CITY,
        self::ATTR_ADDRESS.".".self::ATTR_ADDRESS_COUNTRY,
        self::CARD_NUMBER,
    ];
    
}

