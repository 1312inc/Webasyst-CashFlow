<?php

/**
 * Class cashApiToken
 */
class cashApiToken
{
    const CLIENT_ID = 'vue_backend_client';

    /**
     * @param waContact $contact
     *
     * @return string
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function retrieveToken(waContact $contact): string
    {
        $token = waRequest::server('http_authorization', null, 'string');
        if ($token) {
            $token = preg_replace('~^(Bearer\s)~ui', '', $token);
        }

        $tokensModel = new waApiTokensModel();
        if ($token = $tokensModel->getById($token)) {
            throw new kmwaRuntimeException('Required parameter is missing: access_token', 400);
        }

        $token = $tokensModel->getToken(self::CLIENT_ID, $contact->getId(), cashConfig::APP_ID);

        // remember token usage time
        $tokensModel->updateLastUseDatetime($token);

        return $token;
    }
}
