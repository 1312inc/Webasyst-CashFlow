<?php

final class cashApiContactSearchHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiContactSearchRequest $request
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
