<?php

use ApiPack1312\ApiParamsFetcher;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

/**
 * Class cashApiAbstractMethod
 */
abstract class cashApiNewAbstractMethod extends waAPIMethod
{
    public const METHOD_GET    = 'GET';
    public const METHOD_POST   = 'POST';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PUT    = 'PUT';

    /**
     * @var ApiParamsFetcher
     */
    private $apiParamsFetcher;

    /**
     * cashApiAbstractMethod constructor.
     */
    public function __construct()
    {
        $this->apiParamsFetcher = new ApiParamsFetcher(wa()->getRequest());

        parent::__construct();
    }

    public function execute()
    {
        try {
            $handlerResult = $this->run();

            $this->response = $handlerResult->getResponseBody();
            $this->http_status_code = $handlerResult->getStatus();
            wa()->getResponse()->setStatus($this->http_status_code);
        } catch (Exception $exception) {
            $this->response = cashApiErrorResponse::fromException($exception);
            $this->http_status_code = $this->response->getStatus() ?: $exception->getCode();
            wa()->getResponse()->setStatus($this->http_status_code);
        }
    }

    /**
     * @return cashApiResponseInterface
     */
    abstract function run(): cashApiResponseInterface;

    /**
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws ApiException
     */
    public function fromPost(string $name, bool $required = false, string $type = null, $format = null)
    {
        return $this->apiParamsFetcher->post($name, $required, $type, $format);
    }

    /**
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws ApiException
     */
    public function fromArray(array $data, string $name, bool $required = false, string $type = null, $format = null)
    {
        return $this->apiParamsFetcher->fromArray($data, $name, $required, $type, $format);
    }

    /**
     * @throws ApiMissingParamException
     * @throws ApiException
     * @throws ApiWrongParamException
     */
    public function fromGet(string $name, bool $required = false, string $type = null, $format = null)
    {
        return $this->apiParamsFetcher->get($name, $required, $type, $format);
    }

    public function getApiParamsFetcher(): ApiParamsFetcher
    {
        return $this->apiParamsFetcher;
    }

    /**
     * @return cashApiResponseInterface
     */
    public function __invoke()
    {
        return $this->run();
    }
}
