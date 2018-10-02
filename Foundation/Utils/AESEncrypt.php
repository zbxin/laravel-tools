<?php

namespace ZhiEq\Utils;

use Illuminate\Contracts\Encryption\EncryptException;

class AESEncrypt
{
    protected static $cipher = 'AES-256-CBC';

    /**
     * @param $value
     * @return string
     */

    public static function quickEncrypt($value)
    {
        return self::encrypt($value, self::key());
    }

    /**
     * @param $text
     * @return string
     */

    public static function quickDecrypt($text)
    {
        return self::decrypt($text, self::key());
    }

    /**
     * @return string
     */

    protected static function key()
    {
        return env('AES_SECRET_KEY', '');
    }

    /**
     * @param $value
     * @param $key
     * @return string
     */


    public static function encrypt($value, $key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $value = openssl_encrypt($value, self::$cipher, self::buildEncryptKey($key, $iv), 0, $iv);
        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }
        $mac = self::hash($iv = bin2hex($iv), $value, $key);
        $json = json_encode(compact('iv', 'value', 'mac'));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }
        return base64_encode($json);
    }

    /**
     * @param $text
     * @param $key
     * @return string
     */

    public static function decrypt($text, $key)
    {
        $encryptData = json_decode(base64_decode($text), true);
        if (!(is_array($encryptData) && isset($encryptData['iv'], $encryptData['value'], $encryptData['mac']))) {
            throw new EncryptException('Could not decrypt the data.');
        }
        if ($encryptData['mac'] !== self::hash($encryptData['iv'], $encryptData['value'], $key)) {
            throw new EncryptException('Could not decrypt the data.mac invalid.');
        }
        $iv = hex2bin($encryptData['iv']);
        return openssl_decrypt($encryptData['value'], self::$cipher, self::buildEncryptKey($key, $iv), 0, $iv);
    }

    /**
     * @param $key
     * @param $iv
     * @return mixed
     */

    protected static function buildEncryptKey($key, $iv)
    {
        return hash_pbkdf2("sha256", $key, $iv, 1000, 32, true);
    }

    /**
     * @param $iv
     * @param $value
     * @param $key
     * @return string
     */

    protected static function hash($iv, $value, $key)
    {
        return base64_encode(hash_hmac('sha256', $iv . $value, $key, true));
    }
}
