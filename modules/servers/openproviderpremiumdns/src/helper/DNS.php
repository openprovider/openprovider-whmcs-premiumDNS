<?php

namespace OpenproviderPremiumDns\helper;

use WHMCS\Database\Capsule;
use OpenproviderPremiumDns\lib\RestCurlApi;

class DNS
{
    /**
     * Get the DNS URL
     *
     * @return bool|void
     */
    public static function getDnsUrlOrFail($params)
    {
        try {
            // Get the URL.
            $restCurlApi = new RestCurlApi();

            $getDnsSingleDomainTokenUrlResponse = $restCurlApi->getDnsSingleDomainTokenUrl($params);

            return $getDnsSingleDomainTokenUrlResponse['result']->data->url;
        } catch (\Exception $e) {
            throw new \Exception(" : " . $e->getMessage());
        }
    }
}