<?php

/**
 * Trait kmwaWaUserTrait
 */
trait kmwaWaUserTrait
{
    /**
     * @var int
     */
    protected $contact_id;

    /**
     * @var string
     */
    protected $name = '(DELETED USER)';

    /**
     * @var string
     */
    protected $username = '(DELETED USER)';

    /**
     * @var string
     */
    protected $photoUrl = '/wa-content/img/userpic96@2x.jpg';

    /**
     * @var string
     */
    protected $userPic = '/wa-content/img/userpic20@2x.jpg';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $login = 'deleted';

    /**
     * @var bool
     */
    protected $me = false;

    /**
     * @var bool
     */
    protected $exists = false;

    /**
     * @var int
     */
    protected $lastActivity = 0;

    /**
     * @var array
     */
    protected $listActivities;

    /**
     * @var string|null
     */
    protected $email = 'deleted@1312.localhost';

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var waContact
     */
    protected $contact;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return bool
     */
    public function isMe()
    {
        return $this->me;
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->exists;
    }

    /**
     * @return int
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @return array
     */
    public function getListActivities()
    {
        return $this->listActivities;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return self
     */
    protected function init()
    {
        $this->timezone = date_default_timezone_get();
        if ($this->contact->exists()) {
            if (wa()->getUser()) {
                $this->me = ($this->contact->getId() == wa()->getUser()->getId());
            }
            $this->name = $this->contact->getName();
            $this->username = $this->contact->getName();
            $this->contact_id = $this->contact->getId();
            $this->photoUrl = $this->contact->getPhoto();
            $this->login = $this->contact->get('login');
            $this->userPic = $this->contact->getPhoto(20);
            $this->status = $this->contact->getStatus();
            $this->exists = $this->contact->get('is_user') != -1;
            $this->email = $this->contact->get('email', 'default');
            $this->timezone = $this->contact->getTimezone();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     *
     * @return self
     * @throws waException
     */
    public function setContactId($contact_id)
    {
        $this->setContact(new waContact($contact_id));

        return $this;
    }

    /**
     * @return waContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param waContact $contact
     *
     * @return self
     */
    public function setContact(waContact $contact)
    {
        $this->contact = $contact;
        $this->init();
        if ($this->getId() && $this->contact->exists()) {
            $this->contact_id = $contact->getId();
        } else {
//            throw new kmwaLogicException('');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPhotoUrl()
    {
        return $this->photoUrl;
    }

    /**
     * @return string
     */
    public function getUserPic()
    {
        return $this->userPic;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
