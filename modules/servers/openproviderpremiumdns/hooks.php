<?php

use WHMCS\Database\Capsule;
use OpenproviderPremiumDns\OpenproviderPremiumDnsModuleHelper;

const ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS = 'You have no product id in query parameters: "pid" does not exist!';
const ERROR_API_CLIENT_IS_NOT_CONFIGURED = 'Credentials are incorrect or api is not configured!';
const ERROR_DOMAIN_NOT_FOUND_IN_OPENPROVIDER = 'This domain not found in Openprovider or something went wrong!';

add_hook('ShoppingCartValidateDomain', 1, function($vars) {
    if ($vars['domainoption'] != 'owndomain') {
        return;
    }

    $productId = $_REQUEST['pid'];
    if (!$productId) {
        return ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS;
    }

    $productRow = Capsule::table('tblproducts')
        ->where('id', $productId)
        ->first();
    $username = $productRow->configoption1;
    $password = $productRow->configoption2;

    $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

    if (!$moduleHelper->initApi($username, $password)) {
        return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
    }

    $domainRequest = $moduleHelper->call('searchDomainRequest', [
        'fullName' => sprintf('%s.%s', $vars['sld'], $vars['tld'])
    ]);

    if ($domainRequest->getCode() != 0 || count($domainRequest->getData()['results']) == 0) {
        return ERROR_DOMAIN_NOT_FOUND_IN_OPENPROVIDER;
    }
});
