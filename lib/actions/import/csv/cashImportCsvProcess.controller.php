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
     * @var cashImport
     */
    private $import;

    public function execute()
    {
        try {
            if (!cash()->getUser()->canImport()) {
                throw new kmwaForbiddenException();
            }

            parent::execute();
        } catch (Exception $ex) {
            cash()->getLogger()->error($ex->getMessage(), $ex, 'import/error');
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
        cash()->getLogger()->debug(<<<LOG
++++++++++++++++++++++++++++++++++++
+++++++++++ NEW IMPORT +++++++++++++
++++++++++++++++++++++++++++++++++++
PREINIT
LOG
        , 'import/import');

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
            cash()->getLogger()->debug(sprintf('PREINIT error: %s', $this->settings->getError()), 'import/import');

            throw new kmwaRuntimeException($this->settings->getError());
        }

        $this->import = cash()->getEntityFactory(cashImport::class)->createNew();
        $this->import
            ->setSettings(json_encode($this->settings, JSON_UNESCAPED_UNICODE))
            ->setFilename((new SplFileInfo($this->currentImport->getCsvInfoDto()->path))->getFilename());
        cash()->getEntityPersister()->save($this->import);

        cash()->getLogger()->debug(
            sprintf('PREINIT ok, new import created: %d', $this->import->getId()),
            'import/import'
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->csvInfo = $this->currentImport->getCsvInfoDto();
        $this->info = new cashCsvImportProcessInfoDto($this->processId, $this->import->getId());
        $this->info->totalRows = $this->csvInfo->totalRows;

        $this->info->accounts = cashDtoFromEntityFactory::fromEntities(
            cashAccountDto::class,
            cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact()
        );
        $this->info->categories = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact()
        );

        $this->data['info'] = $this->info;
        $this->data['csv_info'] = $this->csvInfo;
        $this->data['settings'] = $this->settings;

        cash()->getLogger()->debug('INIT ok', 'import/import');
        cash()->getLogger()->debug($this->settings, 'import/import');
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
        cash()->getLogger()->debug('==== STEP ====', 'import/import');

        $response = $this->currentImport->process($this->csvInfo->headers, $this->info->passedRows, $this->info->chunk);
        foreach ($response->data as $rowNum => $datum) {
            cash()->getLogger()->log(sprintf('Import transaction from row %d', $rowNum), sprintf('import/import_%d', $this->info->importId));

            if ($this->currentImport->save($datum, $this->info)) {
                $this->import->setSuccess($this->info->ok);
            } else {
                $this->import->setFail($this->info->fail);
                $error = $this->currentImport->getError();
                $this->import->addError($error);

                cash()->getLogger()->log(
                    sprintf('row #%d: %s', $rowNum, $error),
                    sprintf('import/import_%d_errors', $this->info->importId)
                );
            }
        }
        cash()->getEntityPersister()->save($this->import);

        $this->info->passedRows = $response->rows;
        $this->info->done = $this->info->passedRows >= $this->info->totalRows;
        $this->data['info'] = $this->info;

        if ($this->remaining_exec_time < $this->max_exec_time / 6) {
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
        $this->import = cash()->getEntityRepository(cashImport::class)->findById($this->info->importId);
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
        echo json_encode($this->data['info'], JSON_UNESCAPED_UNICODE);

        return true;
    }
}
