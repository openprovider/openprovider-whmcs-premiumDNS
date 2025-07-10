<?php

namespace OpenproviderPremiumDns\controller;

use OpenproviderPremiumDns\helper\OpenproviderPremiumDnsModuleHelper;
use OpenproviderPremiumDns\lib\ApiCommandNames;
use Exception;

class AccountController
{
    public function createAccount(array $params): string
    {
        $username = $params['configoption1'];
        $password = $params['configoption2'];

        $isDNSSECEnabled = (
            isset($params['customfields']["DNSSEC_CUSTOM_FIELD_NAME"]) &&
            $params['customfields'][DNSSEC_CUSTOM_FIELD_NAME] === 'on'
        ) ? true : false;

        $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

        if (!$moduleHelper->initApi($username, $password)) {
            return ERROR_API_CLIENT_IS_NOT_CONFIGURED;
        }

        $dnsZoneResponse = $moduleHelper->call(ApiCommandNames::RETRIEVE_ZONE_DNS_REQUEST, [
            'name' => $params['domain'],
        ]);

        if ($dnsZoneResponse->getCode() == 0) {
            $modifyZoneResponse = $moduleHelper->call(ApiCommandNames::MODIFY_ZONE_DNS_REQUEST, [
                'name' => $params['domain'],
                'provider' => 'sectigo',
            ]);

            if ($modifyZoneResponse->getCode() != 0) {
                return $modifyZoneResponse->getMessage();
            }

            return SUCCESS_MESSAGE;
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
            'provider' => 'sectigo',
            'secured' => $isDNSSECEnabled,
        ]);

        if ($createDnsZoneResponse->getCode() != 0) {
            return $createDnsZoneResponse->getMessage();
        }

        return SUCCESS_MESSAGE;
    }
}
