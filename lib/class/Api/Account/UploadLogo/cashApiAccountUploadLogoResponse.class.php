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

        $this->response = sprintf('%s%s', rtrim(wa()->getRootUrl(true), '/'), $logoPath);
    }
}
