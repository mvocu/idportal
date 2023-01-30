<?php
namespace App\Models\Cas;

class MfaPolicy
{
    public const NONE = 'none';
    public const ALLOWED = 'allowed';
    public const ALWAYS = 'always';
    public const IMPORTANT = 'important';
    
    private static $_DESCRIPTIONS = [
        self::NONE => 'Authentication by second factor is turned off. You do not have access to applications that require multifactor authentication.',
        self::ALLOWED => 'Turned on, second factor will be used only for applications that require it.',
        self::IMPORTANT => 'Turned on, second factor will be used for important services and services that require it.',
        self::ALWAYS => 'Turned on, second factor will be required every time you attempt to log in, unless you log in from already known device.',
    ];
    
    protected $policy;
    
    public function __construct($source)
    {
        if(empty($source) || !array_key_exists($source, self::$_DESCRIPTIONS)) {
            $source = self::NONE;
        }
        $this->policy = $source;
    }
    
    public function getValue() {
        return empty($this->policy) ? self::NONE : $this->policy;
    }
    
    public function getDescription()
    {
        if(array_key_exists($this->policy, self::$_DESCRIPTIONS)) {
            return self::$_DESCRIPTIONS[$this->policy];
        }
        
        return self::$_DESCRIPTIONS[self::NONE];
    }
    
    public function isOn() 
    {
        $policy = $this->getValue();
        return $policy != self::NONE; 
    }
    
    public function getDescriptions() {
        return self::$_DESCRIPTIONS;
    }
}

