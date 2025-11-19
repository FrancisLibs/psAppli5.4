<?php

namespace App\Data;

class SearchOrder
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
     * Type de compte
     *
     * @var string
     */
    public $accountType;
    
    /**
     * Le numéro de la commande
     *
     * @var string
     */
    public $number;

    /**
     * Date
     *
     * @var date
     */
    public $date;

    /**
     * Fournisseur
     *
     * @var string
     */
    public $provider;

    /**
     * Désignation de la commande
     *
     * @var string
     */
    public $designation;

    /**
     * Statut de la commande
     *
     * @var string
     */
    public $statut;

    /**
     * Auteur de la commande
     *
     * @var string
     */
    public $createdBy;

     /**
      * Investissement
      *
      * @var boolean
      */
    public $investment;
}
