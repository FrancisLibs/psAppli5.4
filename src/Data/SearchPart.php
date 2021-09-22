<?php

namespace App\Data;

class SearchPart 
{
    /**
     * Numéro de page pour knp_paginator
     *
     * @var integer
     */
    public $page = 1;

    /**
     * Le code de la pièce
     *
     * @var string
     */
    public $code;

    /**
     * Organisation
     *
     * @var string
     */
    public $organisation;

    /**
     * Désignation
     *
     * @var string
     */
    public $designation;

    /**
     * Référence
     *
     * @var string
     */
    public $reference;

    /**
     * Emplacement stock
     *
     * @var string
     */
    public $place;
}