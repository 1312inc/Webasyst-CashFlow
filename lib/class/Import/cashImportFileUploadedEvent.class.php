<?php

/**
 * Class cashImportFileUploadedEvent
 */
class cashImportFileUploadedEvent extends cashEvent
{
    /**
     * @var waRequestFile
     */
    private $file;

    /**
     * @var string
     */
    private $savePath;

    /**
     * cashImportFileUploadedEvent constructor.
     *
     * @param string $name
     * @param null   $object
     * @param array  $params
     */
    public function __construct($name = '', $object = null, $params = [])
    {
        parent::__construct(cashEventStorage::WA_BACKEND_IMPORT_FILE_UPLOADED, $object, $params);
    }

    /**
     * @return waRequestFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param waRequestFile $file
     *
     * @return cashImportFileUploadedEvent
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
     * @param string $savePath
     *
     * @return cashImportFileUploadedEvent
     */
    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;

        return $this;
    }
}
