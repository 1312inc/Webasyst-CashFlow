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
        $accountDtos[0] = new cashAccountDto(['name' => _w('Skip'), 'id' => 0, 'currency' => 'RUB']);

        /** @var cashCategory[] $accounts */
        $categories = cash()->getEntityRepository(cashCategory::class)->findAllActive();
        /** @var cashCategoryDto[] $categorieDtos */
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categories);
        $categoryDtos[0] = new cashCategoryDto(['name' => _w('Skip'), 'id' => 0]);
        $categoryDtos[] = new cashCategoryDto(['name' => _w('New income category'), 'id' => -1]);
        $categoryDtos[] = new cashCategoryDto(['name' => _w('New expense category'), 'id' => -2]);

        $view->assign(
            [
                'dateFormats' => array_keys(cashDatetimeHelper::getDatetimeFormats()),
                'info' => $this->csvInfoDto,
                'accounts' => $accountDtos,
                'categories' => $categoryDtos,
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
