<?php

/**
 * Class cashTransactionPageFilterDto
 */
class cashTransactionPageFilterDto implements JsonSerializable
{
    const FILTER_ACCOUNT  = 'account';
    const FILTER_CATEGORY = 'category';
    const FILTER_IMPORT = 'import';

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $identifier = '';

    /**
     * @var string
     */
    public $type = self::FILTER_ACCOUNT;

    /**
     * @var cashAccount|cashCategory|cashImport
     */
    public $entity;

    /**
     * @var waContact
     */
    public $contact;

    /**
     * self constructor.
     *
     * @param string         $filterType
     * @param string         $identifier
     * @param waContact|null $contact
     *
     * @throws kmwaAssertException
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function __construct($filterType, $identifier = '', waContact $contact = null)
    {
        $this->type = $filterType;
        if (!$contact) {
            $this->contact = wa()->getUser();
        } else {
            $this->contact = $contact;
        }

        switch ($this->type) {
            case self::FILTER_ACCOUNT:
                if ($identifier) {
                    $this->entity = cash()->getEntityRepository(cashAccount::class)->findById($identifier);
                    kmwaAssert::instance($this->entity, cashAccount::class);
                } else {
                    $this->entity = cash()->getEntityFactory(cashAccount::class)->createAllAccount();
                }
                $this->name = $this->entity->getName();

                if ($this->entity->getIsArchived()) {
                    throw new kmwaNotFoundException(_w('Account not found'));
                }

                break;

            case self::FILTER_CATEGORY:
                if (!$identifier) {
                    throw new kmwaNotFoundException(_w('Category not found'));
                }

                switch (true) {
                    case $identifier == cashCategoryFactory::NO_CATEGORY_EXPENSE_ID:
                        $this->entity = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryExpense();
                        break;

                    case $identifier == cashCategoryFactory::NO_CATEGORY_INCOME_ID:
                        $this->entity = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryIncome();
                        break;

                    default:
                        $this->entity = cash()->getEntityRepository(cashCategory::class)->findById($identifier);
                }

                kmwaAssert::instance($this->entity, cashCategory::class);

                $this->name = $this->entity->getName();

                break;

            case self::FILTER_IMPORT:
                if (!$identifier) {
                    throw new kmwaNotFoundException(_w('Import not found'));
                }

                $this->entity = cash()->getEntityRepository(cashImport::class)->findById($identifier);
                kmwaAssert::instance($this->entity, cashImport::class);

                $this->name = sprintf_wp(
                    'Imported on %s',
                    waDateTime::format('humandatetime', $this->entity->getCreateDatetime())
                );

                break;

            default:
                throw new kmwaLogicException('Wrong filter type');
        }

        $this->identifier = $identifier;
        $this->id = $this->entity->getId();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'filter_type' => $this->type,
            'identifier' => $this->identifier,
        ];
    }
}
