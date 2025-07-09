<?php

namespace OpenproviderPremiumDns\helper;

use WHMCS\Database\Capsule;
use OpenproviderPremiumDns\lib\xmlapihelper\API;

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

        // Let's get the URL.
        try {

            $xmlApiCall = new API();
            $premiumDnsModuleHelper = new OpenproviderPremiumDnsModuleHelper();

            $username = $params['configoption1'];
            $password = $params['configoption2'];

            $args = [
                'Username' => $username,
                'Password' => $password,
            ];

            $xmlApiCall->setParams($args, 0);

            $getDnsSingleDomainTokenUrlResponse = $xmlApiCall->getDnsSingleDomainTokenUrl($premiumDnsModuleHelper->getDomainArrayFromDomain($domainName), $params);

            return $getDnsSingleDomainTokenUrlResponse['url'];
        } catch (\Exception $e) {
            \logModuleCall(MODULE_NAME, 'Fetching generateSingleDomainTokenRequest', $domain->domain, $e->getMessage(), null, null);
            throw new \Exception('Error fetching DNS URL: ' . $e->getMessage());
        }
    }
}