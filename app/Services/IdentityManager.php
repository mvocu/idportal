<?php
namespace App\Services;

use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\IdentityResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;

class IdentityManager implements IdentityManagerInterface
{
    private $last_score = 0;
    private $last_errors = null;
    
    const BASE_IDENTITY_RULES = [
        IdentityResource::ATTR_GIVEN_NAME => 'required|string',
        IdentityResource::ATTR_FAMILY_NAME => 'required|string',
        IdentityResource::ATTR_BIRTHDATE => 'required|date',
    ];
    
    const SAME_IDENTITY_RULES = [
        'candidate.'.IdentityResource::ATTR_GIVEN_NAME => 'required|string|similar:user.'.IdentityResource::ATTR_GIVEN_NAME,
        'candidate.'.IdentityResource::ATTR_FAMILY_NAME => 'required|string|similar:user.'.IdentityResource::ATTR_FAMILY_NAME,
        'candidate.'.IdentityResource::ATTR_BIRTHDATE => 'required|date|same_date_if_exists:user.'.IdentityResource::ATTR_BIRTHDATE,
    ];
    
    const MAIL_RULES = [
        'candidate.'.IdentityResource::ATTR_EMAIL => 'required|intersects_with:user.'.IdentityResource::ATTR_EMAIL,
    ];
    
    const PHONE_RULES = [
        'candidate.'.IdentityResource::ATTR_PHONE_NUMBER => 'required|intersects_with:user.'.IdentityResource::ATTR_PHONE_NUMBER,
    ];
    
    const ADMIN_NUMBER_RULES = [
        'candidate.'.IdentityResource::ATTR_ADMINISTRATIVE_NUMBER 
            => 'required|string|same_administrative_number:user.'.IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
    ];
    
    const GENDER_RULES = [
        'candidate.'.IdentityResource::ATTR_GENDER => 'required|in:M,F|same:user.'.IdentityResource::ATTR_GENDER,
    ];
    
    const NATIONALITY_RULES = [
        'candidate.'.IdentityResource::ATTR_NATIONALITY => 'required|string|size:2|same:user.'.IdentityResource::ATTR_NATIONALITY,  
    ];
    
    
    // scores required by the same identity comparison for given purpose
    const REQUIRED_SCORES = [
        self::COMPARISON_PURPOSE_INITIAL => 50,
        self::COMPARISON_PURPOSE_MERGE => 75,
    ];
    
    // attribute match scores
    const ATTR_SCORES = [
        IdentityResource::ATTR_EMAIL => 25,
        IdentityResource::ATTR_PHONE_NUMBER => 50,
        IdentityResource::ATTR_ADMINISTRATIVE_NUMBER => 75,
        IdentityResource::ATTR_ADDRESS => 25,
        // IdentityResource::ATTR_GENDER => 0,
        // IdentityResource::ATTR_NATIONALITY => 5,
    ];
    
    const ATTR_RULES = [
        IdentityResource::ATTR_EMAIL => self::MAIL_RULES,
        IdentityResource::ATTR_PHONE_NUMBER => self::PHONE_RULES,
        IdentityResource::ATTR_ADMINISTRATIVE_NUMBER => self::ADMIN_NUMBER_RULES,
        // IdentityResource::ATTR_GENDER => self::GENDER_RULES,
        // IdentityResource::ATTR_NATIONALITY => self::NATIONALITY_RULES,
    ];
    
    const ATTR_MATCH_REQUIRED = [
        IdentityResource::ATTR_EMAIL => 0,
        IdentityResource::ATTR_PHONE_NUMBER => 0,
        IdentityResource::ATTR_ADMINISTRATIVE_NUMBER => 1,
        IdentityResource::ATTR_ADDRESS => 1,
        // IdentityResource::ATTR_GENDER => 1,
        // IdentityResource::ATTR_NATIONALITY => 1,
    ];
    
    const FULL_ATTRS_RULES = [
        IdentityResource::ATTR_GIVEN_NAME => 'required',
        IdentityResource::ATTR_FAMILY_NAME => 'required',
        // IdentityResource::ATTR_GENDER,
        IdentityResource::ATTR_BIRTHDATE => 'required',
        // IdentityResource::ATTR_NATIONALITY,
        IdentityResource::ATTR_ADMINISTRATIVE_NUMBER => 'required',
        IdentityResource::ATTR_PHONE_NUMBER => 'required',
        IdentityResource::ATTR_EMAIL => 'required',
        IdentityResource::ATTR_ADDRESS => 'required|array',
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_STREET => 'required',
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_EV_NUMBER 
            => 'required_without:'.IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_STREET_NUMBER,
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_STREET_NUMBER
            => 'required_without:'.IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_EV_NUMBER,
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_POSTAL_CODE,
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_CITY,
        IdentityResource::ATTR_ADDRESS.".".IdentityResource::ATTR_ADDRESS_COUNTRY
    ]; 
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::getLastScore()
     */
    public function getLastScore()
    {
        return $this->last_score;
    }

    public function getLastError()
    {
        return empty($this->last_errors) ? "" : $this->last_errors->first();
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::compareIdentity()
     */
    public function compareIdentity($user, $candidate, $purpose)
    {
        $this->last_errors = null;
        if(isset($user[IdentityResource::CUNIPERSONALID]) && isset($candidate[IdentityResource::CUNIPERSONALID])) {
            // both records have known identity
            return $user[IdentityResource::CUNIPERSONALID] == $candidate[IdentityResource::CUNIPERSONALID];
        }
        // check base identity data are present
        $validator = Validator::make($candidate, self::BASE_IDENTITY_RULES);
        if($validator->fails()) {
            // can not go on without those
            $this->last_errors = $validator->errors();
            return self::IDENTITY_RESULT_UNKNOWN;
        }
        $data = [ 'user' => $user, 'candidate' => $candidate];
        // required rules (may not be sufficient)
        $validator = Validator::make($data, self::SAME_IDENTITY_RULES);
        if($validator->fails()) {
            $this->last_errors = $validator->errors();
            return self::IDENTITY_RESULT_DIFFERENT;
        }

        // if the candidate data have some LoA, increase score
        if(isset($candidate[IdentityResource::LOA]) 
            && in_array($candidate[IdentityResource::LOA], [ self::LOA_SUBSTANTIAL, self::LOA_HIGH ])) 
        {
            $score = 50;
        } else {
            $score = 0;
        }
        
        // go through attributes, perform match and calculate score
        foreach(array_keys(self::ATTR_SCORES) as $attr) {
            if(isset($candidate[$attr]) && isset($user[$attr])) {
                if(!isset(self::ATTR_RULES[$attr])) {
                    die("No rules for $attr");
                }
                $validator = Validator::make($data, self::ATTR_RULES[$attr]);
                if($validator->passes()) {
                    $score += self::ATTR_SCORES[$attr];
                } else {
                    if(self::ATTR_MATCH_REQUIRED[$attr]) {
                        $this->last_errors = new MessageBag([ $attr => "$attr failed to match."]);
                        return self::IDENTITY_RESULT_DIFFERENT;
                    }
                }
            }
        }
        
        $this->last_score = $score;
        
        return ($score >= self::REQUIRED_SCORES[$purpose]) ? self::IDENTITY_RESULT_SAME : self::IDENTITY_RESULT_UNKNOWN;
    }

    
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::hasAllInformation()
     */
    public function hasAllInformation($data)
    {
        $validator = Validator::make($data, self::FULL_ATTRS_RULES);
        return $validator->passes();
    }
    
}

