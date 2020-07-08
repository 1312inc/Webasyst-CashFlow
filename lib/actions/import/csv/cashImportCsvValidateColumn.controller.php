<?php

/**
 * Class cashImportCsvValidateColumnController
 */
class cashImportCsvValidateColumnController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $types = explode(',', waRequest::get('to_validate', '', waRequest::TYPE_STRING_TRIM));
        foreach ($types as $item) {
            $validator = cashImportCsvValidatorFactory::createByType(trim($item));

            if (!$validator->validate()) {
                $this->errors = $validator->getErrors();

                return;
            }

            $this->response[$item] = $validator->getResponse();
        }
    }
}
