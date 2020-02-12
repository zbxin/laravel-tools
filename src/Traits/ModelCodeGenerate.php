<?php

namespace ZhiEq\Traits;

use ZhiEq\Exceptions\CustomException;
use ZhiEq\Utils\CodeGenerator;

trait ModelCodeGenerate
{
  /**
   * @return string
   */

  protected static function generateCodeKey()
  {
    return 'code';
  }

  /**
   * @return string
   */

  protected static function generateCodeUniqueKey()
  {
    return self::class;
  }

  /**
   * @return int
   */

  protected static function generateCodeType()
  {
    return CodeGenerator::TYPE_NUMBER_AND_LETTER;
  }

  /**
   * @return int|null
   */

  protected static function generateCodeMaxLength()
  {
    return null;
  }

  /**
   * @return string|null
   */

  protected function generateCode()
  {
    return CodeGenerator::getUniqueCode(self::generateCodeUniqueKey(), function () {
      return self::maxCode();
    }, self::generateCodeLength(), self::generateCodeType(), self::generateCodePrefix(), 0);
  }

  /**
   * @return false|int|string
   */

  protected static function maxCode()
  {
    if ($maxModel = self::orderByDesc(self::generateCodeKey())->first()) {
      return substr($maxModel[self::generateCodeKey()], strlen(self::generateCodePrefix()));
    }
    return 0;
  }

  /**
   * @return string
   */

  protected static function generateCodePrefix()
  {
    $names = explode('\\', self::class);
    if (preg_match_all('/[A-Z]/', end($names), $matchItems) === 0) {
      throw new CustomException('Could Not Get Model Name To Code Prefix.');
    }
    return implode('', $matchItems[0]);
  }

  /**
   * @return int
   */

  protected static function generateCodeLength()
  {
    $maxLength = self::generateCodeMaxLength() === null ? config('tools.model_code_length') : self::generateCodeMaxLength();
    $length = $maxLength - strlen(self::generateCodePrefix());
    if ($length <= 0) {
      throw new CustomException('Model Length Is Too Short.');
    }
    return $length;
  }
}
