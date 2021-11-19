<?php

/**
 * Interface cashApiHandlerInterface
 */
interface cashApiHandlerInterface
{
    /**
     * @param object|null $request
     *
     * @return mixed
     */
    public function handle($request);
}
