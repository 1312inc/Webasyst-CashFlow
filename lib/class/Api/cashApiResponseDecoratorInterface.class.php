<?php

/**
 * Interface cashApiResponseDecoratorInterface
 */
interface cashApiResponseDecoratorInterface
{
    /**
     * @param cashAbstractEntity $response
     */
    public function decorateEntity(cashAbstractEntity $response);

    /**
     * @param array $response
     */
    public function decorateArray(array $response);
}
