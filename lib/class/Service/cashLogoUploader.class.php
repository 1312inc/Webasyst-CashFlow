<?php

/**
 * Class cashLogoUploader
 */
final class cashLogoUploader
{
    const ACCOUNT_LOGOS_PATH = 'account_logos';
    const USER_ACCOUNT_LOGOS_PATH = 'user/account_logos';
    const ACCOUNT_LOGO_SIZE  = 192;

    /**
     * @param cashAccount   $account
     * @param waRequestFile $file
     *
     * @return bool
     * @throws waException
     */
    public function uploadAndSaveToAccount(cashAccount $account, waRequestFile $file): bool
    {
        try {
            $logo = $file->waImage();

            $dataPath = $this->getAccountPath($account);
            $dataFullPath = $this->getDataFolder($dataPath);

            waFiles::delete($dataFullPath, true);
            waFiles::create($dataFullPath, true);

            $pathToSave = sprintf('%s/%s', $dataFullPath, $file->name);

            $logo->resize(self::ACCOUNT_LOGO_SIZE, self::ACCOUNT_LOGO_SIZE, waImage::AUTO);
            if ($logo->save($pathToSave)) {
                $path = sprintf('%s/%s', $dataPath, $file->name);
                $account->setIcon($path);

                return true;
            }
        } catch (Exception $exception) {
            cash()->getLogger()->error('Error on account logo upload', $exception);
        }

        return false;
    }

    /**
     * @param waContact     $contact
     * @param waRequestFile $file
     *
     * @return false|string
     */
    public function uploadToContact(waContact $contact, waRequestFile $file)
    {
        try {
            $logo = $file->waImage();

            $dataPath = $this->getUserAccountPath($contact);
            $dataFullPath = $this->getDataFolder($dataPath);

            waFiles::delete($dataFullPath, true);
            waFiles::create($dataFullPath, true);

            $pathToSave = sprintf('%s/%s', $dataFullPath, $file->name);

            $logo->resize(self::ACCOUNT_LOGO_SIZE, self::ACCOUNT_LOGO_SIZE, waImage::AUTO);
            if ($logo->save($pathToSave)) {
                return sprintf('%s/%s', $dataPath, $file->name);
            }
        } catch (Exception $exception) {
            cash()->getLogger()->error('Error on account logo upload', $exception);
        }

        return false;
    }

    /**
     * @param string $logoPath
     *
     * @return string
     * @throws waException
     */
    public static function getUrlToAccountLogo($logoPath): string
    {
        return wa()->getDataUrl($logoPath, true, cashConfig::APP_ID, true);
    }

    /**
     * @param $path
     *
     * @return string
     * @throws waException
     */
    private function getDataFolder($path): string
    {
        return wa()->getDataPath($path, true, cashConfig::APP_ID, true);
    }

    /**
     * @param cashAccount $account
     *
     * @return string
     */
    private function getAccountPath(cashAccount $account): string
    {
        return sprintf('%s%s%s', self::ACCOUNT_LOGOS_PATH, DIRECTORY_SEPARATOR, $account->getId());
    }

    /**
     * @param waContact $contact
     *
     * @return string
     */
    private function getUserAccountPath(waContact $contact): string
    {
        return sprintf('%s%s%s', self::USER_ACCOUNT_LOGOS_PATH, DIRECTORY_SEPARATOR, $contact->getId());
    }
}