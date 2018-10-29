<?php

namespace App\Auth\Passwords;

use Illuminate\Support\Str;
use Illuminate\Auth\Passwords\PasswordBrokerManager as BrokerManager;

class PasswordBrokerManager extends BrokerManager
{
    /**
     * {@inheritDoc}
     * @see \Illuminate\Auth\Passwords\PasswordBrokerManager::createTokenRepository()
     */
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];
        
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        
        $connection = $config['connection'] ?? null;
        
        // App\Auth\Passwords\DatabaseTokenRepository
        return new DatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $this->app['hash'],
            $config['table'],
            $key,
            $config['expire']
            );
    }


}

