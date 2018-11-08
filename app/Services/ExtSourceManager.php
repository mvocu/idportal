<?php

namespace App\Services;

use App\Models\Database\ExtSource;
use App\Models\Database\ExtSourceAttribute;
use Illuminate\Support\Facades\DB;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;

class ExtSourceManager implements ExtSourceManagerInterface
{
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

}

