<?php

/**
 * Class cashApiCategoryGetListHandler
 */
class cashApiCategoryGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param $request
     *
     * @return array|cashApiCategoryResponseDto[]
     * @throws waException
     */
    public function handle($request): array
    {
        /** @var cashCategoryRepository $repository */
        $repository = cash()->getEntityRepository(cashCategory::class);

        $contact = wa()->getUser();
        $categories = $repository->findAllActiveForContact($contact);

        $response = [];
        foreach ($categories as $account) {
            $response[] = cashApiCategoryResponseDto::fromCategory($account);
        }

        return $response;
    }
}
