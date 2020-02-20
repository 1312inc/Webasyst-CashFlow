<?php

/**
 * Class cashRepeatingTransactionRepository
 *
 * @method cashRepeatingTransactionModel getModel()
 */
class cashRepeatingTransactionRepository extends cashBaseRepository
{
    protected $entity = cashRepeatingTransaction::class;

    /**
     * @param DateTime $date
     *
     * @return cashRepeatingTransaction[]
     * @throws waException
     */
    public function findNeverEndingAfterDate(DateTime $date)
    {
        $sql = <<<SQL
select crt.*, ifnull(repeatings, 0) repeatings, ct.last_transaction_date
from cash_repeating_transaction crt
left join (
    select count(repeating_id) repeatings, repeating_id, max(date) last_transaction_date from cash_transaction where date > s:date group by repeating_id
) ct on ct.repeating_id = crt.id
where crt.repeating_end_type = 'never' and crt.enabled = 1
SQL;

        $transToRepeat = [];
        $data = $this->getModel()->query($sql, ['date' => $date->format('Y-m-d')])->fetchAll();
        foreach ($data as $datum) {
            if (empty($datum['repeatings'])) {
                $transToRepeat[$datum['id']] = self::generateWithData($datum);
            }
        }

        return $transToRepeat;
    }
}
