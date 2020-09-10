<?php

/**
 * Class cashApiAbstractMethod
 */
abstract class cashApiAbstractMethod extends waAPIMethod
{
    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';

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
     * @throws waAPIException
     */
    public function get($name, $required = false)
    {
        if (isset($this->jsonParams[$name])) {
            return $this->jsonParams[$name];
        }

        return parent::get($name, $required);
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return array|int|mixed|null
     * @throws waAPIException
     */
    public function post($name, $required = false)
    {
        if (isset($this->jsonParams[$name])) {
            return $this->jsonParams[$name];
        }

        return parent::post($name, $required);
    }

    public function execute()
    {
        try {
            $this->response = $this->run();
        } catch (Exception $exception) {
            $this->response = cashApiErrorResponse::fromException($exception);
        }
    }

    /**
     * @return mixed
     */
    abstract function run();

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return array|int|mixed|null
     * @throws waAPIException
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

        return parent::get($name, $required);
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
