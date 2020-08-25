<?php

/**
 * Class statusUser
 */
class cashUser
{
    use kmwaWaUserTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * statusUser constructor.
     *
     * @param waContact $contact
     */
    public function __construct(waContact $contact)
    {
        $this->setContact($contact);
    }

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
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return cash()->getContactRights()->isRoot($this->contact);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return cash()->getContactRights()->isAdmin($this->contact);
    }

    /**
     * @return bool
     */
    public function hasAccessToApp(): bool
    {
        return cash()->getContactRights()->hasAccessToApp($this->contact);
    }

    /**
     * @return bool
     */
    public function canImport(): bool
    {
        return cash()->getContactRights()->canImport($this->contact);
    }

    /**
     * @return bool
     */
    public function canSeeReport(): bool
    {
        return cash()->getContactRights()->canSeeReport($this->contact);
    }
}
