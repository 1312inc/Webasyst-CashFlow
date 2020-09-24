<?php

/**
 * Class cashApiAbstractMethod
 */
abstract class cashApiAbstractMethod extends waAPIMethod
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT    = 'PUT';

    /**
     * @var array
     */
    protected $jsonParams = [];

    /**
     * cashApiAbstractMethod constructor.
     */
    public function __construct()
    {
        // check for json request type
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strcmp($content_type, 'application/json') === 0) {
            $this->jsonParams = json_decode(file_get_contents('php://input'), true);
        }

        parent::__construct();
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return array|int|mixed|null
     * @throws cashApiMissingParamException
     */
    public function get($name, $required = false)
    {
        if (isset($this->jsonParams[$name])) {
            return $this->jsonParams[$name];
        }

        try {
            return parent::get($name, $required);
        } catch (waAPIException $exception) {
            throw new cashApiMissingParamException($name);
        }
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return array|int|mixed|null
     * @throws cashApiMissingParamException
     */
    public function post($name, $required = false)
    {
        if (isset($this->jsonParams[$name])) {
            return $this->jsonParams[$name];
        }

        try {
            return parent::post($name, $required);
        } catch (waAPIException $exception) {
            throw new cashApiMissingParamException($name);
        }
    }

    public function execute()
    {
        try {
            $handlerResult = $this->run();

            $this->response = $handlerResult->getResponseBody();
            wa()->getResponse()->setStatus($handlerResult->getStatus());
        } catch (Exception $exception) {
            $this->response = cashApiErrorResponse::fromException($exception);
            wa()->getResponse()->setStatus($this->response->getStatus());
        }
    }

    /**
     * @return cashApiResponseInterface
     */
    abstract function run(): cashApiResponseInterface;

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return array|int|mixed|null
     * @throws cashApiMissingParamException
     */
    protected function param($name, $required = false)
    {
        if (isset($this->jsonParams[$name])) {
            return $this->jsonParams[$name];
        }

        $param = null;
        try {
            $param = parent::post($name, $required);
        } catch (waAPIException $exception) {
        }

        if ($param !== null) {
            return $param;
        }

        try {
            return parent::get($name, $required);
        } catch (waAPIException $exception) {
            throw new cashApiMissingParamException($name);
        }
    }

    /**
     * @param object $requestDto
     *
     * @return object
     * @throws waAPIException
     */
    protected function fillRequestWithParams($requestDto)
    {
        foreach (get_object_vars($requestDto) as $prop => $value) {
            $requestDto->$prop = $this->param($prop, $value !== null);
        }

        return $requestDto;
    }
}
