<?php

namespace App\Data;

class SearchTemplate 
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var int 
     */
    public $page = 1;

    /**
     * Machine
     *
     * @var string
     */
    public $machine;

    /**
     * Organisation
     *
     * @var string
     */
    public $organisation;
}