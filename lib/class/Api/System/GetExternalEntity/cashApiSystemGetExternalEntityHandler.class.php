<?php

class cashApiSystemGetExternalEntityHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiSystemGetExternalEntityRequest $request
     */
    public function handle($request): ?cashExternalInfoDto
    {
        $getter = sprintf('cashExternal%sInfoGetter', ucfirst($request->getSource()));
        if (!class_exists($getter) || is_a($getter, cashExternalSourceInfoGetterInterface::class)) {
            return null;
        }

        return (new $getter())->info($request->getId());
    }
}
