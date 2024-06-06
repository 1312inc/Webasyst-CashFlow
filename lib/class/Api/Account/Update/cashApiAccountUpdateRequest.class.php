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
        int $is_imaginary,
        ?string $description
    ) {
        if ($id < 1) {
            throw new cashValidateException(_w('Id should be over 0'));
        } elseif (!in_array($is_imaginary, [0, 1, -1])) {
            throw new cashValidateException(_w('Unknown is_imaginary'));
        }

        $this->id = $id;

        parent::__construct($name, $currency, $icon, $is_imaginary, $description);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
