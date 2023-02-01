<?php

return [
  
    'policy-none' => 'Off',
    'policy-allowed' => 'Only when service requires',
    'policy-important' => 'For important services or on request',
    'policy-always' => 'Every time',

    'reauth-description' => 'You will be asked to sign-in again to verify your identity before new device registration.',
    
    'gauth-description' => 'Application installed on your mobile phone (or desktop) is used to generate and display time-based one time passwords. Use this generated password in the second authentication step.',
    'webauthn-description' => 'Security key is a device that you own and that can be used to prove your identity during authentication. You can use either hardware security key or mobile phone with biometric sensor as a second factor.',
    'sms-description' => 'Text message containing verification code will be sent to registered phone number. You will be asked to enter this code to verify your identity in the second authentication step.'
    
];