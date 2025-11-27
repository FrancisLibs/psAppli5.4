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
        return $user?->getOrganisation();
    }

    public function getService(): ?object
    {
        $user = $this->getUser();
        return $user?->getService();
    }
}
