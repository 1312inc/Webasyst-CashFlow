<?php

/**
 * Class cashApiSystemSearchContactsResponse
 */
class cashApiSystemSearchContactsResponse extends cashApiAbstractResponse
{
    /**
     * cashApiSystemSearchContactsResponse constructor.
     *
     * @param
     */
    public function __construct($data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
