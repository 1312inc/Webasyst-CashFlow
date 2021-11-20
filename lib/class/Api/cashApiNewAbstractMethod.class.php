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
            wa()->getResponse()->setStatus($handlerResult->getStatus());
        } catch (Exception $exception) {
            $this->response = cashApiErrorResponse::fromException($exception);
            wa()->getResponse()->setStatus($this->response->getStatus() ?: $exception->getCode());
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
     * @throws ApiException
     * @throws ApiWrongParamException
     */
    public function fromGet(string $name, bool $required = false, string $type = null, $format = null)
    {
        return $this->apiParamsFetcher->get($name, $required, $type, $format);
    }

    /**
     * @return cashApiResponseInterface
     */
    public function __invoke()
    {
        return $this->run();
    }
}
