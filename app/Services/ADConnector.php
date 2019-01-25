<?php
namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use Adldap\Adldap;
use Adldap\AdldapException;

class ADConnector implements ExtSourceConnector
{

    protected $attribute_names = ['samaccountname', 'givenname', 'sn', 'telephonenumber', 'mail' ];
    
    protected $config;
    protected $ad;
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->ad = new Adldap();
        $this->ad->addProvider($config);
    }
    
    public function findUser(ExtSource $source, $user)
    {
        
    }

    public function getUser(ExtSource $source, $id)
    {
        try {
            $entry = $this->ad->search()->select('*')->findBy('samaccountname',  $id);
        } catch (AdldapException $e) {
            $this->lastStatus = $e->getMessage();
            return null;
        }
        $this->lastStatus = null;
        return $this->mapResult($entry->getAttributes());
    }
    
    protected function mapResult($entry) {
        $result = [];
        foreach($this->attribute_names as $name) {
            if(empty($entry[$name])) continue;
            if($name == 'mail') {
                $result[$name] = $entry[$name];
            } else {
                $result[$name] = count($entry[$name]) > 1 ? $entry[$name] : $entry[$name][0];
            }
        }
        if(!empty($entry['proxyaddresses'])) {
            foreach($entry['proxyaddresses'] as $addr) {
                $parts = explode(':', $addr);
                if($parts[0] != 'smtp') continue; 
                if(!array_key_exists('mail', $result)) {
                    $result['mail'] = [];
                }
                $result['mail'][] = $parts[1];
            }
        }
        return $result;
    }
}

