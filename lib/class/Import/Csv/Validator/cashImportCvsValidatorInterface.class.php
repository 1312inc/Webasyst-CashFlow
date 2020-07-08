<?php

/**
 * Interface cashImportCsvValidatorInterface
 */
interface cashImportCvsValidatorInterface
{
    /**
     * @param mixed ...$params
     *
     * @return bool
     */
    public function validate(...$params): bool;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return array
     */
    public function getResponse(): array;
}
