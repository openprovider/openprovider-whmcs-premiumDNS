<?php

namespace OpenproviderPremiumDns\helper;

use OpenproviderPremiumDns\lib\ApiCommandNames;
use OpenproviderPremiumDns\lib\OpenproviderApi;
use OpenproviderPremiumDns\lib\Response;
use WHMCS\Database\Capsule;

class OpenproviderPremiumDnsModuleHelper
{
    /**
     * @var OpenproviderApi|null
     */
    private $api;

    /**
     * @var array module configuration
     */
    private $configs;

    public function __construct()
    {
        $this->configs = $this->loadConfigs();
    }

    /**
     * The method inits Openprovider api client.
     * It returns true if client was successfully initialized.
     *
     * @param string $username OP username
     * @param string $password OP password
     *
     * @return bool
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function initApi(string $username, string $password)
    {
        $this->api = new OpenproviderApi();
        $this->api->getConfig()->setHost($this->api->getApiSettings()->getUrl());

        $tokenRequest = $this->call(ApiCommandNames::GENERATE_AUTH_TOKEN_REQUEST, [
            'username' => $username,
            'password' => $password,
        ]);

        if ($tokenRequest->getCode() != 0) {
            return false;
        }

        $token = $tokenRequest->getData()['token'];

        $this->api->getConfig()->setToken($token);

        return true;
    }

    /**
     * Method load configs from configs.php file.
     *
     * @return array
     */
    public function loadConfigs(): array
    {
        $configsFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'configs.json';

        if ($configs = file_get_contents($configsFilePath)) {
            return (array) json_decode($configs);
        }

        return [];
    }

    /**
     * Method makes api request to OP.
     *
     * @param string $cmd api command
     * @param array $args arguments for api call
     *
     * @return Response
     */
    public function call(string $cmd, array $args = []): Response
    {
        $apiResponse = $this->api->call($cmd, $args);

        $this->logApiCall();

        return $apiResponse;
    }

    /**
     * Log api calls
     *
     * @return void
     */
    private function logApiCall(): void
    {
        \logModuleCall(
            MODULE_NAME,
            $this->api->getLastRequest()->getCommand(),
            json_encode($this->api->getLastRequest()->getArgs()),
            json_encode([
                'code' => $this->api->getLastResponse()->getCode(),
                'message' => $this->api->getLastResponse()->getMessage(),
                'data' => $this->api->getLastResponse()->getData(),
            ]),
            null,
            isset($this->api->getLastRequest()->getArgs()['password']) ? [
                $this->api->getLastRequest()->getArgs()['password'],
                htmlentities($this->api->getLastRequest()->getArgs()['password'])
            ] : []
        );
    }

    /**
     * Example:
     * 'domain.com' => ['domain', 'com']
     *
     * @param string $domain
     * @return array domain array ['domain name', 'domain extension']
     */
    function getDomainArrayFromDomain(string $domain): array
    {
        $domainArray = explode('.', $domain);
        if (count($domainArray) < 2) {
            throw new \Exception('Domain name has no tld.');
        }

        $domainSld = explode('.', $domain)[0];
        $domainTld = substr(str_replace($domainSld, '', $domain), 1);

        return [
            'name'      => $domainSld,
            'extension' => $domainTld
        ];
    }

    /**
     * Get server credentials (username/password) for a product.
     *
     * @param int $productId
     * @return array{username:string,password:string}
     */
    public function getCredentials(int $productId): array
    {
        // Find product & its server group
        $product = Capsule::table('tblproducts')
            ->select('servergroup')
            ->where('id', $productId)
            ->first();

        if (!$product || !$product->servergroup) {
            return ['username' => '', 'password' => ''];
        }

        // Pick a server from that group (first non-disabled)
        $server = Capsule::table('tblservergroupsrel as r')
            ->join('tblservers as s', 's.id', '=', 'r.serverid')
            ->where('r.groupid', $product->servergroup)
            ->where('s.disabled', 0)
            ->orderBy('s.id', 'asc')
            ->select('s.id', 's.username', 's.password')
            ->first();

        if (!$server) {
            return ['username' => '', 'password' => ''];
        }

        // Decrypt via Local API
        $plain = '';
        try {
            $resp = localAPI('DecryptPassword', ['password2' => $server->password]);
            if (!empty($resp['result']) && $resp['result'] === 'success' && isset($resp['password'])) {
                $plain = (string) $resp['password'];
            }
        } catch (\Throwable $e) {
            $plain = '';
        }

        return [
            'username' => (string) ($server->username ?? ''),
            'password' => (string) $plain,
        ];
    }
}