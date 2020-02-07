<?php

/**
 * Class cashImportCsvProcessController
 *
 * @todo move process to service
 */
class cashImportCsvProcessController extends waLongActionController
{
    /**
     * @var cashImportCsv
     */
    private $currentImport;

    /**
     * @var cashCsvImportProcessInfoDto
     */
    private $info;

    /**
     * @var cashCsvImportInfoDto
     */
    private $csvInfo;

    /**
     * @var cashCsvImportSettings
     */
    private $settings;

    /**
     * @var int
     */
    private $importId;

    public function execute()
    {
        try {
            parent::execute();
        } catch (Exception $ex) {
            cash()->getLogger()->error($ex->getMessage(), $ex);
            $this->getResponse()->addHeader('Content-type', 'application/json');
            echo json_encode(['error' => $ex->getMessage()]);
            exit;
        }
    }

    /**
     * @return bool
     * @throws kmwaLogicException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    protected function preInit()
    {
        $settings = waRequest::post('import', [], waRequest::TYPE_ARRAY);
        if (empty($settings)) {
            throw new kmwaRuntimeException('No settings fo import');
        }

        if (!is_array($settings)) {
            throw new kmwaRuntimeException('No settings fo import');
        }

        $this->currentImport = cashImportCsv::createCurrent();
        $this->settings = new cashCsvImportSettings($settings);
        if (!$this->settings->isValid()) {
            throw new kmwaRuntimeException($this->settings->getError());
        }

        $import = cash()->getEntityFactory(cashImport::class)->createNewWithData([
            'settings' => json_encode($this->settings, JSON_UNESCAPED_UNICODE),
            'filename' => (new SplFileInfo($this->currentImport->getCsvInfoDto()->path))->getFilename(),
        ]);
        cash()->getEntityPersister()->save($import);
        $this->importId = $import->getId();

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->csvInfo = $this->currentImport->getCsvInfoDto();
        $this->info = new cashCsvImportProcessInfoDto($this->processId, $this->importId);
        $this->info->totalRows = $this->csvInfo->totalRows;

        $this->info->accounts = cashDtoFromEntityFactory::fromEntities(
            cashAccountDto::class,
            cash()->getEntityRepository(cashAccount::class)->findAllActive()
        );
        $this->info->categories = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAllActive()
        );

        $this->data['info'] = $this->info;
        $this->data['csv_info'] = $this->csvInfo;
        $this->data['settings'] = $this->settings;
    }

    /**
     * @inheritDoc
     */
    protected function isDone()
    {
        return $this->info->done;
    }

    /**
     * @inheritDoc
     */
    protected function step()
    {
        $response = $this->currentImport->process($this->csvInfo->headers, $this->info->passedRows, $this->info->chunk);
        foreach ($response->data as $datum) {
            $this->currentImport->save($datum, $this->info);
        }
        $this->info->passedRows = $response->rows;
        $this->info->done = $this->info->passedRows >= $this->info->totalRows;
        $this->data['info'] = $this->info;

        if ($this->remaining_exec_time < $this->max_exec_time/6) {
            return false;
        }

        return !$this->info->done;
    }

    protected function restore()
    {
        $this->info = $this->data['info'];
        $this->csvInfo = $this->data['csv_info'];
        $this->currentImport = cashImportCsv::createCurrent();
        $this->currentImport->setSettings($this->data['settings']);
    }

    /**
     * @inheritDoc
     */
    protected function info()
    {
        echo json_encode($this->data['info']);
    }

    /**
     * @inheritDoc
     */
    protected function finish($filename)
    {
        $this->info();
    }
}
