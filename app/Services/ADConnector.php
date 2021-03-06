<?php
namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use Adldap\Adldap;
use Adldap\AdldapException;
use Adldap\Utilities;
use Illuminate\Support\Str;

class ADConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{

    protected $attribute_names = ['samaccountname', 'givenname', 'sn', 'telephonenumber', 'pager', 'mail' ];
    
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
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::listUsers()
     */
    public function listUsers(\App\Models\Database\ExtSource $source)
    {
        try {
            $groups = $this->listGroups($source);
            $results = $this->ad->search()->select('*')
                ->where([
                    ['objectclass', '=', 'person'  ],
                    ['objectclass', '=', 'user' ],
                    ['objectclass', '!', 'computer']
                ])
                ->get();
        } catch (AdldapException $e) {
            $this->lastStatus = $e->getMessage();
            return null;
        }
        $this->lastStatus = null;
        return $results->map(function($item, $key) use ($groups) { 
            $attrs = $item->getAttributes();
            $attrs['dn'] = $item->getDn();
            return $this->mapResult($attrs, $groups); 
        });
    }
    
    public function listGroups(ExtSource $source)
    {
        try {
            $results = $this->ad->search()->select('*')
                ->where('objectclass', '=', 'group')
                ->get();
        } catch (AdldapException $e) {
            $this->lastStatus = $e->getMessage();
            return null;
        }
        $this->lastStatus = null;
        return $results->map(function($item, $key) { 
            $members = $item->getAttribute('member');
            if(is_array($members)) {
                $members = array_map(function($val) { return Str::lower($val); } , $members);
            } else {
                $members = [];
            }
            return [ $item->getFirstAttribute('cn') => $members ];
        });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::supportsUserListing()
     */
    public function supportsUserListing(\App\Models\Database\ExtSource $source)
    {
        return true;
    }

    public function supportsGroupListing(ExtSource $source)
    {
        return true;    
    }
    
    
    protected function mapResult($entry, $groups) {
        $result = [];
        foreach($this->attribute_names as $name) {
            if(empty($entry[$name])) continue;
            if(false && $name == 'mail') {
                $result[$name] = $entry[$name];
            } else {
                $result[$name] = count($entry[$name]) > 1 ? $entry[$name] : $entry[$name][0];
            }
        }
        if(!empty($entry['proxyaddresses'])) {
            foreach($entry['proxyaddresses'] as $addr) {
                $parts = explode(':', $addr);
                if($parts[0] != 'smtp' && $parts[0] != 'SMTP') continue; 
                if(ends_with($parts[1], array(".local"))) continue;
                if(!array_key_exists('mail', $result)) {
                    $result['proxyaddresses'] = [];
                }
                $result['proxyaddresses'][] = $parts[1];
            }
        }
        $dn = Str::lower($entry['dn']);
        $u_groups = $groups
        ->filter(function($item, $key) use($dn) { return false !== array_search($dn, array_values($item)[0]); })
            ->map(function($item, $key) {  return array_keys($item)[0]; })
            ->toArray();
        $result['groups'] = $u_groups;
        return $this->makeResource($result, 'samaccountname');
    }
}

