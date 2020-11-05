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
     * @var bool
     */
    public $canAccessTransfers;

    /**
     * cashContactRightsDto constructor.
     *
     * @param $isAdmin
     * @param $categories
     * @param $accounts
     * @param $canImport
     * @param $canSeeReport
     * @param $canAccessTransfers
     */
    public function __construct($isAdmin, $categories, $accounts, $canImport, $canSeeReport, $canAccessTransfers)
    {
        $this->isAdmin = $isAdmin;
        $this->categories = $categories;
        $this->accounts = $accounts;
        $this->canImport = $canImport;
        $this->canSeeReport = $canSeeReport;
        $this->canAccessTransfers = $canAccessTransfers;
    }
}
