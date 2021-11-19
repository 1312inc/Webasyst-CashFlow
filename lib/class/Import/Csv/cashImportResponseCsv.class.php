<?php

/**
 * Class cashImportResponseCsv
 */
final class cashImportResponseCsv implements cashImportFileUploadedEventResponseInterface
{
    public const NEW_INCOME_ID = -1111111111;
    public const NEW_EXPENSE_ID = -2222222222;

    /**
     * @var cashCsvImportInfoDto
     */
    private $csvInfoDto;

    /**
     * @inheritDoc
     */
    public function getFileType()
    {
        return self::FILE_TYPE_CSV;
    }

    /**
     * @var
     */
    private $error;

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        $view = wa()->getView();

        $template = wa()->getAppPath('./templates/actions/import/csv/form.html', cashConfig::APP_ID);

        /** @var cashAccount[] $accounts */
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        /** @var cashAccountDto[] $accountDtos */
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        $catWithParent = cash()->getModel(cashCategory::class)->getAllWithParent();
        $addParentNameFunc = static function (cashCategoryDto $categoryDto) use ($catWithParent) {
            if ($categoryDto->category_parent_id && isset($catWithParent[$categoryDto->id])) {
                $categoryDto->name = sprintf(
                    '%s -> %s',
                    $catWithParent[$categoryDto->id]['parent_name'],
                    $categoryDto->name
                );
            }
        };

        $categoriesIncome = cash()->getEntityRepository(cashCategory::class)->findAllIncomeForContact();
        /** @var cashCategoryDto[] $categoryIncomeDtos */
        $categoryIncomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesIncome);
        array_map($addParentNameFunc, $categoryIncomeDtos);

        array_unshift(
            $categoryIncomeDtos,
            cashDtoFromEntityFactory::fromEntity(
                cashCategoryDto::class,
                cash()->getEntityRepository(cashCategory::class)
                    ->findNoCategoryIncome()
                    ->setName(_w('New income category'))
                    ->setId(self::NEW_INCOME_ID)
            )
        );

        $categoriesExpense = cash()->getEntityRepository(cashCategory::class)->findAllExpenseForContact();
        /** @var cashCategoryDto[] $categoryExpenseDtos */
        $categoryExpenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesExpense);
        array_map($addParentNameFunc, $categoryExpenseDtos);

        array_unshift(
            $categoryExpenseDtos,
            cashDtoFromEntityFactory::fromEntity(
                cashCategoryDto::class,
                cash()->getEntityRepository(cashCategory::class)
                    ->findNoCategoryExpense()
                    ->setName(_w('New expense category'))
                    ->setId(self::NEW_EXPENSE_ID)
            )
        );

        $view->assign(
            [
                'dateFormats' => array_keys(cashDatetimeHelper::getDatetimeFormats()),
                'info' => $this->csvInfoDto,
                'accounts' => array_values($accountDtos),
                'categoriesIncome' => array_values($categoryIncomeDtos),
                'categoriesExpense' => array_values($categoryExpenseDtos),
            ]
        );

        return $view->fetch($template);
    }

    /**
     * @inheritDoc
     */
    public function getIdentification()
    {
        return 'cash-csv';
    }

    /**
     * @param cashCsvImportInfoDto $info
     *
     * @return cashImportResponseCsv
     */
    public function setCsvInfoDto(cashCsvImportInfoDto $info)
    {
        $this->csvInfoDto = $info;

        return $this;
    }

    /**
     * @return cashCsvImportInfoDto
     */
    public function getCsvInfoDto()
    {
        return $this->csvInfoDto;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return cashImportResponseCsv
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }
}
