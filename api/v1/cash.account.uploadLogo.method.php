<?php

/**
 * Class cashAccountCreateMethod
 */
class cashAccountUploadLogoMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountUploadLogoResponse
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     */
    public function run(): cashApiResponseInterface
    {
        $file = waRequest::file('logo');
        if (!$file->uploaded()) {
            return new cashApiErrorResponse(sprintf('File upload error: %s %s.', $file->error_code, $file->error));
        }

        $request = $this->fillRequestWithParams(new cashApiAccountUploadLogoRequest());

        $request->file = $file;

        $response = (new cashApiAccountUploadLogoHandler())->handle($request);

        return new cashApiAccountUploadLogoResponse(cashLogoUploader::getUrlToAccountLogo($response));
    }
}
