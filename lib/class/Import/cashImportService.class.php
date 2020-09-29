<?php

/**
 * Class cashImportService
 */
class cashImportService
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param waRequestFileIterator $files
     * @param array                 $settings
     *
     * @return cashImportFileUploadedEventResponseInterface[]
     */
    public function uploadFile(waRequestFileIterator $files, array $settings = [])
    {
        $responses = [];

        foreach ($files as $file) {
            if ($file->error_code != UPLOAD_ERR_OK) {
                $this->errors[] = $file->error;
            } else {
                try {
                    $f = $this->save($file, $settings);
                    if ($f) {
                        foreach ($f as $eventResponse) {
                            if (!$eventResponse instanceof cashImportFileUploadedEventResponseInterface) {
                                continue;
                            }

                            if ($eventResponse->getError()) {
                                throw new cashValidateException($eventResponse->getError());
                            }

                            $fileType = $eventResponse->getFileType();
                            if (!isset($responses[$fileType])) {
                                $responses[$fileType] = [];
                            }
                            $responses[$fileType][] = [
                                $eventResponse->getIdentification() => $eventResponse->getHtml(),
                            ];
                        }
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e->getMessage();
                }
            }
        }

        return $responses;
    }

    /**
     * @param waRequestFile $file
     *
     * @return bool|string
     */
    public function validFile(waRequestFile $file)
    {
        $name_ext = $file->extension;

        if (stripos($name_ext, 'php') !== false) {
            $name_ext = 'php';
        }

        if (in_array($name_ext, ['php', 'phtml', 'htaccess'], true)) {
            $this->errors[] = sprintf(
                _w('Files with extension .%s are not allowed to security considerations.'),
                $name_ext
            );

            return false;
        }

        if (exif_imagetype($file->tmp_name)) {
            $this->errors[] = _w('File is image.');

            return false;
        }

        return true;
    }

    /**
     * @param waRequestFile $file
     * @param array         $settings
     *
     * @return array|bool
     */
    public function save(waRequestFile $file, array $settings)
    {
        if (!$this->validFile($file)) {
            return false;
        }

        $savePath = wa()->getTempPath(sprintf('import/%d/', wa()->getUser()->getId()), cashConfig::APP_ID);
        if (is_writable($savePath)) {
            $name = $file->name;

            if ($file->uploaded()) {
                if (!preg_match('//u', $name)) {
                    $tmp_name = @iconv('windows-1251', 'utf-8//ignore', $name);
                    if ($tmp_name) {
                        $name = $tmp_name;
                    }
                }

                if ($file->moveTo($savePath, $name)) {

                    $this->errors = [];

                    $event = (new cashImportFileUploadedEvent('', null, $settings))
                        ->setFile($file)
                        ->setSavePath($savePath);

                    return cash()->waDispatchEvent($event);
                }

                $this->errors[] = sprintf(_w('Failed to upload file %s.'), $file->name);
            } else {
                $this->errors[] = sprintf(_w('Failed to upload file %s.'), $file->name).' ('.$file->error.')';
            }
        } else {
            $this->errors[] = _w('Temp path is not writable');
        }

        return false;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}