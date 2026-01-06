<?php

namespace OpenproviderPremiumDns\lib;

class ApiSettings
{
    /**
     * @var string
     */
    private $clientName;
    
    /**
     * @var string
     */
    private $url;
    
    /**
     * @var string
     */
    private $cteUrl;

    public function __construct(string $settingsPath)
    {
        $configs = file_get_contents($settingsPath);

        $this->clientName = $configs['client_name'] ?? 'whmcs-premiumdns-v1.0.0';
        $this->url = $configs['url'] ?? 'https://api.openprovider.eu';
        $this->cteUrl = $configs['cte_url'] ?? 'https://api.cte.openprovider.eu';
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName): void
    {
        $this->clientName = $clientName;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getCteUrl(): string
    {
        return $this->cteUrl;
    }

    /**
     * @param string $cteUrl
     */
    public function setCteUrl(string $cteUrl): void
    {
        $this->cteUrl = $cteUrl;
    }
}
