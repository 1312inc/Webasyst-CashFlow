<?php

/**
 * Interface cashApiResponseInterface
 */
interface cashApiResponseInterface
{
    /**
     * @return int
     */
    public function getStatus(): int;

    public function getResponseBody();
}
