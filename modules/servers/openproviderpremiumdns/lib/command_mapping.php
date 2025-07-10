<?php

namespace OpenproviderPremiumDns\lib;

use Openprovider\Api\Rest\Client\Dns\Api\ZoneServiceApi;
use Openprovider\Api\Rest\Client\Auth\Api\AuthApi;
use Openprovider\Api\Rest\Client\Domain\Api\DomainServiceApi;

class CommandMapping
{
    const COMMAND_MAP_METHOD = 'method';
    const COMMAND_MAP_CLASS  = 'apiClass';

    const COMMAND_MAP = [
        // LOGIN
        'generateAuthTokenRequest' => [
            self::COMMAND_MAP_METHOD => 'login',
            self::COMMAND_MAP_CLASS => AuthApi::class,
        ],

        // DNS
        'retrieveZoneDnsRequest' => [
            self::COMMAND_MAP_METHOD => 'getZone',
            self::COMMAND_MAP_CLASS => ZoneServiceApi::class,
        ],
        'modifyZoneDnsRequest' => [
            self::COMMAND_MAP_METHOD => 'updateZone',
            self::COMMAND_MAP_CLASS => ZoneServiceApi::class,
        ],
        'createZoneDnsRequest' => [
            self::COMMAND_MAP_METHOD => 'createZone',
            self::COMMAND_MAP_CLASS => ZoneServiceApi::class,
        ],
        'deleteZoneDnsRequest' => [
            self::COMMAND_MAP_METHOD => 'createZone',
            self::COMMAND_MAP_CLASS => ZoneServiceApi::class,
        ],

        // DOMAINS
        'searchDomainRequest' => [
            self::COMMAND_MAP_METHOD => 'listDomains',
            self::COMMAND_MAP_CLASS => DomainServiceApi::class,
        ],
    ];

    /**
     * @param string $command
     * @param string $field
     * @return string
     * @throws \Exception
     */
    public function getCommandMapping(string $command, string $field): string
    {
        if (!isset(self::COMMAND_MAP[$command][$field])) {
            throw new \Exception("Field {$field} not found into command mapping!");
        }

        return self::COMMAND_MAP[$command][$field];
    }
}
