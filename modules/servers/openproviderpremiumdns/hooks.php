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

add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    return <<<HTML
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteBtn = document.querySelector("a[menuitemname='Custom Module Button Delete PDNS Zone']");
    if (deleteBtn) {
        deleteBtn.addEventListener("click", function (e) {
            const confirmed = confirm("⚠️ Are you sure you want to delete this PDNS zone? This action cannot be undone.");
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }
});
</script>
HTML;
});

add_hook('ClientAreaProductDetailsOutput', 1, function ($vars) {
    $output = '';

    if (isset($_GET['a']) && $_GET['a'] === 'TerminateAccount') {
        $output .= <<<HTML
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const msg = document.querySelector(".alert-success");
        if (msg) {
            setTimeout(function () {
                window.location.href = window.location.pathname + window.location.search.replace("&a=TerminateAccount", "") + "&deleted=1";
            }, 1500);
        }
    });
</script>
HTML;
    }

    if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
        $output .= '<div class="alert alert-success">Zone deleted successfully.</div>';
    }

    return $output;
});
