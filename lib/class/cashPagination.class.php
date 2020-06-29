<?php

/**
 * Class cashPagination
 */
class cashPagination
{
    const LIMIT = 500;

    /**
     * @var int
     */
    private $start = 0;

    /**
     * @var int
     */
    private $currentPage = 0;

    /**
     * @var array
     */
    private $pagination = [];

    /**
     * @var int
     */
    private $totalRows = 0;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var string
     */
    private $baseUrl = '#/';

    /**
     * cashPagination constructor.
     *
     * @param string $baseUrl
     * @param int    $limit
     */
    public function __construct($baseUrl, $limit = self::LIMIT)
    {
        $this->baseUrl = $baseUrl;
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return array
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * @return int
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $start
     *
     * @return cashPagination
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param int $totalRows
     *
     * @return cashPagination
     */
    public function setTotalRows($totalRows)
    {
        $this->totalRows = $totalRows;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return cashPagination
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function prepare()
    {
        $this->pagination = [];
        $limit = empty($this->limit) ? 1 : $this->limit;
        $currentPage = floor($this->start / $limit) + 1;
        $totalPages = floor(($this->totalRows - 1) / $limit) + 1;
        $dotsAdded = false;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i < 2) {
                $this->pagination[$i] = ($i - 1) * $limit;
                $dotsAdded = false;
            } else {
                if (abs($i - $currentPage) < 2) {
                    $this->pagination[$i] = ($i - 1) * $limit;
                    $dotsAdded = false;
                } else {
                    if ($totalPages - $i < 1) {
                        $this->pagination[$i] = ($i - 1) * $limit;
                        $dotsAdded = false;
                    } else {
                        if (!$dotsAdded) {
                            $dotsAdded = true;
                            $this->pagination[$i] = false;
                        }
                    }
                }
            }
        }

        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @param waSmarty3View $view
     *
     * @return string
     * @throws waException
     */
    public function render(waSmarty3View $view)
    {
        $path = wa()->getAppPath('templates/include/pagination.html', 'cash');
        $html = $view->renderTemplate(
            $path,
            [
                'start' => $this->start,
                'limit' => $this->limit,
                'total_rows' => $this->totalRows,
                'pagination' => $this->pagination,
                'current_page' => $this->currentPage,
                'show_total_rows' => false,
                'show_records_on_page' => false,
                'records_on_page' => [10, 30, 50, 100, 200, 500],
                'base_url' => $this->baseUrl,
            ]
        );

        return $html;
    }
}
