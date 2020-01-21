<?php

/**
 * Class self
 */
class cashTransactionPageFilterDto implements JsonSerializable
{
    const FILTER_ACCOUNT  = 'account';
    const FILTER_CATEGORY = 'category';

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
     * @var cashAccount|cashCategory
     */
    public $entity;

    /**
     * self constructor.
     *
     * @param string $filterType
     * @param string $identifier
     *
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function __construct($filterType, $identifier = '')
    {
        $this->type = $filterType;

        switch ($this->type) {
            case self::FILTER_ACCOUNT:
                if ($identifier) {
                    $this->entity = cash()->getEntityRepository(cashAccount::class)->findById($identifier);
                    if (!$this->entity instanceof cashAccount) {
                        throw new kmwaNotFoundException(_w('Account not found'));
                    }
                } else {
                    $this->entity = cash()->getEntityFactory(cashAccount::class)->createAllAccount();
                }

                if ($this->entity->getIsArchived()) {
                    throw new kmwaNotFoundException(_w('Account not found'));
                }
                break;

            case self::FILTER_CATEGORY:
                if ($identifier) {
                    $this->entity = cash()->getEntityRepository(cashCategory::class)->findBySlug($identifier);
                    if (!$this->entity instanceof cashCategory) {
                        throw new kmwaNotFoundException(_w('Category not found'));
                    }
                } else {
                    throw new kmwaNotFoundException(_w('Category not found'));

                }
                break;

            default:
                throw new kmwaLogicException('Wrong filter type');
        }

        $this->identifier = $identifier;
        $this->name = $this->entity->getName();
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
