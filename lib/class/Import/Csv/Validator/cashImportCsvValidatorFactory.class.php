<?php

/**
 * Class cashImportCsvValidatorFactory
 */
final class cashImportCsvValidatorFactory
{
    /**
     * @param string $type
     *
     * @return cashImportCvsValidatorInterface
     * @throws kmwaLogicException
     * @throws kmwaRuntimeException
     */
    public static function createByType(string $type): cashImportCvsValidatorInterface
    {
        $csvImport = cashImportCsv::createCurrent();

        switch ($type) {
            case cashImportCsvAbstractValidator::DATETIME:
                return new cashImportCsvValidatorDatetime($csvImport);

            case cashImportCsvAbstractValidator::AMOUNT:
                return new cashImportCsvValidatorAmountFormat($csvImport);

            case cashImportCsvAbstractValidator::UNIQUE_VALUES:
                return new cashImportCsvValidatorUniqueValues($csvImport);

            case cashImportCsvAbstractValidator::FULLNESS:
                return new cashImportCsvValidatorFullness($csvImport);
        }

        throw new kmwaRuntimeException(sprintf('Unknown validator type %s', $type));
    }
}
