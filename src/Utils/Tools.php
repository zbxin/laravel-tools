<?php

namespace ZhiEq\Utils;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tools
{
  /**
   * @var App\Models\User|Model
   */

  public static $authUser;

  /**
   * @return Authenticatable|App\Models\User|Model
   */

  public static function authUser()
  {
    if (self::$authUser === null && !Auth::guest()) {
      self::$authUser = Auth::user();
    }
    return self::$authUser;
  }
}
