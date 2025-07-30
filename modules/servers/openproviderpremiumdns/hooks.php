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
    // Check if product module matches
    if (
        !isset($vars['productinfo']['module']) ||
        strtolower($vars['productinfo']['module']) !== MODULE_IDENTIFIER
    ) {
        return; // Don't run this header
    }

    return <<<HTML
                <style>
                /* PremiumDNS banner styling */
                #nameserver-warning {
                    display: none; /* only show when the 3rd option is selected */
                    margin: 10px 0 0;
                    padding: 10px 12px;
                    background: #fff3cd;          /* Bootstrap-like warning background */
                    border: 1px solid #ffeeba;
                    color: #856404;
                    border-radius: 6px;
                    line-height: 1.45;
                }
                #nameserver-warning strong { font-weight: 600; }
                #nameserver-warning .ns-list { margin: 6px 0 0 0; padding-left: 18px; }
                </style>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        // ===== confirm before deleting a PDNS zone =====
                        const deleteBtn = document.querySelector("a[menuitemname='Custom Module Button Delete PDNS Zone']");
                        if (deleteBtn) {
                            deleteBtn.addEventListener("click", function (e) {
                                const confirmed = confirm("⚠️ Are you sure you want to delete this PDNS zone? This action cannot be undone.");
                                if (!confirmed) {
                                    e.preventDefault();
                                }
                            });
                        }

                        // ===== Banner for the 3rd flow: "I will use my existing domain and update my nameservers" =====
                        const ownDomainInput = document.querySelector("input[name='domainoption'][value='owndomain']");
                        if (!ownDomainInput) return;

                        const optionBlock   = ownDomainInput.closest(".option"); // wrapper that toggles .option-selected
                        const icheckDiv     = document.getElementById("iCheck-selowndomain"); // iCheck wrapper that toggles .checked
                        const ownDomainGrp  = document.getElementById("domainowndomain");     // section that toggles display: block/none

                        // Reuse an existing banner if present in markup, otherwise create it after the label
                        let banner = document.getElementById("nameserver-warning");
                        if (!banner) {
                            banner = document.createElement("div");
                            banner.id = "nameserver-warning";
                            banner.setAttribute("role", "alert");
                            banner.innerHTML =
                                "<strong>Important:</strong> After your Premium DNS Zone is created, you must " +
                                "<b>manually update your domain\\'s nameservers</b> to point to PremiumDNS Zone nameservers. " +
                                "Until you update nameservers, DNS for this domain will continue to resolve via your old provider.";
                            // insert right under the label inside the same option block
                            const label = ownDomainInput.closest("label");
                            if (label) {
                                label.insertAdjacentElement("afterend", banner);
                            } else if (optionBlock) {
                                optionBlock.insertAdjacentElement("afterbegin", banner);
                            } else {
                                // last resort
                                ownDomainInput.parentElement.insertAdjacentElement("afterend", banner);
                            }
                        }

                        // Visibility-only detection: no use of input.checked
                        function isOwnDomainSelected() {
                            // 1) The option block gets .option-selected when chosen
                            if (optionBlock && optionBlock.classList.contains("option-selected")) return true;

                            // 2) iCheck wrapper adds .checked when chosen (visible class change)
                            if (icheckDiv && icheckDiv.classList.contains("checked")) return true;

                            // 3) The "own domain" input group becomes visible when chosen
                            if (ownDomainGrp) {
                                const style = window.getComputedStyle(ownDomainGrp);
                                if (style.display !== "none") return true;
                            }
                            return false;
                        }

                        function syncBanner() {
                            banner.style.display = isOwnDomainSelected() ? "block" : "none";
                        }

                        // Initial state
                        syncBanner();

                        // Listen for native radio changes on the domainoption group
                        document.querySelectorAll("input[name='domainoption']").forEach(function (radio) {
                            radio.addEventListener("change", syncBanner);
                        });

                        // Observe class changes on elements that visually reflect selection
                        if (optionBlock) {
                            new MutationObserver(syncBanner).observe(optionBlock, { attributes: true, attributeFilter: ["class"] });
                        }
                        if (icheckDiv) {
                            new MutationObserver(syncBanner).observe(icheckDiv, { attributes: true, attributeFilter: ["class"] });
                        }
                        if (ownDomainGrp) {
                            new MutationObserver(syncBanner).observe(ownDomainGrp, { attributes: true, attributeFilter: ["style", "class"] });
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
