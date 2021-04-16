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
        return array_reduce(
            waCurrency::getAll(true),
            static function ($carry, $currency) {
                if ($currency['code'] === 'RUB') {
                    $currency['sign'] = '₽';
                }
                $carry[] = $currency;

                return $carry;
            },
            []
        );
    }
}
