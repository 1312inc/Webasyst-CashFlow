<?php

/**
 * Class cashTransactionListAction
 */
class cashTransactionListAction extends cashTransactionPageAction
{
    /**
     * @throws waException
     */
    public function runAction($params = null)
    {
        $dtoAssembler = new cashTransactionDtoAssembler();
        $tomorrow = new DateTime('tomorrow');
        $upcoming = $completed = [];

        switch ($this->filterDto->type) {
            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                $upcoming = array_reverse(
                    $dtoAssembler->findByDatesAndAccount(
                        $tomorrow,
                        $this->endDate,
                        $this->filterDto->id
                    ),
                    true
                );
                $completed = array_reverse(
                    $dtoAssembler->findByDatesAndAccount(
                        $this->startDate,
                        $this->today,
                        $this->filterDto->id
                    ),
                    true
                );
                break;

            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                $upcoming = array_reverse(
                    $dtoAssembler->findByDatesAndCategory(
                        $tomorrow,
                        $this->endDate,
                        $this->filterDto->id
                    ),
                    true
                );
                $completed = array_reverse(
                    $dtoAssembler->findByDatesAndCategory(
                        $this->startDate,
                        $this->today,
                        $this->filterDto->id
                    ),
                    true
                );
                break;
        }

        $this->view->assign(
            [
                'upcoming' => $upcoming,
                'completed' => $completed,
                'filter' => $this->filterDto,
            ]
        );
    }
}
