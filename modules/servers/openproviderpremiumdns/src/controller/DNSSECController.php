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
            $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

            if (!$moduleHelper->initApi($params['configoption1'], localAPI('DecryptPassword', ['password2' => $params['configoption2']])['password'])) {
                throw new Exception('Failed to initialize Authentication API');
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

            return ['templatefile' => 'manageDnssec', 'vars' => [
                'serviceId' => $params['serviceid'],
                'isDnssecEnabled' => $isDnssecEnabled,
                'dnssecKey' => $dnssecKey,
                'cssModuleUrl' => Configuration::getCssModuleUrl('dnssec'),
                'jsModuleUrl' => Configuration::getJsModuleUrl('dnssec'),
            ]];
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            \logModuleCall(
                MODULE_NAME,
                __FUNCTION__,
                $e->getMessage(),
                $e->getTraceAsString(),
                null
            );

            return $e->getMessage();
        }

        return SUCCESS_MESSAGE;
    }

    public function toggleDnssecStatus(array $params)
    {
        try {
            $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

            if (!$moduleHelper->initApi($params['configoption1'], localAPI('DecryptPassword', ['password2' => $params['configoption2']])['password'])) {
                throw new \Exception('Failed to initialize API');
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
