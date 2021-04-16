<?php

/**
 * Class cashApiSystemSearchContactsHandler
 */
class cashApiSystemSearchContactsHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiSystemSearchContactsRequest $request
     *
     * @return array
     * @throws waException
     */
    public function handle($request)
    {
        return (new cashAutocomplete())->findContacts($request->term, 10);
    }
}
