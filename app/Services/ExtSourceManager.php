<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use App\Models\Database\ExtSourceAttribute;
use Illuminate\Support\Facades\DB;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;
use Illuminate\Contracts\Foundation\Application;

class ExtSourceManager implements ExtSourceManagerInterface
{
    protected $app;
    
    public function __construct(Application $app) {
        $this->app = $app;    
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceManager::createExtSourceWithAttributes()
     */
    public function createExtSourceWithAttributes(array $data): ExtSource
    {
        $source = new ExtSource();
        $source->fill($data);
        
        DB::transaction(function() use ($source, $data) {
            
            $source->save();
            
            if(array_key_exists('attributes', $data) && is_array($data['attributes'])) {
                foreach($data['attributes'] as $name => $core_name) {
                    $attr = new ExtSourceAttribute([
                        'name' => $name,
                        'core_name' => $core_name
                    ]);
                    $source->attributes()->save($attr);
                }
            }
            
        });
        return $source;
            
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceManager::getConnector()
     */
    public function getConnector(ExtSource $ext_source): ExtSourceConnector
    {
        $config = json_decode($ext_source->configuration, true);
        $connector = $this->app->makeWith($ext_source->type, [ 'config' => $config ]);
        return $connector;
    }


}

