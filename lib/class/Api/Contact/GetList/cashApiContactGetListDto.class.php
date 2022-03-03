<?php

final class cashApiContactGetListDto
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $photo_url;

    /**
     * @var string
     */
    private $photo_url_absolute;

    /**
     * @var array
     */
    private $stat;

    public function __construct(
        int $id,
        string $name,
        string $firstname,
        string $lastname,
        string $photo_url,
        string $photo_url_absolute,
        array $stat
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->photo_url = $photo_url;
        $this->photo_url_absolute = $photo_url_absolute;
        $this->stat = $stat;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPhotoUrl(): string
    {
        return $this->photo_url;
    }

    public function getPhotoUrlAbsolute(): string
    {
        return $this->photo_url_absolute;
    }

    public function getStat(): array
    {
        return $this->stat;
    }
}
