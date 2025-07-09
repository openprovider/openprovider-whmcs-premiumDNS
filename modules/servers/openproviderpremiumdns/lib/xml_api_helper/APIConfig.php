<?php

namespace OpenproviderPremiumDns\lib\xmlapihelper;

/**
 * Class APIConfig
 * @package OpenproviderPremiumDns\lib\xmlapihelper* OpenProvider PremiumDNS module
 *
 * @copyright Copyright (c) Openprovider 2025
 */

class APIConfig
{
    static public $moduleVersion     = 'whmcs-5.9.0';
    static public $encoding          = 'UTF-8';
    static public $curlTimeout       = 1000;

    /**
     * Check what is generating the API call.
     *
     * @return string
     */
    public static function getInitiator()
    {
        if(strpos($_SERVER['SCRIPT_NAME'], 'api.php'))
            return 'api';
        elseif(isset($_SESSION['adminid']))
            return 'admin';
        elseif(isset($_SESSION['uid']))
            return 'customer';
        else
            return 'system';
    }
}
