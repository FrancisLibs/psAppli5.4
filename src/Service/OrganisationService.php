<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;

class OrganisationService
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getOrganisation()
    {
        // gets the current user
        $user = $this->security->getUser();

        $organisation = $user->getOrganisation();

        return $organisation;
    }
}