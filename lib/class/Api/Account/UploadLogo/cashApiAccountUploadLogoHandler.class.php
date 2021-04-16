<?php

/**
 * Class cashApiAccountUploadLogoHandler
 */
class cashApiAccountUploadLogoHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountUploadLogoRequest $request
     *
     * @return string
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $logoUploader = new cashLogoUploader();
        $path = $logoUploader->uploadToContact(wa()->getUser(), $request->file);
        if ($path === false) {
            throw new kmwaRuntimeException('Error on save logo');
        }

        return $path;
    }
}
