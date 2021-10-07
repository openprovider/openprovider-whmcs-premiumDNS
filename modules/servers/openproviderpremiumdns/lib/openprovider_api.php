<?php

namespace OpenproviderPremiumDns\lib;

use Openprovider\Api\Rest\Client\Base\Configuration;
use GuzzleHttp6\Client as HttpClient;
use OpenproviderPremiumDns\lib\paramsBuilder\ParamsCreatorFactory;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class OpenproviderApi
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var CommandMapping
     */
    private $commandMapping;

    /**
     * @var ApiConfig
     */
    private $apiConfig;

    /**
     * @var ParamsCreatorFactory
     */
    private $paramsCreatorFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * @var LastRequest
     */
    private $lastRequest;

    /**
     * @var ApiSettings
     */
    private $apiSettings;

    public function __construct()
    {
        $this->configuration = new Configuration();
        $this->commandMapping = new CommandMapping();
        $this->apiConfig = new ApiConfig();
        $this->paramsCreatorFactory = new ParamsCreatorFactory();
        $this->serializer = new Serializer([new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())]);

        $this->apiSettings = new ApiSettings(__DIR__ . DIRECTORY_SEPARATOR . 'api.settings.json');

        $this->httpClient = new HttpClient([
            'headers' => [
                'X-Client' => $this->apiSettings->getClientName()
            ]
        ]);
    }

    /**
     * @param string $cmd
     * @param array $args
     *
     * @return Response
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function call(string $cmd, array $args = []): Response
    {
        $response = new Response();

        try {
            $apiClass = $this->commandMapping->getCommandMapping($cmd, CommandMapping::COMMAND_MAP_CLASS);
            $apiMethod = $this->commandMapping->getCommandMapping($cmd, CommandMapping::COMMAND_MAP_METHOD);
        } catch (\Exception $e) {
            return $this->failedResponse($response, $e->getMessage(), $e->getCode());
        }

        $service = new $apiClass($this->httpClient, $this->configuration);

        $service->getConfig()->setHost($this->apiConfig->getHost());

        if ($this->apiConfig->getToken()) {
            $service->getConfig()->setAccessToken($this->apiConfig->getToken());
        }

        $this->lastRequest = new LastRequest();
        $this->lastRequest->setArgs($args);
        $this->lastRequest->setCommand($cmd);

        try {
            $paramsCreator = $this->paramsCreatorFactory->build($cmd);
            $requestParameters = $paramsCreator->createParameters($args, $service, $apiMethod);
            $reply = $service->$apiMethod(...$requestParameters);
        } catch (\Exception $e) {
            $responseData = $this->serializer->normalize(
                    json_decode(substr($e->getMessage(), strpos($e->getMessage(), 'response:') + strlen('response:')))
                ) ?? $e->getMessage();

            $return = $this->failedResponse(
                $response,
                $responseData['desc'] ?? $e->getMessage(),
                $responseData['code'] ?? $e->getCode()
            );
            $this->lastResponse = $return;

            return $return;
        }

        $data = $this->serializer->normalize($reply->getData());

        $return = $this->successResponse($response, $data);
        $this->lastResponse = $return;

        return $return;
    }

    /**
     * @return ApiConfig
     */
    public function getConfig(): ApiConfig
    {
        return $this->apiConfig;
    }

    /**
     * @param Response $response
     * @param array $data
     *
     * @return Response
     */
    private function successResponse(Response $response, array $data): Response
    {
        $response->setTotal($data['total'] ?? 0);
        unset($data['total']);

        $response->setCode($data['code'] ?? 0);
        unset($data['code']);

        $response->setData($data);

        return $response;
    }

    /**
     * @param Response $response
     * @param string $message
     * @param int $code
     *
     * @return Response
     */
    private function failedResponse(Response $response, string $message, int $code): Response
    {
        $response->setMessage($message);
        $response->setCode($code);

        return $response;
    }

    /**
     * @return LastRequest
     */
    public function getLastRequest(): LastRequest
    {
        return $this->lastRequest;
    }

    /**
     * @return Response
     */
    public function getLastResponse(): Response
    {
        return $this->lastResponse;
    }

    public function getApiSettings(): ApiSettings
    {
        return $this->apiSettings;
    }
}
