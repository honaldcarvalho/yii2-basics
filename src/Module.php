<?php

namespace weebz\yii2basics;

/**
 * common module definition class
 */
class Module extends \yii\base\Module
{
    const MODULE = "yii2basics";
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    protected static function generateRandomBytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        if (extension_loaded('openssl')) {
            return openssl_random_pseudo_bytes($length);
        }

        throw new \Exception('PHP >= 7.0 or the OpenSSL PHP extension is required by Yii2.');
    }

    protected static function generateRandomString()
    {
        $length = 32;
        $bytes = self::generateRandomBytes($length);
        return strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
    }

    public static function generateCookieValidationKey()
    {
        $configs = func_get_args();
        $key = self::generateRandomString();
        foreach ($configs as $config) {
            if (is_file($config)) {
                $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", file_get_contents($config), -1, $count);
                if ($count > 0) {
                    file_put_contents($config, $content);
                }
            }
        }
    }

    public static function postPackageInstall()
    {
        //file_exists('@app/.env') || copy('@vendor/weebz/yii2-basics/src/server/.env.example', './.env');
        file_exists(__DIR__ . '/../../../../.env') || copy(__DIR__ . '/config/.env.example', __DIR__ . '/../../../../.env');
    }

}
