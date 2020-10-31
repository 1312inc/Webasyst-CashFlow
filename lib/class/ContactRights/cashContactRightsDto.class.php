<?php

class cashContactRightsDto
{
    /**
     * @var bool
     */
    public $isAdmin;

    /**
     * @var array
     */
    public $categories;

    /**
     * @var array
     */
    public $accounts;

    /**
     * @var bool
     */
    public $canImport;

    /**
     * @var bool
     */
    public $canSeeReport;

    /**
     * cashContactRightsDto constructor.
     *
     * @param $isAdmin
     * @param $categories
     * @param $accounts
     * @param $canImport
     * @param $canSeeReport
     */
    public function __construct($isAdmin, $categories, $accounts, $canImport, $canSeeReport)
    {
        $this->isAdmin = $isAdmin;
        $this->categories = $categories;
        $this->accounts = $accounts;
        $this->canImport = $canImport;
        $this->canSeeReport = $canSeeReport;
    }
}
