<?php

final class cashApiContactGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiContactGetListRequest $request
     *
     * @return array<cashApiContactSearchInfoDto>
     * @throws waException
     */
    public function handle($request)
    {
        $data = (new cashQueryGetContractors())->getContractors(
            $request->getOffset(),
            $request->getLimit(),
            wa()->getUser()
        );

        $response = [];
        foreach ($data as $datum) {
            $contact = new waContact($datum['contact_id']);
            $photo = waContact::getPhotoUrl($contact->getId(), $contact->get('photo'), 96);
            $dto = new cashApiContactGetListDto(
                $contact->getId(),
                $contact->getName(),
                $contact->get('firstname'),
                $contact->get('lastname'),
                $photo,
                wa()->getConfig()->getHostUrl() . $photo,
                array_map(function (array $statData) {
                    return [
                        'currency' => $statData['currency'],
                        'data' => cashStatOnDateDto::createFromArray($statData['stat']),
                    ];
                }, $datum['stat'])
            );

            $response[] = $dto;
        }

        return $response;
    }
}
