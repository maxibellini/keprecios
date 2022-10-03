<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comercio;
use App\Service\ApiMLService;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface; 

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
        $ofertas = $em->getRepository("App:Oferta")
             ->createQueryBuilder('o')
             ->innerJoin('o.comercio', 'c')
             ->andWhere("c.estadoComercio like :estado")
             ->setParameter('estado','ACTIVO')
             ->orderBy('o.fechaCarga', 'DESC')
             ->getQuery()
             ->getResult();
        return $this->render('app/homepage.html.twig', [
            'controller_name' => 'Inicio KePrecios',
            'comercios' => $comercios,
            'ofertas' => $ofertas
        ]);
    }
    /**
     * @Route("/buscar", name="app_buscar")
     */
    public function buscar( PaginatorInterface $paginator, Request $request)
    {
        $texto='';
        $texto = $request->request->get('_buscador');
        $form = $request->request->get('_form');
        $categoria = $request->request->get('_categoria');
        $preciomn = $request->request->get('_preciomn');
        if($preciomn == ''){
          $preciomn = 0;
        }
        $preciomx = $request->request->get('_preciomx');
        if($preciomx == ''){
          $preciomx = 99999999999999999999;
        }
        $marca = $request->request->get('_marca');
        $fini = $request->request->get('_fini');
        if($fini == ''){
          $fini = '1900-01-01';
        }
        $ffin = $request->request->get('_ffin');
        if($ffin == ''){
          $ffin = '3000-12-01';
        }
        $comercio = $request->request->get('_comercio');
        $gtin = $request->request->get('_gtin');
        $compania = $request->request->get('_compania');
        $descuento = $request->request->get('_descuento');
        $em = $this->getDoctrine()->getManager();
        $comercios = $em->getRepository("App:Comercio")->findBy(array('estadoComercio'=> 'ACTIVO'));
        //$ofertas = $em->getRepository("App:Oferta")->findBy(array('estado'=> 1));

        $ofertas = $em->getRepository("App:Oferta")->createQueryBuilder('o')
          ->innerJoin('o.comercio', 'c')
          ->innerJoin('o.producto', 'p')
          ->andWhere("c.estadoComercio like :estado")
          ->setParameter('estado','ACTIVO')
          ->andWhere("p.categoriaProducto like :texto or p.descripcionProducto like :texto or p.marcaProducto like :texto")
          ->setParameter('texto','%'.$texto.'%')
          ->andWhere("p.categoriaProducto like :categoria")
          ->setParameter('categoria','%'.$categoria.'%')
          ->andWhere("c.nombreComercio like :comercio")
          ->setParameter('comercio','%'.$comercio.'%')
          ->andWhere("o.monto >= :preciomn")
          ->setParameter('preciomn',$preciomn)
          ->andWhere("o.monto <= :preciomx")
          ->setParameter('preciomx',$preciomx)
          ->andWhere("o.fechaCarga >= :fini")
          ->setParameter('fini',$fini)
          ->andWhere("o.fechaCarga <= :ffin")
          ->setParameter('ffin',$ffin)
          ->andWhere("p.gtin like :gtin")
          ->setParameter('gtin','%'.$gtin.'%')
          ->andWhere("p.companiaProducto like :compania")
          ->setParameter('compania','%'.$compania.'%')
          ->andWhere("p.marcaProducto like :marca")
          ->setParameter('marca','%'.$marca.'%')
          ->andWhere("o.tipoDescuento like :descuento")
          ->setParameter('descuento','%'.$descuento.'%')
          ->orderBy('o.fechaCarga', 'DESC')
          ->addOrderBy('o.monto', 'ASC')
          ->getQuery()
          ->getResult();
        $ofpaginado = $paginator->paginate(
            // Doctrine Query, not results
            $ofertas,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            24  );
       if($preciomn == 0){
          $preciomn = ''; 
       }
       if($preciomx == 99999999999999999999){
          $preciomx = ''; 
       }
       if($fini== '1900-01-01'){
          $fini = '';

       }
       if($ffin == '3000-12-01'){
          $ffin ='';
       }
        //dd($ofpaginado);
        return $this->render('app/buscar.html.twig', [
            'controller_name' => 'Buscar KePrecios',
            'comercios' => $comercios,
            'texto' => $texto,
            'ofertas' => $ofpaginado,
            'of'=> $ofertas,
            'formu'=> $form,
            'categoria' => $categoria,
            'preciomn' => $preciomn,
            'preciomx' => $preciomx,
            'marca' => $marca,
            'fini' => $fini,
            'ffin' => $ffin,
            'comerciox' => $comercio,
            'gtin' => $gtin,
            'compania' => $compania,
            'marca' => $marca,
            'descuento' => $descuento
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
     * @Route("/lector", name="app_lector")
     */
    public function lector()
    {
      $em = $this->getDoctrine()->getManager();
        
        return $this->render('app/lector.html.twig', [
            'controller_name' => 'Lector KePrecios'
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
