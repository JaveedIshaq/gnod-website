<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitec15166cfcc6cd2400bfe87414758635
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitec15166cfcc6cd2400bfe87414758635::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitec15166cfcc6cd2400bfe87414758635::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitec15166cfcc6cd2400bfe87414758635::$classMap;

        }, null, ClassLoader::class);
    }
}
