<?php

namespace OpenproviderPremiumDns\config;

/**
 * Hardcoded configuration
 * OpenProvider Premium DNS module
 *
 * @copyright Copyright (c) Openprovider 2025
 */
class Configuration
{
    /**
     * init config
     */
    public static function init()
    {
    }

    public static function getApiUrl($apiMethod)
    {
        return self::getServerUrl() . "modules/servers/openproviderpremiumdns/api/{$apiMethod}";
    }

    public static function getJsModuleUrl($jsModuleName)
    {
        return self::getServerUrl() . "modules/servers/openproviderpremiumdns/assets/js/{$jsModuleName}.js";
    }

    public static function getCssModuleUrl($cssModuleName)
    {
        return self::getServerUrl() . "modules/servers/openproviderpremiumdns/assets/css/{$cssModuleName}.css";
    }

    public static function getServerUrl()
    {
        if (method_exists('\\WHMCS\\Config\\Setting', 'getValue')) {
            $systemUrl = rtrim(\WHMCS\Config\Setting::getValue('SystemURL'), '/') . "/";
        }

        if (!isset($systemUrl)) {
            $systemUrl = localAPI('GetConfigurationValue', ['setting' => 'SystemURL'])['value'];
        }

        $systemUrlWithoutProtocol = str_replace(['http://', 'https://'], '', $systemUrl);
        $phpHostUrl = $_SERVER['HTTP_HOST'];

        if (
            (strpos($systemUrlWithoutProtocol, 'www.') !== false &&
                strpos($phpHostUrl, 'www.') !== false) ||
            (strpos($systemUrlWithoutProtocol, 'www.') === false &&
                strpos($phpHostUrl, 'www.') === false)
        ) {
            return '//' . $systemUrlWithoutProtocol;
        }

        if (
            strpos($systemUrlWithoutProtocol, 'www.') !== false &&
            strpos($phpHostUrl, 'www.') === false
        ) {
            return '//' . str_replace('www.', '', $systemUrlWithoutProtocol);
        }

        return '//www.' . $systemUrlWithoutProtocol;
    }
}
