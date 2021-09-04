<?php

namespace App\Data;

class SearchWorkorder 
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var int
     */
    public $page = 1;

    /**
     * Date de création
     *
     * @var dateTime
     */
    public $createdAt;

    /**
     * Machine
     *
     * @var machine
     */
    public $machine;

    /**
     * Utilisateur
     *
     * @var user
     */
    public $user;

    /**
     * Status
     *
     * @var status
     */
    public $status;
}