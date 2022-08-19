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
     * Organisation
     *
     * @var string
     */
    public $organisation;
    
    /**
     * Le code de la pièce
     *
     * @var string
     */
    public $code;

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

    /**
     * Quantité en stock
     * 
     * @var int
     */
    public $qteStock;


}