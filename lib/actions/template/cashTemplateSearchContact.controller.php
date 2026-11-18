<?php

class cashTemplateSearchContactController extends cashJsonController
{
    public function execute()
    {

        $request = new cashApiContactSearchRequest(
            waRequest::get('term', '', waRequest::TYPE_STRING_TRIM),
            waRequest::get('category_id', null, waRequest::TYPE_INT),
            waRequest::get('is_user', 0, waRequest::TYPE_INT),
            waRequest::get('limit', 10, waRequest::TYPE_INT)
        );

        $api_contact_search = new cashApiContactSearchResponse((new cashApiContactSearchHandler())->handle($request));
        $this->response = $api_contact_search->getResponseBody();
    }
}
