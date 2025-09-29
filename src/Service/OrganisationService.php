<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;

class OrganisationService
{
    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    private function getUser()
    {
        return $this->security->getUser();
    }

    public function getOrganisation(): ?object
    {
        $user = $this->getUser();
        return $user ? $user->getOrganisation() : null;
    }

    public function getService(): ?object
    {
        $user = $this->getUser();
        return $user ? $user->getService() : null;
    }
}
