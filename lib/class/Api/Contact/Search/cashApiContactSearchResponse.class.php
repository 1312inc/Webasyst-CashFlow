<?php

final class cashApiContactSearchResponse extends cashApiAbstractResponse
{
    /**
     * cashApiSystemSearchContactsResponse constructor.
     *
     * @param
     */
    public function __construct($data)
    {
        parent::__construct(200);

        $this->response = [];
        foreach ($data as $datum) {
            $this->response[] = new cashApiContactSearchInfoDto(
                $datum['id'],
                $datum['value'],
                $datum['name'],
                $datum['firstname'],
                $datum['lastname'],
                $datum['photo_url'],
                $datum['photo_url_absolute'],
                $datum['label']
            );
        }
    }
}
