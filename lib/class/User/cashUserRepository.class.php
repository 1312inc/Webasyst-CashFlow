<?php

final class cashUserRepository extends cashBaseRepository
{
    /**
     * @var array<cashUser>
     */
    private $users = [];

    /**
     * @param int $contactId
     *
     * @return cashUser
     * @throws waException
     */
    public function getUser($contactId): cashUser
    {
        if (!isset($this->users[$contactId])) {
            $this->users[$contactId] = new cashUser(new waContact($contactId));
        }

        return $this->users[$contactId];
    }
}
