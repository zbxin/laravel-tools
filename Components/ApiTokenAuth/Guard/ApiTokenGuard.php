<?php

namespace App\Extend;

use App\Models\User;
use App\Models\UserAccessToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Request;

class ApiTokenGuard implements Guard
{

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return UserAccessToken::get(Request::header(UserAccessToken::HEADER_ACCESS_TOKEN_KEY));
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !UserAccessToken::get(Request::header(UserAccessToken::HEADER_ACCESS_TOKEN_KEY));
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!$userCode = UserAccessToken::get(Request::header(UserAccessToken::HEADER_ACCESS_TOKEN_KEY))) {
            return null;
        }
        return User::enabled()->user()->whereCode($userCode)->first();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user()->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {

    }
}
