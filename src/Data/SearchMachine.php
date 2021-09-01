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
     * L'atelier de la machine
     *
     * @var string
     */
    public $workshop;
}
