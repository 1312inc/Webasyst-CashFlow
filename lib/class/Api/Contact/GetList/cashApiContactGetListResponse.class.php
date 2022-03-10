<?php

final class cashApiContactGetListResponse extends cashApiAbstractResponse
{
    /**
     * @param array<cashApiContactGetListDto> $data
     */
    public function __construct(int $total, array $data, int $offset, int $limit)
    {
        parent::__construct(200);

        $this->response = [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'data' => [],
        ];
        foreach ($data as $datum) {
            $this->response['data'][] = [
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
