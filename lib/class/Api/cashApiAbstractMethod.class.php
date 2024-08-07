<?php

/**
 * Class cashApiAbstractMethod
 */
abstract class cashApiAbstractMethod extends waAPIMethod
{
    public const METHOD_GET    = 'GET';
    public const METHOD_POST   = 'POST';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PUT    = 'PUT';

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
                @ini_set('precision', 15);
            @ini_set('serialize_precision', -1);
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
            return $this->fromGet($name, $required);
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
            return $this->fromPost($name, $required);
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
            $this->http_status_code = $exception->getCode();
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
            $param = $this->fromPost($name, $required);
        } catch (waAPIException $exception) {
        }

        if ($param !== null) {
            return $param;
        }

        try {
            return $this->fromGet($name, $required);
        } catch (waAPIException $exception) {
            $this->http_status_code = $exception->getCode();
            throw new cashApiMissingParamException($name);
        }
    }

    private function fromGet($name, $required = false)
    {
        $v = waRequest::get($name);
        if ($required && $v === null) {
            throw new waAPIException('invalid_param', 'Required parameter is missing: ' . $name, 400);
        }

        return $v;
    }

    private function fromPost($name, $required = false)
    {
        $v = waRequest::post($name);
        if ($required && $v === null) {
            throw new waAPIException('invalid_param', 'Required parameter is missing: ' . $name, 400);
        }

        return $v;
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

    /**
     * @return cashApiResponseInterface
     */
    public function __invoke()
    {
        return $this->run();
    }
}
