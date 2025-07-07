<?php

namespace OpenproviderPremiumDns\lib\paramsBuilder;

class CheckDomainsParamsCreator extends ParamsCreator
{
    public function createParameters(array $args, $client, string $method): array
    {
        $args = $this->formatDomainNamesWithIdn($args);

        return parent::createParameters($args, $client, $method);
    }

    private function formatDomainNamesWithIdn($args): array
    {
        foreach ($args['domains'] as $index => $domain) {
            $args['domains'][$index]['name'] = parent::idnEncode($domain['name']);
            $args['domains'][$index]['extension'] = parent::idnEncode($domain['extension']);
        }

        return $args;
    }
}
