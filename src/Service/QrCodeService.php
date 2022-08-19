<?php

namespace App\Service;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class QrCodeService
{
    /**
     * Qr code generator
     *
     * @var BuilderInterface $customQrCodeBuilder
     */

    protected $builder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function qrcode($data)
    {
        // Set QrCode
        $result = $this->builder
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(400)
            ->margin(20)
            ->labelText($data)
            ->build();

        // Generate unique name
        $returnNamePng = uniqid('', '') . '.png';
        $namePng = \dirname(__DIR__, 2) . '/public/images/qr-codes/' . $returnNamePng;
        
        // Save img png
        $result->saveToFile($namePng);

        return $returnNamePng;
    }
}
