<?php

namespace OpenproviderPremiumDns\controller;

use OpenproviderPremiumDns\helper\DNS;
use Exception;

class DNSController
{
    public function showManagePdns(array $params)
    {
        try {
            if ($url = DNS::getDnsUrlOrFail($params)) {
                $urlOne = $_SERVER['HTTP_REFERER'];
                $url_decoded = html_entity_decode($urlOne);


                // JavaScript confirm dialog
                echo '<script type="text/javascript">
                        document.addEventListener("DOMContentLoaded", function() {
                            var userConfirmed = confirm("Do you want to open in New Tab?");
                            if (userConfirmed) {
                                var newWindow = window.open("' . $url . '", "_blank"); // Open OP DNS management page in a new tab
                                if (newWindow) {
                                    window.location.href = "' . $url_decoded . '"; // Redirect to previous page
                                    newWindow.focus(); // Focus on the new tab
                                } else {
                                    alert("New tab opening blocked! Please allow it for this site.");
                                    window.location.href = "' . $url . '"; // Redirect to OP DNS management page
                                }
                            } else {
                                window.location.href = "' . $url . '"; // Redirect to OP DNS management page
                            }
                        });
                        </script>';
                exit;
            } else {
                throw new Exception("Failing to get DNS URL. Please check error logs for more details.");
            }
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            \logModuleCall(
                MODULE_NAME,
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            return $e->getMessage();
        }

        return SUCCESS_MESSAGE;
    }
}
