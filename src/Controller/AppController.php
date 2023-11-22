<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comercio;
use App\Entity\Oferta;
use App\Entity\Colaboracion;
use App\Entity\Cupon;
use App\Entity\User;
use App\Entity\Suspension;
use App\Service\ApiMLService;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface; 
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_inicio")
     */
    public function homepage(SessionInterface $session)
    {
    	$em = $this->getDoctrine()->getManager();
        // Verificar si existe un usuario con el nombre 'usuario_eliminado'
        $usuarioExistente = $em->getRepository(User::class)->findOneBy(['name' => 'usuario_eliminado']);
        if (!$usuarioExistente) {
            // Crear un nuevo usuario con las especificaciones
            $nuevoUsuario = new User();
            $nuevoUsuario->setName('usuario_eliminado');
            $nuevoUsuario->setEmail('usuario_eliminado@keprecios.com');
            $nuevoUsuario->setRoles(['ROLE_USER']);
            $nuevoUsuario->setEstado('dummy');
            $nuevoUsuario->setPassword('usuario_eliminado');
            // Guardar el nuevo usuario en la base de datos
            $em->persist($nuevoUsuario);
            $em->flush();
        }
        $ofertas = $em->getRepository(Oferta::class)->findAll();
        $cupones = $em->getRepository(Cupon::class)->findBy(['estado' => 'SIN CANJEAR']);
        $suspensiones = $em->getRepository(Suspension::class)->findBy(['estado' => 'ACTIVA']);
        $fechaHoy = new \DateTime();
        foreach ($ofertas as $oferta) {
            
            if ($oferta->getFechaVto() == null) {
                $fechaCarga = $oferta->getFechaCarga();
                $fechaVto = clone $fechaCarga;
                $fechaVto->modify('+14 days');
                $oferta->setFechaVto($fechaVto);
            }
            if ($oferta->getFechaUpdate() != null) {
                $fechaUpdate = $oferta->getFechaUpdate();
                $fechaVto = clone $fechaUpdate;
                $fechaVto->modify('+14 days');
                $oferta->setFechaVto($fechaVto);
            }
            if ($oferta->getFechaVto() <= $fechaHoy) {
                $oferta->setEstado(0);
                //aca tratar premios/penalización si corresponde, si terminó en confianza se premia, sino si terminó en desconfianza se penaliza, hay que crearle confianza a la oferta si no la tenía
                $confianza= $oferta->getConfianza();
                $userOferta = $oferta->getUser();
                if ($confianza != null) {
                    $nombreConfianza = $confianza->getNombre();
                    if ($nombreConfianza == 'desconfianza') {
                        //tratar penalización
                        $usuOColab= $userOferta->getPuntosColab();
                        $usuORep=  $userOferta->getPuntosRep(); 
                        if ($usuOColab == null){ $usuOColab= 0; }
                        if ($usuORep == null){ $usuORep= 0; }
                        $userOferta->setPuntosColab($usuOColab-2); 
                        $userOferta->setPuntosRep($usuORep-2); 
                        //----------agregar colaboración mala --------
                        $colab = new Colaboracion();
                        $colab->setPuntaje(-2); // Establece el puntaje deseado
                        $colab->setTipo('mala');
                        $colab->setFecha(new \DateTime());
                        $colab->setDescripcion('-2 puntos por oferta finalizada en confianza Muy Baja'); 
                        $colab->setTipoVoto(0); 
                        $em->persist($colab);
                        $userOferta->addColaboracion($colab);
                        if($userOferta->getPuntosRep() < -4 ){
                            if($userOferta->getCantFaltas() == null){
                                $userOferta->setCantFaltas(1);
                            }
                            $catnFaltasUser = $userOferta->getCantFaltas()+1;
                            $userOferta->setCantFaltas($catnFaltasUser);
                            $userOferta->setEstado('SUSPENDIDO');
                            $suspension = new Suspension();
                            $hoy = new \DateTime();
                            $fechaVto = clone $hoy;
                            $fechaVto->modify('+3 days');
                            $suspension->setFechaCreacion($hoy);
                            $suspension->setFechaVto($fechaVto); 
                            $suspension->setDescripcion('por colaboración mala en puntaje menor a -4 puntos');
                            $suspension->setEstado('ACTIVA');
                            $suspension->setUser($userOferta);
                            $userOferta->addSuspension($suspension);
                            $em->persist($suspension);

                        } 
                        $em->persist($userOferta);
                        if($userOferta->getCantFaltas() > 2){
                            //eliminar usuario
                            $usuarioDumy = $em->getRepository(User::class)->findOneBy(['name' => 'usuario_eliminado']);
                            if (!$usuarioDumy) {
                                // Crear un nuevo usuario con las especificaciones
                                $usuarioDumy = new User();
                                $usuarioDumy->setName('usuario_eliminado');
                                $usuarioDumy->setName('usuario_eliminado@keprecios.com');
                                $usuarioDumy->setRoles(['ROLE_USER']);
                                $usuarioDumy->setEstado('dummy');
                                $usuarioDumy->setPassword('usuario_eliminado');
                                // Guardar el nuevo usuario en la base de datos
                                $em->persist($usuarioDumy);
                                $em->flush();
                            }

                            //tratar asociados
                                $productos = $userOferta->getProductos();
                                foreach ($productos as $producto) {
                                    $producto->setUser($usuarioDumy);
                                }
                                $comercios = $userOferta->getComercio();
                                foreach ($comercios as $comercio) {
                                    $comercio->setUser($usuarioDumy);
                                }
                                $ofertas = $userOferta->getOfertas();
                                foreach ($ofertas as $oferta) {
                                    $oferta->setUser($usuarioDumy);
                                }
                                $colaboracions = $userOferta->getColaboracions();
                                foreach ($colaboracions as $colaboracion) {
                                    $colaboracion->setUser($usuarioDumy);
                                }
                                $vouchers = $userOferta->getVouchers();
                                foreach ($vouchers as $voucher) {
                                    $voucher->setResponsable($usuarioDumy);
                                }
                                $cupons = $userOferta->getCupones();
                                foreach ($cupons as $cupon) {
                                    $cupon->setUser($usuarioDumy);
                                }
                                $suspensions = $userOferta->getSuspensions();
                                foreach ($suspensions as $suspension) {
                                    $suspension->setUser($usuarioDumy);
                                }
                            $em->remove($userOferta);
                        }
                        $em->persist($oferta);
                        $em->flush(); 
                        
                    } elseif ($nombreConfianza == 'confiable') {
                        //tratar premio
                        $usuOColab= $userOferta->getPuntosColab();
                        $usuORep=  $userOferta->getPuntosRep(); 
                        if ($usuOColab == null){ $usuOColab= 0; }
                        if ($usuORep == null){ $usuORep= 0; }
                        $userOferta->setPuntosColab($usuOColab+2); 
                        $userOferta->setPuntosRep($usuORep+2); 
                        //----------agregar colaboración mala --------
                        $colab = new Colaboracion();
                        $colab->setPuntaje(+2); // Establece el puntaje deseado
                        $colab->setTipo('premio');
                        $colab->setFecha(new \DateTime());
                        $colab->setDescripcion('+2 puntos por oferta finalizada en confianza Muy Alta'); 
                        $colab->setTipoVoto(0); 
                        $em->persist($colab);
                        $userOferta->addColaboracion($colab); 
                        $em->persist($userOferta);
                        $em->persist($oferta);
                        
                    }
                }

            }
            $em->persist($oferta);

        }
        foreach ($cupones as $cupon) {
            if ($cupon->getFechaVto() != null) {
               if( $cupon->getFechaVto() <= $fechaHoy){
                    $cupon->setEstado('VENCIDO');
               }
            }
            $em->persist($cupon);
        }
        foreach ($suspensiones as $suspension) {
            if ($suspension->getFechaVto() != null) {
               if( $suspension->getFechaVto() <= $fechaHoy){
                    $suspension->setEstado('CUMPLIDA');
                    if($suspension->getUser() != null){
                        $user=$suspension->getUser();
                        $user->setEstado('ACTIVO');
                        $em->persist($user);  
                        $suspension->setUser($user);
                    }
               }
            }    
            $em->persist($suspension);        
        }
        $em->flush();

        $comercios = $em->getRepository("App:Comercio")->findBy(array('estadoComercio'=> 'ACTIVO'));
        $ofertas = $em->getRepository("App:Oferta")->findBy(array('estado'=> 1));
        $ofertas = $em->getRepository("App:Oferta")
             ->createQueryBuilder('o')
             ->innerJoin('o.comercio', 'c')
             ->andWhere("o.estado = 1")
             ->andWhere("c.estadoComercio like :estado")
             ->setParameter('estado','ACTIVO')
             ->innerJoin('o.producto', 'p')
             ->andWhere("p.estadoProducto = 1")
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
          ->andWhere("p.estadoProducto = 1")
          ->andWhere("o.estado = 1")
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
