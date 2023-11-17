<?php

namespace App\Service;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;

class QrcodeService 
 {   /**
     * @var BuilderInterface
     */
    protected $builder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function qrcode($query,$nrocupon,$semilla,$urlBase)
    {

        
        $objDateTime = new \DateTime('NOW');
        $dateString = $objDateTime->format('d-m-Y H:i:s');
        $pathini = dirname(__DIR__, 2).'/public/';
        $path = $urlBase.'/public/uploads/images/qr/';

        $url =$urlBase.'/validar-cupon-'.$query.'-'.$nrocupon.'-'.$semilla;
        // set qrcode
        $result = $this->builder
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(150)
            ->margin(5)
            ->labelText('')
            ->labelAlignment(new LabelAlignmentCenter())
            ->labelMargin(new Margin(5, 5, 5, 5))
            //->logoPath($pathini.'/img/assets/images/marca-dc-qr.png')
            //->logoResizeToWidth('100')
            //->logoResizeToHeight('100')
            ->build()
        ;

        //generate name
        $namePng = uniqid('', '') . '.png';

        //Save img png
      //  $result->saveToFile($path.'qr-code/'.$namePng);

        return $result->getDataUri();
    }
}