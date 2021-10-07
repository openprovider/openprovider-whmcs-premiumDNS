<?php

namespace OpenproviderPremiumDns\lib\paramsBuilder;

class SearchDomainsParamsCreator extends ParamsCreator
{
    public function createParameters(array $args, $client, string $method): array
    {
        $args = $this->formatDomainWithIdn($args);

        return parent::createParameters($args, $client, $method);
    }

    private function formatDomainWithIdn($args): array
    {
        if (isset($args['extension'])) {
            $args['extension'] = parent::idnEncode($args['extension']);
        }

        if (isset($args['full_name'])) {
            $args['full_name'] = parent::idnEncode($args['full_name']);
        }

        if (isset($args['domain_name_pattern'])) {
            $args['domain_name_pattern'] = parent::idnEncode($args['domain_name_pattern']);
        }

        return $args;
    }
}
