<?php

namespace App\Auth\Passwords;

use Illuminate\Support\Str;
use Illuminate\Auth\Passwords\PasswordBrokerManager as BrokerManager;

use InvalidArgumentException;

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

    /**
     * {@inheritDoc}
     * @see \Illuminate\Auth\Passwords\PasswordBrokerManager::resolve()
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        
        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }
        
        // The password broker uses a token repository to validate tokens and send user
        // password e-mails, as well as validating that password reset process as an
        // aggregate service of sorts providing a convenient interface for resets.
        // App\Auth\Passwords\PasswordBroker
        return new PasswordBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null)
            );
    }



}

