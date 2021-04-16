<?php

/**
 * Class cashApiAccountUploadLogoResponse
 */
class cashApiAccountUploadLogoResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountUploadLogoResponse constructor.
     *
     * @param string $logoUrl
     */
    public function __construct($logoUrl)
    {
        parent::__construct(200);

        $this->response = $logoUrl;
    }
}
