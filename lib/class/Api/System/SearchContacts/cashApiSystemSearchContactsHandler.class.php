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
        return (new cashAutocomplete())->findContacts(
            new cashAutocompleteParamsDto($request->getTerm(), $request->getCategoryId()),
            $request->getLimit()
        );
    }
}
