<?php

class cashApiAccountCreateRequest
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $accountable_contact_id;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var int
     */
    private $is_imaginary;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name, string $currency, ?int $accountable_contact_id, ?string $icon, int $is_imaginary, ?string $description)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new cashValidateException(_w('No account name'));
        } elseif (empty($currency)) {
            throw new cashValidateException(_w('No account currency'));
        } elseif (!in_array($is_imaginary, [0, 1, -1])) {
            throw new cashValidateException(_w('Unknown is_imaginary'));
        }
        if ($accountable_contact_id) {
            $contact = cashHelper::getContact($accountable_contact_id);
            if (!$contact->isExists()) {
                throw new cashValidateException(_w('Contact not found'));
            } elseif (!$contact->isUser()) {
                throw new cashValidateException(_w('Contact is not user'));
            }
            $icon = wa()->getConfig()->getHostUrl().$contact->getUserPic();
        }
        if (!empty($icon) && !preg_match('#^(https?://)?(www\.)?.{2,225}\..{2,20}.+$#u', $icon)) {
            $icon = '';
        }

        $this->name = $name;
        $this->currency = $currency;
        $this->accountable_contact_id = $accountable_contact_id;
        $this->icon = (string) $icon;
        $this->is_imaginary = $is_imaginary;
        $this->description = (string) $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAccountableContactId(): ?int
    {
        return $this->accountable_contact_id;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getImaginary(): int
    {
        return $this->is_imaginary;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
