<?php

final class cashTinkoffPluginSettings implements cashTinkoffPluginEnablableInterface, JsonSerializable, cashTinkoffPluginToArrayInterface
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var cashTinkoffPluginCompanySettings[]
     */
    private $companies = [];

    /**
     * @var waAppSettingsModel
     */
    private $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new waAppSettingsModel();
        $settings = json_decode(
            $this->settingsModel->get($this->getSettingsKey(), 'settings'),
            true
        );

        $this->enabled = (bool) ($settings['enabled'] ?? false);

        if (!isset($settings['companies']) || !is_array($settings['companies'])) {
            $settings['companies'] = [];
        }

        foreach ($settings['companies'] as $companySettings) {
            $tinkoffAccounts = [];
            if (!isset($companySettings['accounts']) || !is_array($companySettings['accounts'])) {
                $companySettings['accounts'] = [];
            }

            foreach ($companySettings['accounts'] as $tinkoffAccount) {
                $ta = cashTinkoffPluginTinkoffAccountSettings::fromArray($tinkoffAccount);
                $tinkoffAccounts[$ta->getAccountNumber()] = $ta;
            }

            $this->companies[] = new cashTinkoffPluginCompanySettings(
                $companySettings['company'],
                (bool) ($companySettings['enabled'] ?? false),
                new cashTinkoffPluginToken(
                    (string) ($companySettings['token']['value'] ?? ''),
                    (bool) ($companySettings['token']['value'] ?? false)
                ),
                $tinkoffAccounts
            );
        }
    }

    public function save(): bool
    {
        try {
            $this->settingsModel->set($this->getSettingsKey(), 'settings', json_encode($this, JSON_UNESCAPED_UNICODE));

            return true;
        } catch (waException $e) {
            cashTinkoffPlugin::log($e);
        }

        return false;
    }

    public function getCompanySettings(string $name): ?cashTinkoffPluginCompanySettings
    {
        $company = array_filter(
            $this->companies,
            static function (cashTinkoffPluginCompanySettings $companySettings) use ($name) {
                return $companySettings->getCompany() === $name;
            }
        );

        return $company ? reset($company) : null;
    }

    public function hasCompanySettings(string $name): bool
    {
        return isset($this->companies[$name]);
    }

    public function addCompanySettings(cashTinkoffPluginCompanySettings $companySettings): self
    {
        if (!isset($this->companies[$companySettings->getCompany()])) {
            $this->companies[$companySettings->getCompany()] = $companySettings;
        }

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): cashTinkoffPluginSettings
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return cashTinkoffPluginCompanySettings[]
     */
    public function getCompanies(): array
    {
        return $this->companies;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $data = [
            'enabled' => $this->enabled,
            'companies' => [],
        ];

        foreach ($this->companies as $company) {
            $data['companies'][] = $company->toArray();
        }

        return $data;
    }

    private function getSettingsKey(): array
    {
        return ['cash', 'tinkoff'];
    }
}
