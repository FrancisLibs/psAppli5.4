<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;

class OrganisationService
{
    protected $security;
    protected $user;


    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->user = $this->security->getUser();
    }


    public function getOrganisation()
    {
        return $this->user->getOrganisation();
    }

    public function getService()
    {
        return $this->user->getService();
    }
}