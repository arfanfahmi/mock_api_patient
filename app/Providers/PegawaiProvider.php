<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Session;

class PegawaiProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $userData = Session::get('user');

        // Ensure the user exists and the token matches
        if ($userData && $userData->getAuthIdentifier() == $identifier) {
            return $userData;
        }

        return null;
    }

    // You can override other methods as needed

    // For example:
    
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // Implement your logic to retrieve the user by ID

        // For example, you might check the session data
        $userData = Session::get('user');

        // Ensure the user exists and the identifier matches
        if ($userData && $userData->getAuthIdentifier() == $identifier) {
            return $userData;
        }

        return null;
    }
}
