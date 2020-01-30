<?php

/**
 * Class cashImportResponseCsv
 */
class cashImportResponseCsv implements cashImportFileUploadedEventResponseInterface
{
    /**
     * @var cashCsvDataDto
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

        /** @var cashCategory[] $accounts */
        $categories = cash()->getEntityRepository(cashCategory::class)->findAllActive();
        /** @var cashCategoryDto[] $categorieDtos */
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $categories);

        $view->assign(
            [
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
     * @return cashCsvDataDto
     */
    public function getImportInfo()
    {
        return $this->csvInfoDto;
    }

    /**
     * @param cashCsvImportInfoDto $info
     *
     * @return cashImportResponseCsv
     */
    public function setImportInfo(cashCsvImportInfoDto $info)
    {
        $this->csvInfoDto = $info;

        return $this;
    }
}
