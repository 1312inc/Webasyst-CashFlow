<?php

/**
 * Class cashImportCsvViewAction
 */
class cashImportCsvViewAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        if (!$id) {
            throw new kmwaRuntimeException(_w('No import id'));
        }

        $filterDto = new cashTransactionPageFilterDto(cashTransactionPageFilterDto::FILTER_IMPORT, $id);

        if ($filterDto->entity->getIsArchived()) {
            throw new kmwaNotFoundException(_w('No import found'));
        }

        list($startDate, $endDate) = cash()->getModel(cashImport::class)->getDateBounds($filterDto->identifier);
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);

        $this->view->assign(
            [
                'filter' => $filterDto,
//                'upcoming' => $upcoming,
//                'completed' => $completed,
//                'upcomingOnDate' => $upcomingOnDate,
//                'completedOnDate' => $completedOnDate,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]
        );

        $this->setTemplate('templates/actions/import/csv/view.html');
    }
}
