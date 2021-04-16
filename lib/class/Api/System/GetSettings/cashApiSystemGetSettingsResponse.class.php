<?php

/**
 * Class cashApiSystemGetSettingsResponse
 */
class cashApiSystemGetSettingsResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashSystemSettingsDto $data
     */
    public function __construct($data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
