<?php

final class cashTinkoffPluginCompanySettings implements cashTinkoffPluginEnablableInterface, JsonSerializable
{
    use cashTinkoffPluginToArrayTrait;

    /**
     * @var string
     */
    private $company;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var cashTinkoffPluginToken
     */
    private $token;

    /**
     * @var cashTinkoffPluginTinkoffAccountSettings[]
     */
    private $tinkoffAccountSettings;

    public function __construct(
        string $company,
        bool $enabled,
        cashTinkoffPluginToken $token,
        array $tinkoffAccountSettings
    ) {
        $this->enabled = $enabled && $token->isValid();
        $this->company = $company;
        $this->token = $token;

        $this->tinkoffAccountSettings = $tinkoffAccountSettings;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getToken(): cashTinkoffPluginToken
    {
        return $this->token;
    }

    public function setCompany(string $company): cashTinkoffPluginCompanySettings
    {
        $this->company = $company;

        return $this;
    }

    public function setEnabled(bool $enabled): cashTinkoffPluginCompanySettings
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function setToken(cashTinkoffPluginToken $token): cashTinkoffPluginCompanySettings
    {
        $this->token = $token;

        return $this;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string|null $accountNumber
     *
     * @return array<cashTinkoffPluginTinkoffAccountSettings>|cashTinkoffPluginTinkoffAccountSettings|null
     */
    public function getCompanyAccountSettings(?string $accountNumber = null)
    {
        if ($accountNumber) {
            return $this->tinkoffAccountSettings[$accountNumber] ?? null;
        }

        return $this->tinkoffAccountSettings;
    }

    public function addCompanyAccountSetting(cashTinkoffPluginTinkoffAccountSettings $accountSettings): self
    {
        $this->tinkoffAccountSettings[$accountSettings->getAccountNumber()] = $accountSettings;

        return $this;
    }

    public function __toString(): string
    {
        return $this->company;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function createEmpty(): self
    {
        return new self(
            'Новая компания',
            false,
            new cashTinkoffPluginToken('invalid', false),
            []
        );
    }

    public function toArray(): array
    {
        $data = [
            'company' => $this->company,
            'enabled' => $this->enabled,
            'token' => $this->token->toArray(),
            'accounts' => [],
        ];

        foreach ($this->tinkoffAccountSettings as $companyAccountSetting) {
            $number = $companyAccountSetting->getAccountNumber();
            $data['accounts'][$number] = $companyAccountSetting->toArray();
        }

        return $data;
    }
}
