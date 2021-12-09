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
     * @var string
     */
    public $machine;

    /**
     * Utilisateur
     *
     * @var string
     */
    public $user;

    /**
     * Préventif
     *
     * @var boolean
     */
    public $preventive;

    /**
     * Préventif
     *
     * @var closure
     */
    public $closure;

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