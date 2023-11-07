<?php

class cashSystemSettingsDto
{
    public $ts;

    public $userId;

    public $isShop;

    public $rights;

    public function __construct($userId, cashContactRightsDto $rights)
    {
        $this->ts = time();
        $this->userId = $userId;
        $this->isShop = (int)wa()->appExists('shop');
        $this->rights = $rights;
    }
}
