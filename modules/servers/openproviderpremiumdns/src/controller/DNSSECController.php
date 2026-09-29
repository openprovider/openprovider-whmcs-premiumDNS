<?php

namespace OpenproviderPremiumDns\controller;

use OpenproviderPremiumDns\helper\OpenproviderPremiumDnsModuleHelper;
use OpenproviderPremiumDns\lib\ApiCommandNames;
use OpenproviderPremiumDns\config\Configuration;
use WHMCS\Database\Capsule;
use Exception;

class DNSSECController
{
    public function showClientAreaDnssecPage(array $params)
    {
        try {
            $productId = $params['pid'];
            if (!$productId) {
                throw new Exception(ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS);
            }

            $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

            // get credentials array with productId
            $credentials = $moduleHelper->getCredentials($productId);

            if (empty($credentials['username']) || empty($credentials['password'])) {
                throw new Exception(ERROR_API_CLIENT_IS_NOT_CONFIGURED);
            }

            if (!$moduleHelper->initApi($credentials['username'], $credentials['password'])) {
                throw new Exception(ERROR_API_CLIENT_IS_NOT_CONFIGURED);
            }

            $dnsZoneResponse = $moduleHelper->call(ApiCommandNames::RETRIEVE_ZONE_DNS_REQUEST, [
                'name' => $params['domain'],
                'provider' => ZONE_PROVIDER_SECTIGO,
                'with_dnskey' => true,
            ]);

            if ($dnsZoneResponse->getCode() != 0) {
                throw new Exception($dnsZoneResponse->getMessage());
            }

            $isDnssecEnabled = $dnsZoneResponse->getData()['premium_dns']['sectigo']['secured'] ?? false;

            $dnssecKeys = $dnsZoneResponse->getData()['dnskey'] ?? "";

            // split the dnssecKeys string into an array
            $dnssecKeysArray = explode(" ", $dnssecKeys);
            $dnssecKey = [
                'flags'    => $dnssecKeysArray[0],
                'alg'      => $dnssecKeysArray[2],
                'protocol' => $dnssecKeysArray[1],
                'pubKey'   => $dnssecKeysArray[3],
            ];

            return $this->renderManageDnssecPage(
                $params['serviceid'],
                $isDnssecEnabled,
                $dnssecKey
            );
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Custom actions triggered via ClientAreaCustomButtonArray (modop=custom&a=...)
     * are not rendered through WHMCS's templatefile/vars mechanism, so the markup
     * is built and returned as a plain HTML string here instead (the same
     * string-return convention toggleDnssecStatus() already uses). Do not echo+exit
     * here: that skips WHMCS's normal request shutdown (session/cookie handling)
     * and was observed to log the client and admin session out entirely.
     */
    private function renderManageDnssecPage($serviceId, $isDnssecEnabled, array $dnssecKey): string
    {
        $cssModuleUrl = htmlspecialchars(Configuration::getCssModuleUrl('dnssec'), ENT_QUOTES);
        $jsModuleUrl = htmlspecialchars(Configuration::getJsModuleUrl('dnssec'), ENT_QUOTES);
        $serviceId = htmlspecialchars((string) $serviceId, ENT_QUOTES);
        $toggleLabel = $isDnssecEnabled ? 'Deactivate DNSSEC' : 'Activate DNSSEC';

        $flags = htmlspecialchars((string) $dnssecKey['flags'], ENT_QUOTES);
        $alg = htmlspecialchars((string) $dnssecKey['alg'], ENT_QUOTES);
        $pubKey = htmlspecialchars((string) $dnssecKey['pubKey'], ENT_QUOTES);

        if ($isDnssecEnabled) {
            $disabledAlertClass = 'dnssec-alert-on-disabled alert alert-warning hidden';
            $enabledAlertClass = 'dnssec-alert-on-enabled alert alert-warning';
            $enabledNewAlertHtml = '';
            $tableClass = 'dnssec-records-table table table-bordered';
        } else {
            $disabledAlertClass = 'dnssec-alert-on-disabled alert alert-warning';
            $enabledAlertClass = 'dnssec-alert-on-enabled alert alert-warning hidden';
            $enabledNewAlertHtml = '<div class="dnssec-alert-on-enabled-new alert alert-warning hidden">
            DNSSEC has not been activated yet. Please activate to add a DNSSEC record for this premium DNS zone.
        </div>';
            $tableClass = 'dnssec-records-table table table-bordered hidden';
        }

        return <<<HTML
        <link rel="stylesheet" href="{$cssModuleUrl}">
        <script src="{$jsModuleUrl}"></script>
        <section class="js-dnssec-module">
            <div class="row d-flex align-items-center justify-content-between mb-3">
                <h2 class="mb-0">Manage DNSSEC Records</h2>

                <form id="dnssecToggleForm" class="mb-0 d-flex align-items-center gap-2">
                    <input type="hidden" name="id" value="{$serviceId}" />
                    <input type="hidden" name="modop" value="custom" />
                    <input type="hidden" name="a" value="toggle_dnssec" />
                    <button class="btn btn-primary" type="submit" id="dnssecToggleBtn">
                        {$toggleLabel}
                    </button>
                    <div id="dnssecLoading" class="spinner-border text-primary ml-2" role="status" style="display: none;">
                        <span style="display: none;">Loading...</span>
                    </div>
                </form>
            </div>

            <div class="dnssec-alert-error-message alert alert-danger hidden">
                <span id="dnssecErrorMessage"></span>
            </div>

            <div class="{$disabledAlertClass}">
                DNSSEC is not active on this domain.
            </div>
            <div class="{$enabledAlertClass}">
                DNSSEC is active for this domain. If you deactivate DNSSEC, existing key will be deleted from this premium DNS zone.
            </div>
            {$enabledNewAlertHtml}

            <table class="{$tableClass}">
                <thead>
                    <tr>
                        <th>Flags</th>
                        <th>Algorithm</th>
                        <th>Public key</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{$flags}</td>
                    <td>{$alg}</td>
                    <td class="break-word">{$pubKey}</td>
                </tr>
                </tbody>
            </table>
        </section>
        HTML;
    }

    public function toggleDnssecStatus(array $params)
    {
        try {
            $productId = $params['pid'];
            if (!$productId) {
                throw new Exception(ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS);
            }

            $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

            // get credentials array with productId
            $credentials = $moduleHelper->getCredentials($productId);

            if (empty($credentials['username']) || empty($credentials['password'])) {
                throw new Exception(ERROR_API_CLIENT_IS_NOT_CONFIGURED);
            }

            if (!$moduleHelper->initApi($credentials['username'], $credentials['password'])) {
                throw new Exception(ERROR_API_CLIENT_IS_NOT_CONFIGURED);
            }

            $dnsZoneResponse = $moduleHelper->call(ApiCommandNames::RETRIEVE_ZONE_DNS_REQUEST, [
                'name' => $params['domain'],
                'provider' => ZONE_PROVIDER_SECTIGO,
                'with_dnskey' => true,
            ]);

            if ($dnsZoneResponse->getCode() != 0) {
                throw new Exception($dnsZoneResponse->getMessage());
            }

            $isDnssecEnabled = $dnsZoneResponse->getData()['premium_dns']['sectigo']['secured'] ?? false;
            $dnsZoneId = $dnsZoneResponse->getData()['id'];
            $masterIP = $dnsZoneResponse->getData()['ip'];
            $isSpamExpertEnabled = $dnsZoneResponse->getData()['is_spamexperts_enabled'];

            $apiResponse = $moduleHelper->call(ApiCommandNames::MODIFY_ZONE_DNS_REQUEST, [
                'id' => $dnsZoneId,
                'name' => $params['domain'],
                'provider' => ZONE_PROVIDER_SECTIGO,
                'premium_dns' => [
                    ZONE_PROVIDER_SECTIGO => [
                        'secured' => $isDnssecEnabled ? false : true,
                        'autorenew' => true,
                    ],
                ],
                'master_ip' => $masterIP,
                'is_spamexperts_enabled' => $isSpamExpertEnabled,
            ]);

            if ($apiResponse->getCode() != 0) {
                throw new Exception($apiResponse->getMessage());
            }

            $serviceId = $params['serviceid'];
            $fieldName = DNSSEC_CUSTOM_FIELD_NAME;
            $newDnssecValue = $isDnssecEnabled ? "" : "on";

            // Find the custom field ID
            $customField = Capsule::table('tblcustomfields')
                ->where('type', 'product')
                ->where('relid', $params['pid']) // Product ID
                ->where('fieldname', $fieldName)
                ->first();

            if ($customField) {
                // Update or insert value
                $existing = Capsule::table('tblcustomfieldsvalues')
                    ->where('fieldid', $customField->id)
                    ->where('relid', $serviceId)
                    ->first();

                if ($existing) {
                    Capsule::table('tblcustomfieldsvalues')
                        ->where('id', $existing->id)
                        ->update(['value' => $newDnssecValue]);
                } else {
                    Capsule::table('tblcustomfieldsvalues')
                        ->insert([
                            'fieldid' => $customField->id,
                            'relid' => $serviceId,
                            'value' => $newDnssecValue,
                        ]);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return SUCCESS_MESSAGE;
    }
}
