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
        $key = self::generateRandomString();
        $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", file_get_contents(__DIR__ . '\\config.web'), -1, $count);
        if ($count > 0) {
            file_put_contents(__DIR__ . '\\config.web', $content);
        }   
    }

    public static function postPackageInstall()
    {
        self::generateCookieValidationKey();
        file_exists(__DIR__ . '\\..\\..\\..\\..\\.env') || copy(__DIR__ . '\\server\\.env.example', __DIR__ . '\\..\\..\\..\\..\\.env');
    }

    public static function execCommand($command){
        $output=null;
        $retval=null;
        exec($command, $output, $retval);
        echo "Returned with status $retval and output:\n";
        print_r($output);
    }
    
    public static function postPackageUpdate()
    {
        self::execCommand("@php yii migrate --migrationPath=@vendor/weebz/yii2-basics/src/migrations");
    }

}
