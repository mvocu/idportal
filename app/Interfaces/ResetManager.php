<?php
namespace App\Interfaces;

use App\Models\Ldap\User as LdapUser;

interface ResetManager
{
        public function getAvailableMethods(LdapUser $model = null);
        
        public function getMethodDescription($method = null);
        
        public function determineRequiredScore(LdapUser $user);

        public function rememberTargetUser(LdapUser $user);

        public function retrieveTargetUser();
        
        public function forgetTargetUser();
        
        public function rememberRemoteIdentity($identity);
        
        public function retrieveRemoteIdentity();
        
        public function forgetRemoteIdentity();
        
        public function saveMethod($method);
        
        public function retrieveMethod();
        
        public function forgetMethod();
        
        public function saveVerificationResult($result);
        
        public function retrieveVerificationResult();
        
        public function forgetVerificationResult();
        
}

