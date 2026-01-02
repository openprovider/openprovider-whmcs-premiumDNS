<?php

namespace OpenproviderPremiumDns\lib;

use OpenproviderPremiumDns\lib\ApiCommandNames;
use OpenproviderPremiumDns\helper\OpenproviderPremiumDnsModuleHelper;

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}

class RestCurlApi
{
    public $url;
    public $apiUrl = '';
    protected $apiSettings;
    
    public function __construct()
    {
        $this->apiSettings = new ApiSettings(__DIR__ . DIRECTORY_SEPARATOR . 'api.settings.json');
        $this->url = $this->apiSettings->getUrl();
    }

    public function generateToken(string $username, string $password)
    {
        try {
            
            $data = array(
                'username' => $username,
                'password' => "$password",
            );
            $this->apiUrl = $this->url . '/v1beta/auth/login';
            $header = array(
                'Content-Type: application/json',
                'X-Client: ' . $this->apiSettings->getClientName()
            );

            $res =  $this->__curlCall("POST", $data, $this->apiUrl, $header, ApiCommandNames::GENERATE_AUTH_TOKEN_REQUEST);

            if ($res['httpcode'] !== 200) {
                throw new \Exception($res['result']->desc, $res['httpcode']);
            }

            $token = $res['result']->data->token;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode() == $res['httpcode'] ? $e->getMessage() : 'Failed to generate auth token');
        }

        return $token;
    }

    /**
     * Get the DNS Single Domain Token
     * @param array 
     * @return array
     * @throws \Exception
     */
    public function getDnsSingleDomainTokenUrl($params)
    {
        try {
            $productId = $params['pid'];
            if (!$productId) {
                throw new \Exception(ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS);
            }

            $moduleHelper = new OpenproviderPremiumDnsModuleHelper();

            // get credentials array with productId
            $credentials = $moduleHelper->getCredentials($productId);

            if (empty($credentials['username']) || empty($credentials['password'])) {
                throw new \Exception(ERROR_API_CLIENT_IS_NOT_CONFIGURED);
            }

            $token = $this->generateToken($credentials['username'], $credentials['password']);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        try {
            $this->apiUrl = $this->url . '/v1beta/dns/domain-token';
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'X-Client: ' . $this->apiSettings->getClientName()
            );
            $data = array(
                'domain' => $params['domain'],
                'zone_provider' => ZONE_PROVIDER_SECTIGO, 
            );
            $res =  $this->__curlCall("POST", $data, $this->apiUrl, $header, ApiCommandNames::GENERATE_SINGLE_DOMAIN_TOKEN_REQUEST);

            if ($res['httpcode'] !== 200) {
               throw new \Exception($res['result']->desc, $res['httpcode']);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getCode() == $res['httpcode'] ? $e->getMessage() : 'Failed to get DNS Single Domain Token URL');
        }

        return $res;
    }

    public function __curlCall($method, $data = null, $apiUrl = null, $header  = "", $action = '')
    {
        $curl = curl_init();
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, (count($data) ? json_encode($data) : ""));
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, (count($data) ? json_encode($data) : ""));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, (count($data) ? json_encode($data) : ""));
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($curl);
       
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            throw new \Exception(curl_error($curl));
        }
        curl_close($curl);

        if(isset($data['password'])){
            $data['password'] = '********';
        }

        \logModuleCall(MODULE_NAME, $action, $data, json_decode($response));
        return ['httpcode' => $httpCode, 'result' => json_decode($response)];
    }

    public function postCurl($data,$token, $baseUrl,$action ){
        $this->apiUrl = 'https://' . $this->url .$baseUrl;

        $header = array(
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        );

        $res =  $this->__curlCall("POST", $data, $this->apiUrl, $header, $action);
        return $res;
    }

    public function deleteCurl($token, $baseUrl,$action ){
        $this->apiUrl = 'https://' . $this->url .$baseUrl;

        $header = array(
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        );

        $res =  $this->__curlCall("DELETE",[], $this->apiUrl, $header, $action);
        return $res;
    }

    public function getCurl($token, $baseUrl,$action ){
        $this->apiUrl = 'https://' . $this->url .$baseUrl;

        $header = array(
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        );

        $res =  $this->__curlCall("GET",[], $this->apiUrl, $header, $action);
        return $res;
    }

}