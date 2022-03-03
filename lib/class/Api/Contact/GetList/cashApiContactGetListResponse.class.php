<?php

final class cashApiContactGetListResponse extends cashApiAbstractResponse
{
    /**
     * @param array<cashApiContactGetListDto> $data
     */
    public function __construct($data)
    {
        parent::__construct(200);

        $this->response = [];
        foreach ($data as $datum) {
            $this->response[] = [
                'id' => $datum->getId(),
                'name' => $datum->getName(),
                'firstname' => $datum->getFirstname(),
                'lastname' => $datum->getFirstname(),
                'photo_url' => $datum->getPhotoUrl(),
                'photo_url_absolute' => $datum->getPhotoUrlAbsolute(),
                'stat' => $datum->getStat(),
            ];
        }
    }
}
