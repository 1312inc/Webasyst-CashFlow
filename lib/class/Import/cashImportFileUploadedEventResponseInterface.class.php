<?php

/**
 * Interface cashImportFileUploadedEventResponseInterface
 */
interface cashImportFileUploadedEventResponseInterface
{
    const FILE_TYPE_CSV = 'csv';

    /**
     * @return string
     */
    public function getFileType();

    /**
     * @return string
     */
    public function getIdentification();

    /**
     * @return string
     */
    public function getHtml();
}
