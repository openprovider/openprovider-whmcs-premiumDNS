<?php

namespace OpenproviderPremiumDns\lib\paramsBuilder;

class ModifyDomainParamsCreator extends ParamsCreator
{
    public function createParameters(array $args, $client, string $method): array
    {
        $args = $this->formatDomainNameWithIdn($args);

        return parent::createParameters($args, $client, $method);
    }

    private function formatDomainNameWithIdn($args): array
    {
        if (isset($args['domain']['name'])) {
            $args['domain']['name'] = parent::idnEncode($args['domain']['name']);
        }

        if (isset($args['domain']['extension'])) {
            $args['domain']['extension'] = parent::idnEncode($args['domain']['extension']);
        }

        return $args;
    }
}
