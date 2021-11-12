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

    // /**
    //  * Date de création
    //  *
    //  * @var dateTime
    //  */
    // public $createdAt;

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
     * Préventif
     *
     * @var preventive
     */
    public $preventive;

    /**
     * Status
     *
     * @var status
     */
    public $status;

    /**
     * Organisation
     *
     * @var string
     */
    public $organisation;
}