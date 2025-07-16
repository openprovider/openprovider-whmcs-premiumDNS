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
        $domainName = $params['domain'];

        // Lookup domain in tbldomains table
        $domain = Capsule::table('tbldomains')
            ->where('domain', $domainName)
            ->first();

        // Check if OpenProvider is the provider
        if ($domain->registrar != 'openprovider' || $domain->status != 'Active')
            return false;

        // Getet the URL.
        try {
            $restCurlApi = new RestCurlApi();

            $getDnsSingleDomainTokenUrlResponse = $restCurlApi->getDnsSingleDomainTokenUrl($params);

            return $getDnsSingleDomainTokenUrlResponse['result']->data->url;
        } catch (\Exception $e) {
            throw new \Exception(" : " . $e->getMessage());
        }
    }
}