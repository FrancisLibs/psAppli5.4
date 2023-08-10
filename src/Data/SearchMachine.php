<?php

namespace App\Data;

class SearchMachine
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var integer
     */
    public $page = 1;

    /**
     * La désignation de la machine
     *
     * @var string
     */
    public $internalCode;

    /**
     * La désignation de la machine
     *
     * @var string
     */
    public $designation;

    /**
     * Le constructeur de la machine
     *
     * @var string
     */
    public $constructor;

    /**
     * Le modèle de la machine
     *
     * @var string
     */
    public $model;

     /**
     * Le numéro de série de la machine
     *
     * @var string
     */
    public $serialNumber;

    /**
     * L'atelier de la machine
     *
     * @var string
     */
    public $workshop;

    /**
     * Machine active
     *
     * @var string
     */
    public $active;
}
