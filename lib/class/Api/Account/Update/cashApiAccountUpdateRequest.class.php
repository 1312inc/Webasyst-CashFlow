<?php

final class cashApiAccountUpdateRequest extends cashApiAccountCreateRequest
{
    /**
     * @var int
     */
    private $id;

    public function __construct(
        int $id,
        string $name,
        string $currency,
        string $icon,
        ?string $iconLink,
        ?string $description
    ) {
        if ($id < 1) {
            throw new cashValidateException(_w('Id should be over 0'));
        }

        $this->id = $id;

        parent::__construct($name, $currency, $icon, $description);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
