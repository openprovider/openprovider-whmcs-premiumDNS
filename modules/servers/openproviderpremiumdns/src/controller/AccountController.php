<?php

namespace OpenproviderPremiumDns\controller;

use OpenproviderPremiumDns\helper\OpenproviderPremiumDnsModuleHelper;
use OpenproviderPremiumDns\lib\ApiCommandNames;
use WHMCS\Database\Capsule;
use Exception;

class AccountController
{
    public function createAccount(array $params): string
    {
        $isDNSSECEnabled = (
            isset($params['customfields'][DNSSEC_CUSTOM_FIELD_NAME]) &&
            $params['customfields'][DNSSEC_CUSTOM_FIELD_NAME] === 'on'
        ) ? true : false;

        $productId = $params['pid'];
        if (!$productId) {
            return ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS;
        }

        $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

        // get credentials array with productId
        $credentials = $moduleHelper->getCredentials($productId);

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
        }

        if (!$moduleHelper->initApi($credentials['username'], $credentials['password'])) {
            return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
        } 
        try {
            $dnsZoneResponse = $moduleHelper->call(ApiCommandNames::RETRIEVE_ZONE_DNS_REQUEST, [
                'name' => $params['domain'],
                'provider' => ZONE_PROVIDER_SECTIGO
            ]);

            if ($dnsZoneResponse->getCode() == 0) {
                $modifyZoneResponse = $moduleHelper->call(ApiCommandNames::MODIFY_ZONE_DNS_REQUEST, [
                    'name' => $params['domain'],
                    'provider' => ZONE_PROVIDER_SECTIGO,
                ]);
                if ($modifyZoneResponse->getCode() != 0) {
                    return $modifyZoneResponse->getMessage();
                }

                return SUCCESS_MESSAGE;
            }
        } catch (Exception $e){
            return $e->getMessage();
        }

        try {
            $domainArray = $moduleHelper->getDomainArrayFromDomain($params['domain']);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $createDnsZoneResponse = $moduleHelper->call(ApiCommandNames::CREATE_ZONE_DNS_REQUEST, [
            'domain' => $domainArray,
            'records' => [],
            'type' => CREATE_DNS_ZONE_TYPE,
            'provider' => ZONE_PROVIDER_SECTIGO,
            'secured' => $isDNSSECEnabled,
        ]);

        if ($createDnsZoneResponse->getCode() != 0) {
            return $createDnsZoneResponse->getMessage();
        }

        return SUCCESS_MESSAGE;
    }

    public function terminateAccount(array $params): string
    {
        $productId = $params['pid'];
        if (!$productId) {
            return ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS;
        }

        $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

        // get credentials array with productId
        $credentials = $moduleHelper->getCredentials($productId);

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
        }

        if (!$moduleHelper->initApi($credentials['username'], $credentials['password'])) {
            return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
        }

        $deleteZoneResponse = $moduleHelper->call(ApiCommandNames::DELETE_ZONE_DNS_REQUEST, [
            'name'     => $params['domain'],
            'provider' => ZONE_PROVIDER_SECTIGO,
        ]);

        if ($deleteZoneResponse->getCode() != 0) {
            return 'Zone deletion failed: ' . $deleteZoneResponse->getMessage();
        }

        try {
            Capsule::table('tblhosting')
                ->where('id', $params['serviceid'])
                ->update([
                    'domainstatus'    => 'Terminated',
                    'termination_date' => date('Y-m-d'),
                    'nextduedate'     => '0000-00-00',
                ]);

            return SUCCESS_MESSAGE;
        } catch (Exception $e) {
            return 'Zone deleted, but DB termination failed: ' . $e->getMessage();
        }
    }
}
