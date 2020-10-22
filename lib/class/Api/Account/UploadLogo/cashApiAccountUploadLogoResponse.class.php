<?php

/**
 * Class cashApiAccountUploadLogoResponse
 */
class cashApiAccountUploadLogoResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountUploadLogoResponse constructor.
     *
     * @param string $logoPath
     */
    public function __construct($logoPath)
    {
        parent::__construct(200);

        $this->response = $logoPath;
    }
}
