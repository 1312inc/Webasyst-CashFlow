<?php

/**
 * Class cashContactRights
 */
class cashContactRights
{
    /**
     * @var bool
     */
    private $isAdmin = false;

    /**
     * @var bool
     */
    private $isRoot = false;

    /**
     * @var bool
     */
    private $hasAccessToApp = false;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var array
     */
    private $accounts = [];

    /**
     * @var bool
     */
    private $canImport = false;

    /**
     * @var bool
     */
    private $canSeeReport = false;

    /**
     * @var array
     */
    private $rights;

    /**
     * cashContactRights constructor.
     *
     * @param array $rights
     */
    public function __construct(array $rights)
    {
        $this->rights = $rights;
        if (!empty($rights)) {
            $this->hasAccessToApp = true;
        }

        foreach ($this->rights as $right => $value) {
            $id = 0;
            $value = (int) $value;
            if (strpos($right, '.') !== false) {
                list($right, $id) = explode('.', $right);
            }

            switch ($right) {
                case cashRightConfig::RIGHT_CAN_ACCESS_ACCOUNT:
                    $this->accounts[(int) $id] = $value;
                    break;

                case cashRightConfig::RIGHT_CAN_ACCESS_CATEGORY:
                    $this->categories[(int) $id] = $value;
                    break;

                case cashRightConfig::RIGHT_BACKEND:
                    switch ($value) {
                        case PHP_INT_MAX:
                            $this->isRoot = true;

                        case cashRightConfig::ADMIN_ACCESS:
                            $this->isAdmin = true;
                            $this->canImport = true;
                            $this->canSeeReport = true;
                    }

                    break;

                case cashRightConfig::RIGHT_IMPORT_TRANSACTIONS:
                    if ($value === cashRightConfig::YES_ACCESS) {
                        $this->canImport = true;
                    }
                    break;

                case cashRightConfig::RIGHT_SEE_REPORTS:
                    if ($value === cashRightConfig::YES_ACCESS) {
                        $this->canSeeReport = true;
                    }
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return bool
     */
    public function hasAccessToApp(): bool
    {
        return $this->hasAccessToApp;
    }

    /**
     * @param string $access Access level
     * @param bool   $min
     *
     * @return array[]
     */
    public function getCategoriesIdsWithAccess($access, $min = true): array
    {
        return array_keys(
            array_filter(
                $this->categories,
                static function ($value) use ($access, $min) {
                    return $min ? $value >= $access : $value == $access;
                }
            )
        ) ?: [0];
    }

    /**
     * @param string $access Access level
     * @param bool   $min
     *
     * @return array[]
     */
    public function getAccountIdsWithAccess($access, $min = true): array
    {
        return array_keys(
            array_filter(
                $this->accounts,
                static function ($value) use ($access, $min) {
                    return $min ? $value >= $access : $value == $access;
                }
            )
        ) ?: [0];
    }

    /**
     * @param string $access Access level
     *
     * @return array[]
     */
    public function getAccountIdsGroupedByAccess($access = null): array
    {
        $grouped = [];

        foreach ($this->accounts as $accountId => $value) {
            if ($access !== null && $access != $value) {
                continue;
            }

            if (!isset($grouped[$value])) {
                $grouped[$value] = [];
            }
            $grouped[$value][] = $accountId;
        }

        return $grouped;
    }

    /**
     * @return bool
     */
    public function canImport(): bool
    {
        return $this->canImport;
    }

    /**
     * @return bool
     */
    public function canSeeReport(): bool
    {
        return $this->canSeeReport;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->isRoot;
    }

    /**
     * @param int $accountId
     *
     * @return int
     */
    public function getAccountAccess($accountId): int
    {
        return $this->accounts[$accountId] ?? cashRightConfig::NO_ACCESS;
    }
}
