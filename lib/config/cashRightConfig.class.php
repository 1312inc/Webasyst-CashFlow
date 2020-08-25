<?php

/**
 * Class cashRightConfig
 */
class cashRightConfig extends waRightConfig
{
    const RIGHT_CAN_ACCESS_ACCOUNT  = 'can_access_account';
    const RIGHT_CAN_ACCESS_CATEGORY = 'can_access_category';
    const RIGHT_BACKEND             = 'backend';
    const RIGHT_IMPORT_TRANSACTIONS = 'can_import_transactions';
    const RIGHT_SEE_REPORTS         = 'can_see_reports';

    const ACCOUNT_FULL_ACCESS                                  = 99;
    const ACCOUNT_ADD_EDIT_VIEW_TRANSACTIONS_CREATED_BY_OTHERS = 2;
    const ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY      = 1;
    const CATEGORY_FULL_ACCESS                                 = 1;

    const ADMIN_ACCESS = 2;
    const YES_ACCESS = 1;
    const NO_ACCESS  = 0;

    /**
     * @var int
     */
    private $userId;

    /**
     * @throws waException
     */
    public function init()
    {
        $this->userId = waRequest::post('user_id', 0, waRequest::TYPE_INT);

        if (!$this->userId) {
            $this->userId = waRequest::request('id', 0, waRequest::TYPE_INT);
        }

        $items = [];
        foreach (cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact() as $account) {
            $items[$account->getId()] = $account->getName();
        }

        $this->addItem(self::RIGHT_SEE_REPORTS, _w('Can see reports'), 'checkbox');
        $this->addItem(self::RIGHT_IMPORT_TRANSACTIONS, _w('Can import transactions'), 'checkbox');

        $this->addItem(
            self::RIGHT_CAN_ACCESS_ACCOUNT,
            _w('Accounts'),
            'selectlist',
            [
                'items' => $items,
                'position' => 'right',
                'options' => [
                    self::NO_ACCESS => _w('No access'),
                    self::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY => _w(
                        'Add & edit self-created transactions only'
                    ),
                    self::ACCOUNT_ADD_EDIT_VIEW_TRANSACTIONS_CREATED_BY_OTHERS => _w(
                        'Add, edit & view transactions created by others'
                    ),
                    self::ACCOUNT_FULL_ACCESS => _w('Full access: see the account balance & manage settings'),
                ],
            ]
        );

        $items = [];
        foreach (cash()->getEntityRepository(cashCategory::class)->findAllIncomeForContact() as $category) {
            $items[$category->getId()] = $category->getName();
        }

        $this->addItem(self::RIGHT_CAN_ACCESS_CATEGORY, _w('Income'), 'list', ['items' => $items]);

        $items = [];
        foreach (cash()->getEntityRepository(cashCategory::class)->findAllExpense() as $category) {
            $items[$category->getId()] = $category->getName();
        }

        $this->addItem(self::RIGHT_CAN_ACCESS_CATEGORY, _w('Expense'), 'list', ['items' => $items]);

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
    public function getDefaultRights($contact_id): array
    {
        $default = [
            self::RIGHT_CAN_ACCESS_ACCOUNT => self::NO_ACCESS,
        ];

        $categories = cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact(wa()->getUser());
        /** @var cashCategory $category */
        foreach ($categories as $category) {
            $default[self::RIGHT_CAN_ACCESS_CATEGORY . '.' . $category->getId()] = self::CATEGORY_FULL_ACCESS;
        }

        return $default;
    }
}
