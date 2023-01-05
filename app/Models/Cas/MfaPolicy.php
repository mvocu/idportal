<?php
namespace App\Models\Cas;

class MfaPolicy
{
    public const NONE = 'none';
    public const ALLOWED = 'allowed';
    public const ALWAYS = 'always';
    public const IMPORTANT = 'important';
    
    private static $_DESCRIPTIONS = [
        self::NONE => 'Second factor is not available.',
        self::ALLOWED => 'Second factor will be used only when service requires it',
        self::IMPORTANT => 'Second factor will be used for important services and services that require it.',
        self::ALWAYS => 'Second factor will be required every time you attempt to log in',
    ];
    
    protected $policy;
    
    public function __construct($source)
    {
        $this->policy = $source;
    }
    
    public function getDescription()
    {
        if(array_key_exists($this->policy, self::$_DESCRIPTIONS)) {
            return self::$_DESCRIPTIONS[$this->policy];
        }
        
        return self::$_DESCRIPTIONS[self::NONE];
    }
}

