<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comercio;
use App\Service\ApiMLService;
use Symfony\Component\HttpFoundation\Request;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_inicio")
     */
    public function homepage()
    {
    	$em = $this->getDoctrine()->getManager();
        $comercios = $em->getRepository("App:Comercio")->findBy(array('estadoComercio'=> 'ACTIVO'));
        $ofertas = $em->getRepository("App:Oferta")->findBy(array('estado'=> 1));
        return $this->render('app/homepage.html.twig', [
            'controller_name' => 'Inicio KePrecios',
            'comercios' => $comercios,
            'ofertas' => $ofertas
        ]);
    }

    /**
     * @Route("/map", name="app_map")
     */
    public function map()
    {
    	$em = $this->getDoctrine()->getManager();
        $comercios = $em->getRepository("App:Comercio")->findBy(array('estadoComercio'=> 'ACTIVO'));
        return $this->render('app/map.html.twig', [
            'controller_name' => 'Mapa KePrecios',
            'comercios' => $comercios
        ]);
    }

    /**
     * @Route("/map-ubicacion", name="app_map_ubicacion")
     */
    public function mapUbicacion()
    {
        return $this->render('app/map_ubicacion.html.twig', [
            'controller_name' => 'Mapa ubicación KePrecios',

        ]);
    }

    /**
     * @param int    $codigo
     * @Route("/apiml/{codigo}", name="apiml_test")
     */
    public function testApiML($codigo, Request $request, ApiMLService $renaper)
    {
        $ret = $renaper->fetchByCodigo($codigo, $request);

        return $this->json($ret);
    }

}
