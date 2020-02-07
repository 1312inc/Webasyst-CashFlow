<?php

/**
 * Class cashImport
 */
class cashImport extends cashAbstractEntity
{
    use cashEntityBeforeSaveTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string|null
     */
    private $params;

    /**
     * @var string|null
     */
    private $settings;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return cashImport
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return cashImport
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string|null $params
     *
     * @return cashImport
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param string|null $settings
     *
     * @return cashImport
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->updateCreateUpdateDatetime();

        return true;
    }
}
