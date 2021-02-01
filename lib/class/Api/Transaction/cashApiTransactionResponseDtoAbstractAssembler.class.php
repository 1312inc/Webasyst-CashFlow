<?php

/**
 * Class cashApiTransactionResponseDtoAbstractAssembler
 */
abstract class cashApiTransactionResponseDtoAbstractAssembler
{
    /**
     * @var cashUserRepository
     */
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new cashUserRepository();
    }

    protected function getContactData($contactId): array
    {
        $user = $this->userRepository->getUser($contactId);

        return [
            'name' => $user->getName(),
            'firstname' => $user->getContact()->get('firstname'),
            'lastname' => $user->getContact()->get('lastname'),
            'userpic' => rtrim(wa()->getUrl(true), '/') . $user->getUserPic(),
        ];
    }
}
