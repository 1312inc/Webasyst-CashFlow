<?php

/**
 * Class cashExportCsv
 */
final class cashExportCsv
{
    /**
     * @param string               $name
     * @param cashTransactionDto[] $data
     *
     * @return string
     */
    public function process($name, array $data)
    {
        try {
            $path = wa()->getTempPath(sprintf('export_csv/%s', $name));
            $file = fopen($path, 'wb+');
            $headers = [
                'date',
                'category',
                'description',
                'amount',
                'account',
                'currency',
            ];
            fputcsv($file, $headers, ';');
            foreach ($data as $datum) {
                $row = [
                    $datum->date,
                    $datum->category ? $datum->category->name : '',
                    $datum->description ?: '',
                    $datum->amount,
                    $datum->account->name,
                    $datum->account->currency->getCode()
                ];
                fputcsv($file, $row, ';');
            }
            fclose($file);

            return $path;
        } catch (Exception $exception) {
            cash()->getLogger()->error('Something goes wrong on export to CSV', $exception);
        }
    }
}
