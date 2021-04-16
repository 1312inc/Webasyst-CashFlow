<?php

class cashSystemSettingsDto
{
    public $ts;

    public $userId;

    public $rights;

    public function __construct($userId, cashContactRightsDto $rights)
    {
        $this->ts = time();
        $this->userId = $userId;
        $this->rights = $rights;
    }
}
