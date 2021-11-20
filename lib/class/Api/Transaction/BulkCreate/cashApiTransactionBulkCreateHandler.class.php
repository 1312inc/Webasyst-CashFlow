<?php

/**
 * @todo: test & refactor
 *
 * Class cashApiTransactionCreateHandler
 */
class cashApiTransactionBulkCreateHandler extends cashApiTransactionCreateHandler
{
    /**
     * @param array<cashApiTransactionCreateRequest> $request
     *
     * @return array<cashApiTransactionResponseDto>
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $response = [];
        foreach ($request as $r) {
            $response = array_merge($response, parent::handle($r));
        }

        return $response;
    }
}
