<?php

/**
 * Class cashApiTemplateGetListResponse
 */
class cashApiTemplateGetListResponse extends cashApiAbstractResponse
{
    /**
     * @param array $templates
     */
    public function __construct(array $templates)
    {
        parent::__construct(200);

        $this->response = $templates;
    }
}
