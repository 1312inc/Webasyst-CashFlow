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

        $this->response = $this->filterFields(
            $templates,
            ['id', 'contact_id', 'name', 'type', 'content', 'sort'],
            [
                'id' => 'int',
                'contact_id' => 'int',
                'sort' => 'int',
            ]
        );
    }
}
