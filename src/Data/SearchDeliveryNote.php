<?php

namespace App\Data;

class SearchDeliveryNote 
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var int
     */
    public $page = 1;

    /**
     * Numéro
     *
     * @var string
     */
    public $number;

    /**
     * Fournisseur
     *
     * @var string
     */
    public $provider;

    /**
     * Organisation
     *
     * @var string
     */
    public $organisation;
}