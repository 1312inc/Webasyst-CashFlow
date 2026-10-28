<?php

/**
 * cashLicensing
 */
class cashLicensing extends waLicensing
{
    /**
     * @return bool|string
     */
    public function licenseExpiration()
    {
        $license = $this->getLicense();
        if (empty($license['status'])) {
            // no license, no expiration date
            $result = false;
        } elseif (isset($license['expire_date'])) {
            // license is valid until YYYY-MM-DD format
            $result = (string) $license['expire_date'];
        } else {
            // lifetime license, no expiration date
            $result = false;
        }

        return $result;
    }
}
