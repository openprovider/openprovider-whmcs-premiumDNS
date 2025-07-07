<?php

namespace OpenproviderPremiumDns\lib\paramsBuilder;

class ParamsCreatorFactory
{
    public function build($cmd): ParamsCreator
    {
        switch ($cmd) {
            case 'searchDomainRequest':
                return new SearchDomainsParamsCreator();
            case 'createDomainRequest':
            case 'modifyDomainRequest':
            case 'transferDomainRequest':
            case 'restoreDomainRequest':
            case 'renewDomainRequest':
            case 'resetAuthCodeDomainRequest':
                return new ModifyDomainParamsCreator();
            case 'checkDomainRequest':
                return new CheckDomainsParamsCreator();
            case 'retrievePriceDomainRequest':
                return new RetrievePriceDomainParamsCreator();
        }

        return new ParamsCreator();
    }
}
