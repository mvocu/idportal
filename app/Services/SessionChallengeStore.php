<?php
namespace App\Services;

use App\Interfaces\ChallengeStore;
use Illuminate\Contracts\Session\Session;

class SessionChallengeStore implements ChallengeStore
{
    const SESSION_KEY = "challenge";

    protected $session;
    
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function removeChallenge($key)
    {
        $chall = $this->session->get(self::SESSION_KEY, []);
        if(isset($chall[$key])) {
            unset($chall[$key]);
        }
        $this->session->put(self::SESSION_KEY, $chall);
    }

    public function saveChallenge($key, $data)
    {
        $chall = $this->session->get(self::SESSION_KEY, []);
        $chall[$key] = $data;
        $this->session->put(self::SESSION_KEY, $chall);
        return $data;
    }

    public function getChallenge($key)
    {
        $chall = $this->session->get(self::SESSION_KEY, []);
        return isset($chall[$key]) ? $chall[$key] : null;
    }
    
}

