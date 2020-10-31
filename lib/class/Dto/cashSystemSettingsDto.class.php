<?php

class cashSystemSettingsDto
{
    public $ts;

    public $showReviewWidget;

    public $userId;

    public $rights;

    public function __construct($showReviewWidget, $userId, cashContactRightsDto $rights)
    {
        $this->ts = time();
        $this->showReviewWidget = $showReviewWidget;
        $this->userId = $userId;
        $this->rights = $rights;
    }
}
