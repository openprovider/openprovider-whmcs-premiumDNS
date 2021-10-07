<?php

namespace OpenproviderPremiumDns\lib\paramsBuilder;

class RetrievePriceDomainParamsCreator extends ParamsCreator
{
    public function createParameters(array $args, $client, string $method): array
    {
        $args = $this->formatDomainNameWithIdn($args);

        return parent::createParameters($args, $client, $method);
    }

    private function formatDomainNameWithIdn($args): array
    {
        if (isset($args['domain_name'])) {
            $args['domain_name'] = parent::idnEncode($args['domain_name']);
        }

        if (isset($args['domain_extension'])) {
            $args['domain_extension'] = parent::idnEncode($args['domain_extension']);
        }

        return $args;
    }
}
