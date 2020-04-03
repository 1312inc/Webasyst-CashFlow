<?php

/**
 * Class cashImportResponseCsv
 */
final class cashImportResponseCsv implements cashImportFileUploadedEventResponseInterface
{
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
     * @inheritDoc
     */
    public function getHtml()
    {
        $view = wa()->getView();
        $template = wa()->getAppPath('./templates/actions/import/csv/form.html', cashConfig::APP_ID);

        /** @var cashAccount[] $accounts */
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActive();
        /** @var cashAccountDto[] $accountDtos */
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        /** @var cashCategory[] $categoriesIncome */
        $categoriesIncome = cash()->getEntityRepository(cashCategory::class)->findAllIncome();
        /** @var cashCategoryDto[] $categoryIncomeDtos */
        $categoryIncomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesIncome);
        array_unshift(
            $categoryIncomeDtos,
            new cashCategoryDto(
                ['name' => _w('New income category'), 'id' => -1, 'type' => cashCategory::TYPE_INCOME]
            )
        );

        /** @var cashCategory[] $categoriesExpense */
        $categoriesExpense = cash()->getEntityRepository(cashCategory::class)->findAllExpense();
        /** @var cashCategoryDto[] $categoryExpenseDtos */
        $categoryExpenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesExpense);
        array_unshift(
            $categoryExpenseDtos,
            new cashCategoryDto(
                ['name' => _w('New expense category'), 'id' => -2, 'type' => cashCategory::TYPE_EXPENSE]
            )
        );

        $view->assign(
            [
                'dateFormats' => array_keys(cashDatetimeHelper::getDatetimeFormats()),
                'info' => $this->csvInfoDto,
                'accounts' => $accountDtos,
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
}
