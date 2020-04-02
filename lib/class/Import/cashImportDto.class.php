<?php

/**
 * Class cashImportDto
 */
class cashImportDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int|null
     */
    public $contact_id;

    /**
     * @var waContact
     */
    public $contact;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string|null
     */
    public $params;

    /**
     * @var int
     */
    public $success = 0;

    /**
     * @var int
     */
    public $fail = 0;

    /**
     * @var string
     */
    public $create_datetime;

    /**
     * @var int
     */
    public $is_archived;

    /**
     * @var string
     */
    public $provider;

    /**
     * cashImportDto constructor.
     *
     * @param array $data
     *
     * @throws waException
     */
    public function __construct($data = [])
    {
        if ($data) {
            $this->initializeWithArray($data);
            if ($this->contact_id) {
                $this->contact = new waContact($this->contact_id);
            }
        }
    }
}
