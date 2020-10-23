<?php

/**
 * Class cashApiSystemGetCurrenciesHandler
 */
class cashApiSystemGetCurrenciesHandler implements cashApiHandlerInterface
{
    /**
     * @param null $request
     *
     * @return array
     */
    public function handle($request)
    {
        return waCurrency::getAll(true);
    }
}
