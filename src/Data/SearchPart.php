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
     * Désignation
     *
     * @var Designation
     */
    public $designation;

    /**
     * Référence
     *
     * @var Reference
     */
    public $reference;

    /**
     * Emplacement stock
     *
     * @var Place
     */
    public $place;
}