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

        $categoriesIncome = cash()->getEntityRepository(cashCategory::class)->findAllIncomeForContact();
        /** @var cashCategoryDto[] $categoryIncomeDtos */
        $categoryIncomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesIncome);
        array_unshift(
            $categoryIncomeDtos,
            cashDtoFromEntityFactory::fromEntity(
                cashCategoryDto::class,
                cash()->getEntityRepository(cashCategory::class)
                    ->findNoCategoryIncome()
                    ->setName(_w('New income category'))
            )
        );

        $categoriesExpense = cash()->getEntityRepository(cashCategory::class)->findAllExpense();
        /** @var cashCategoryDto[] $categoryExpenseDtos */
        $categoryExpenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categoriesExpense);
        array_unshift(
            $categoryExpenseDtos,
            cashDtoFromEntityFactory::fromEntity(
                cashCategoryDto::class,
                cash()->getEntityRepository(cashCategory::class)
                    ->findNoCategoryExpense()
                    ->setName(_w('New expense category'))
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
