<?php

namespace App\Data;

class SearchProvider
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var int
     */
    public $page = 1;

    /**
     * Nom forunisseur
     *
     * @var string
     */
    public $name;

    /**
     * Ville
     *
     * @var string
     */
    public $city;

    /**
     * Code
     *
     * @var string
     */
    public $code;
}