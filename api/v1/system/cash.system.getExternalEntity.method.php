<?php

use ApiPack1312\ApiParamsCaster;

class cashSystemGetExternalEntityMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run(): cashApiResponseInterface
    {
        $request = new cashApiSystemGetExternalEntityRequest(
            $this->fromGet('source', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromGet('id', true, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $dto = (new cashApiSystemGetExternalEntityHandler())->handle($request);

        if (!$dto) {
            return new cashApiErrorResponse('Не найдено', 'fail', 404);
        }

        return new cashApiSystemGetExternalEntityResponse($dto);
    }
}
