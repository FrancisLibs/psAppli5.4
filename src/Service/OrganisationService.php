<?php

namespace App\Service;

use App\Entity\User;


class OrganisationService
{
    private $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function organisation(){
        return $this->user->getOrganisation();
    }
}