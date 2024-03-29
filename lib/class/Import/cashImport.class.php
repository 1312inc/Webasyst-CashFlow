<?php

/**
 * Class cashImport
 */
class cashImport extends cashAbstractEntity
{
    use kmwaEntityDatetimeTrait;
    use cashEntityJsonTransformerTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int|null
     */
    private $contact_id;

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
     * @var int
     */
    private $success = 0;

    /**
     * @var int
     */
    private $fail = 0;

    /**
     * @var array|string
     */
    private $errors = [];

    /**
     * @var bool|int
     */
    private $is_archived = 0;

    /**
     * @var string
     */
    private $provider = cashImportCsv::PROVIDER_CSV;

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

    public function beforeExtract(array &$fields)
    {
        $this->toJson(['errors']);
    }

    public function afterExtract(array &$fields)
    {
        $this->fromJson(['errors']);
    }

    public function afterHydrate($data = [])
    {
        $this->fromJson(['errors']);
    }

    /**
     * @return int|null
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param int|null $contact_id
     *
     * @return cashImport
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param int $success
     *
     * @return cashImport
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @param int $inc
     *
     * @return cashImport
     */
    public function incSuccess(int $inc = 1)
    {
        $this->success += $inc;

        return $this;
    }

    /**
     * @return int
     */
    public function getFail()
    {
        return $this->fail;
    }

    /**
     * @param int $fail
     *
     * @return cashImport
     */
    public function setFail($fail)
    {
        $this->fail = $fail;

        return $this;
    }

    /**
     * @param int $inc
     *
     * @return cashImport
     */
    public function incFail(int $inc = 1)
    {
        $this->fail += $inc;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return cashImport
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $error
     *
     * @return cashImport
     */
    public function addError($error)
    {
        $this->errors[md5($error)] = $error;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param bool|int $is_archived
     *
     * @return cashImport
     */
    public function setIsArchived($is_archived)
    {
        $this->is_archived = $is_archived;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     *
     * @return cashImport
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
