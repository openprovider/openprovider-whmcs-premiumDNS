<?php

require_once __DIR__ . '/openproviderpremiumdns.php';

use WHMCS\Database\Capsule;
use OpenproviderPremiumDns\helper\OpenproviderPremiumDnsModuleHelper;
use OpenproviderPremiumDns\lib\ApiCommandNames;
use WHMCS\View\Menu\Item as MenuItem;

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

    $domainRequest = $moduleHelper->call(ApiCommandNames::SEARCH_DOMAIN_REQUEST, [
        'fullName' => sprintf('%s.%s', $vars['sld'], $vars['tld'])
    ]);

    if ($domainRequest->getCode() != 0 || count($domainRequest->getData()['results']) == 0) {
        return ERROR_DOMAIN_NOT_FOUND_IN_OPENPROVIDER;
    }
});

add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar) {
    $serviceDetailsMenu = $primarySidebar->getChild('Service Details Actions');

    if ($serviceDetailsMenu) {
        $targetItem = $serviceDetailsMenu->getChild('Custom Module Button Activate/Deactivate DNSSEC');

        if ($targetItem) {
            $serviceDetailsMenu->removeChild('Custom Module Button Activate/Deactivate DNSSEC');
        }
    }
});
