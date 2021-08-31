<?php

namespace App\Data;

class SearchUser
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var integer
     */
    public $page = 1;

    /**
     * Le nom de l'utilisateur
     *
     * @var string
     */
    public $username;

}