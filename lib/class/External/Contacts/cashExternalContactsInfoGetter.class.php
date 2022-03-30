<?php

final class cashExternalContactsInfoGetter implements cashExternalSourceInfoGetterInterface
{
    private const APP = 'webasyst';

    public function info(string $id): ?cashExternalInfoDto
    {
        wa(self::APP);
        $contact = new waContact((int) $id);

        if (!$contact->exists()) {
            return null;
        }

        $info = wa()->getAppInfo(self::APP);
        $rootUrl = rtrim(wa()->getRootUrl(true), '/');

        return new cashExternalInfoDto(
            self::APP,
            $info['name'],
            sprintf('%s/%s', $rootUrl, $info['icon']['96'] ?? $info['img'] ?? ''),
            (int) $id,
            $contact->getName(),
            sprintf(
                '%s%s',
                wa()->getConfig()->getHostUrl(),
                waContact::getPhotoUrl($contact->getId(), $contact->get('photo'), 96)
            ),
            sprintf(
                '%s%s/#/contact/%d/',
                wa()->getConfig()->getBackendUrl(true),
                self::APP,
                $id
            ),
            []
        );
    }
}
