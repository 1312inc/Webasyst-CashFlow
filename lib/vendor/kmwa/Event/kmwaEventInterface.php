<?php

/**
 * Interface kmwaEventInterface
 */
interface kmwaEventInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return object
     */
    public function getObject();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param mixed $response
     *
     * @return kmwaEvent
     */
    public function setResponse($response);
}
