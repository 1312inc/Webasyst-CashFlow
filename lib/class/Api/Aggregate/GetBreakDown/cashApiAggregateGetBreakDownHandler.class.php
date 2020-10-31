<?php

/**
 * Class cashApiAggregateGetBreakDownHandler
 */
final class cashApiAggregateGetBreakDownHandler implements cashApiHandlerInterface
{
    private $contractContacts = [];

    private $categories;

    /**
     * @param cashApiAggregateGetBreakDownRequest $request
     *
     * @return cashApiAggregateGetBreakDownDto[]
     * @throws waException
     */
    public function handle($request)
    {
        $user = wa()->getUser();
        $paramsDto = new cashAggregateGetBreakDownFilterParamsDto(
            $user,
            $request->from,
            $request->to,
            $request->details_by,
            cashAggregateFilter::createFromHash($request->filter)
        );

        $graphService = new cashGraphService();
        $graphData = $graphService->getAggregateBreakDownData($paramsDto);

        $response = [];
        foreach ($graphData as $graphDatum) {
            $response[] = new cashApiAggregateGetBreakDownDto(
                $graphDatum['direction'],
                $graphDatum['amount'],
                0,
                $graphDatum['currency'],
                $this->getInfo($paramsDto, $graphDatum['detailed'])
            );
        }

        return $response;
    }

    /**
     * @param cashAggregateGetBreakDownFilterParamsDto $paramsDto
     * @param                                          $detailsId
     *
     * @return array
     */
    private function getInfo(cashAggregateGetBreakDownFilterParamsDto $paramsDto, $detailsId): array
    {
        switch ($paramsDto->detailsBy) {
            case cashAggregateGetBreakDownFilterParamsDto::DETAILS_BY_CATEGORY:
                $category = $this->getCategory($detailsId);
                if (!$category) {
                    break;
                }

                return [
                    'name' => $category->getName(),
                    'color' => $category->getColor(),
                ];

            case cashAggregateGetBreakDownFilterParamsDto::DETAILS_BY_CONTACT:
                $contact = $this->getContractContact($detailsId);
                if (!$contact->exists()) {
                    break;
                }

                return [
                    'name' => $contact->getName(),
                    'color' => $contact->getPhoto(),
                ];
        }

        return [];
    }

    /**
     * @param $contractorId
     *
     * @return waContact
     * @throws waException
     */
    public function getContractContact($contractorId): waContact
    {
        if (!isset($this->contractContacts[$contractorId])) {
            $this->contractContacts[$contractorId] = new waContact($contractorId);
        }

        return $this->contractContacts[$contractorId];
    }

    /**
     * @param $categoryId
     *
     * @return cashCategory|null
     * @throws waException
     */
    public function getCategory($categoryId): ?cashCategory
    {
        if ($this->categories === null) {
            $this->categories = cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact();
        }

        if (isset($this->categories[$categoryId])) {
            return $this->categories[$categoryId];
        }

        return null;
    }


}
