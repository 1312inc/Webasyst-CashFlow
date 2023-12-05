<?php

final class cashRightConfig extends waRightConfig
{
    public const RIGHT_CAN_ACCESS_ACCOUNT  = 'can_access_account';
    public const RIGHT_CAN_ACCESS_CATEGORY = 'can_access_category';
    public const RIGHT_BACKEND             = 'backend';
    public const RIGHT_IMPORT_TRANSACTIONS = 'can_import_transactions';
    public const RIGHT_SEE_REPORTS         = 'can_see_reports';
    public const RIGHT_ACCESS_TRANSFERS    = 'can_access_transfers';

    public const ACCOUNT_FULL_ACCESS                                  = 99;
    public const ACCOUNT_ADD_EDIT_VIEW_TRANSACTIONS_CREATED_BY_OTHERS = 2;
    public const ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY      = 1;
    public const CATEGORY_FULL_ACCESS                                 = 1;

    public const ADMIN_ACCESS = 2;
    public const YES_ACCESS   = 1;
    public const NO_ACCESS    = 0;

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
        $this->addItem(self::RIGHT_ACCESS_TRANSFERS, _w('Can access transfers'), 'checkbox');

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
                        'Contributor: can view & manage self-created transactions only'
                    ),
                    self::ACCOUNT_ADD_EDIT_VIEW_TRANSACTIONS_CREATED_BY_OTHERS => _w(
                        'Accountant: can view & manage transactions created by others'
                    ),
                    self::ACCOUNT_FULL_ACCESS => _w(
                        'Full access: can see the account balance & manage all transactions'
                    ),
                ],
            ]
        );

        $items = [
            cashCategory::TYPE_INCOME => [],
            cashCategory::TYPE_EXPENSE => [],
        ];
        $categories = cash()->getModel(cashCategory::class)->getAllWithParent();
        foreach ($categories as $category) {
            if ($category['type'] === cashCategory::TYPE_TRANSFER) {
                continue;
            }

            $i = $category['category_parent_id']
                ? sprintf('%s.%s', $category['category_parent_id'], $category['id'])
                : $category['id'];
            $items[$category['type']][$i] = [
                'id' => $category['id'],
                'name' => $category['category_parent_id']
                    ? sprintf('%s -> %s', $category['parent_name'], $category['name'])
                    : $category['name'],
            ];
        }

        ksort($items[cashCategory::TYPE_INCOME]);
        ksort($items[cashCategory::TYPE_EXPENSE]);

        $this->addItem(
            self::RIGHT_CAN_ACCESS_CATEGORY,
            _w('Income'),
            'list',
            [
                'items' => array_combine(
                    array_column($items[cashCategory::TYPE_INCOME], 'id'),
                    array_column($items[cashCategory::TYPE_INCOME], 'name')
                ),
            ]
        );
        $this->addItem(
            self::RIGHT_CAN_ACCESS_CATEGORY,
            _w('Expense'),
            'list',
            [
                'items' => array_combine(
                    array_column($items[cashCategory::TYPE_EXPENSE], 'id'),
                    array_column($items[cashCategory::TYPE_EXPENSE], 'name')
                ),
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
     *
     * @throws waException
     */
    public function getDefaultRights($contact_id): array
    {
        $default = [
            self::RIGHT_CAN_ACCESS_ACCOUNT => self::NO_ACCESS,
        ];

        /** @var cashCategory[] $categories */
        $categories = cash()->getEntityRepository(cashCategory::class)->findAll();
        foreach ($categories as $category) {
            if ($category->isTransfer()) {
                continue;
            }

            $default[self::RIGHT_CAN_ACCESS_CATEGORY . '.' . $category->getId()] = self::CATEGORY_FULL_ACCESS;
        }

        return $default;
    }

    public function clearRights($contact_id)
    {
        cash()->getContactRights()->clearRightsCache();
    }
}
