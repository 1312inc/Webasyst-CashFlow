<?php

/**
 * Class cashRightConfig
 */
class cashRightConfig extends waRightConfig
{
    const CAN_ACCESS_ACCOUNT = 'can_access_account';

    const RIGHT_FULL_ACCESS                 = 99;
    const RIGHT_ADD_VIEW_ALLOWED_CATEGORIES = 2;
    const RIGHT_ADD_ONLY                    = 1;
    const RIGHT_NONE                        = 0;

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
        $items = [];
        /** @var cashAccount $account */
        foreach (cash()->getEntityRepository(cashAccount::class)->findAll() as $account) {
            $items[$account->getId()] = $account->getName();
        }

        $this->addItem(
            self::CAN_ACCESS_ACCOUNT,
            _w('Accounts'),
            'selectlist',
            [
                'items' => $items,
                'position' => 'right',
                'options' => [
                    self::RIGHT_NONE => _w('No access'),
                    self::RIGHT_ADD_ONLY => _w('Add & edit self-created transactions only'),
                    self::RIGHT_ADD_VIEW_ALLOWED_CATEGORIES => _w('Add, edit & view transactions created by others'),
                    self::RIGHT_FULL_ACCESS => _w('Full access: see the account balance & manage settings'),
                ],
            ]
        );

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
            self::CAN_ACCESS_ACCOUNT => self::RIGHT_NONE,
        ];
    }

    /**
     * @param int      $right
     * @param int|null $userId
     *
     * @return bool
     * @throws waException
     */
    public function userCan($right, $userId = null)
    {
        if (!$userId) {
            $userId = wa()->getUser()->getId();
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        return in_array($this->accesses[$userId][$right]);
    }

    /**
     * @return array
     */
    public function getUserIdsWithAccess()
    {
        return $this->model->getUsers(cashConfig::APP_ID);
    }

    /**
     * @param int|null $user
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
     * @param int|null $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToApp($user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }

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
