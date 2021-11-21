<?php

final class cashApiSystemSearchContactDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $photo_url;

    /**
     * @var string
     */
    public $photo_url_absolute;

    /**
     * @var string
     */
    public $label;

    public function __construct(
        int $id,
        string $value,
        string $name,
        string $firstname,
        string $lastname,
        string $photo_url,
        string $photo_url_absolute,
        string $label
    ) {
        $this->id = $id;
        $this->value = $value;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->photo_url = $photo_url;
        $this->photo_url_absolute = $photo_url_absolute;
        $this->label = $label;
    }
}
