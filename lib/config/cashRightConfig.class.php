<?php

/**
 * Class cashRightConfig
 */
class cashRightConfig extends waRightConfig
{
    const ADD_TRANSACTION_ONLY = 1;
    const FULL_ACCESS          = 2;
    const CAN_ACCESS_ACCOUNT   = 'can_access_account';

    const RIGHT_CAN    = 1;
    const RIGHT_CANNOT = 0;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var waContactRightsModel
     */
    private $model;

    /**
     * @var array
     */
    private $accesses = [];

    /**
     * cashRightConfig constructor.
     */
    public function __construct()
    {
        $this->userId = waRequest::post('user_id', 0, waRequest::TYPE_INT);

        if (!$this->userId) {
            $this->userId = waRequest::request('id', 0, waRequest::TYPE_INT);
        }

        $this->model = new waContactRightsModel();

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getRightsList()
    {
        return [
            self::CAN_ACCESS_ACCOUNT,
        ];
    }

    /**
     * @throws waException
     */
    public function init()
    {
        $this->addItem(
            self::CAN_ACCESS_ACCOUNT,
            _w('Accounts'),
            'always_enabled'
        );

        $items = [];
        /** @var cashUser $user */
//        foreach (cash()->getEntityRepository(cashUser::class)->findAll() as $user) {
//            if ($user->getContact()->getId() == $this->userId) {
//                continue;
//            }
//
//            $items[$user->getContactId()] = $user->getContact()->getName();
//        }
//
//        $this->addItem(
//            self::CAN_SEE_TEAMMATES,
//            _w('Can see teammates'),
//            'list',
//            ['items' => $items, 'hint1' => 'all_checkbox']
//        );
//
//        $items = [];
//        /** @var cashUser $user */
//        foreach (cash()->getEntityRepository(cashProject::class)->findAll() as $project) {
//            $items[$project->getId()] = $project->getName();
//        }
//
//        $this->addItem(
//            self::CAN_SEE_CONTRIBUTE_TO_PROJECTS,
//            _w('Can see & contribute to projects'),
//            'list',
//            ['items' => $items, 'hint1' => 'all_checkbox']
//        );

        /**
         * @event rights.config
         *
         * @param waRightConfig $this Rights setup object
         *
         * @return void
         */
        wa()->event('rights.config', $this);
    }

    /**
     * @param int $contact_id
     *
     * @return array
     */
    public function getDefaultRights($contact_id)
    {
        return [
            self::CAN_ACCESS_ACCOUNT => self::RIGHT_CANNOT,
        ];
    }

    /**
     * @return array
     */
    public function getUserIdsWithAccess()
    {
        return $this->model->getUsers(cashConfig::APP_ID);
    }

    /**
     * @param int|cashUser|null $user
     *
     * @return bool
     * @throws waException
     */
    public function isAdmin($user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
//        if ($user instanceof cashUser) {
//            $user = $user->getContactId();
//        }

        $this->loadRightsForContactId($user);

        return isset($this->accesses[$user]['backend']) && $this->accesses[$user]['backend'] == PHP_INT_MAX;
    }

    /**
     * @param int|cashUser|null $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToApp($user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
//        if ($user instanceof cashUser) {
//            $user = $user->getContactId();
//        }

        $this->loadRightsForContactId($user);

        if ($this->isAdmin($user)) {
            return true;
        }

        return !empty($this->accesses[$user]['backend']);
    }

    /**
     * @param int $contactId
     *
     * @throws waException
     */
    private function loadRightsForContactId($contactId)
    {
        if (isset($this->accesses[$contactId])) {
            return;
        }

        $contact = new waContact($contactId);

        $this->accesses[$contactId] = $contact->getRights(cashConfig::APP_ID);
    }
}
