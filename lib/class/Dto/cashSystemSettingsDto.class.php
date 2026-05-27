<?php

class cashSystemSettingsDto
{
    public $ts;

    public $userId;

    public $isPremium;

    public $isShop;

    public $rights;

    public function __construct($userId, $isPremium, cashContactRightsDto $rights)
    {
        $this->ts = time();
        $this->userId = $userId;
        $this->isPremium = $isPremium;
        $this->isShop = (int)wa()->appExists('shop');
        $this->rights = $rights;
    }
}
