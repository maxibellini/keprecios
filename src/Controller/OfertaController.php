<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Oferta;
use App\Entity\Colaboracion;
use App\Entity\Confianza;
use App\Entity\Producto;
use App\Entity\Suspension;
use App\Form\OfertaType;
use App\Form\ProductoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class OfertaController extends AbstractController
{
    /**
     * @Route("/registrar-oferta", name="app_oferta_registro")
     */
    public function ofertaRegistro(Request $request)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
    	$oferta = new Oferta();
        $em = $this->getDoctrine()->getManager();
        $oferta->setEm($em);
    	$form = $this->createForm(OfertaType::class , $oferta);
        $form-> handleRequest($request);
    	if($form->isSubmitted() && $form->isValid() ){
    		$em = $this->getDoctrine()->getManager();

            $oferta->setEstado(1);
            if ($this->getUser()){
                $oferta->setUser($this->getUser());
                $producto=$oferta->getProducto();
                if ($producto->getUser() == ''){
                    $producto->setUser($this->getUser());
                    $producto->setEstadoProducto(1);
                } 
            }else{
                $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                return $this->redirectToRoute('app_inicio');
            }
            $hoy= new \DateTime();
            $oferta->setFechaCarga($hoy);
            $fechaVto = clone $hoy;
            $fechaVto->modify('+14 days');
            $oferta->setFechaVto($fechaVto);
            //COLABORACIÓN
            $colaboracion = new Colaboracion();
            $colaboracion->setPuntaje(1);
            $colaboracion->setFecha(new \DateTime());
            $colaboracion->setTipo('alta'); 
            $colaboracion->setDescripcion('+1 por alta de oferta'); 
            $colaboracion->setTipoVoto(0); 
            $oferta->addColaboracion($colaboracion);
            $usuario = $oferta->getUser();
            $usuColab= $usuario->getPuntosColab();
            $usuRep=  $usuario->getPuntosRep(); 
            if ($usuColab == null){ $usuColab= 0; }
            if ($usuRep == null){ $usuRep= 0; }
            $usuario->setPuntosColab($usuColab+1); 
            $usuario->setPuntosRep($usuRep+1); 
            $usuario->addColaboracion($colaboracion); 
            $em->persist($usuario);
            $oferta->setUser($usuario);
            $colaboracion->setUser($usuario);
            $confianzaRepository = $em->getRepository(Confianza::class);
            $confianza = $confianzaRepository->findOneBy([
                'tipo' => 'oferta',
                'nombre' => 'intermedio'
            ]);
            if (!$confianza) {
                // Si no existe, crea una nueva Confianza
                $confianza = new Confianza();
                $confianza->setTipo('oferta');
                $confianza->setNombre('intermedio');
                $confianza->setLimiteInferior(0);
                $confianza->setLimiteSuperior(0);
        
            }

            $oferta->setConfianza($confianza);
            $em->persist($oferta);
            $confianza->addOfertum($oferta);
    		$em->persist($colaboracion);
            
            $em->persist($confianza);
            
    		$em->flush();
    		$this->addFlash('exito','¡Tu oferta fue publicada exitosamente!');
            $this->addFlash('exito','¡Has ganado +1 punto por tu colaboración!');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/oferta/new.html.twig', [
            'controller_name' => 'Registro de Oferta',
            'formulario' => $form->createView(),
            //'formp' => $formp->createView(),
            'oferta' => $oferta
        ]);
    }

    /**
     * @Route("/perfiloferta-{id}", name="app_oferta_perfil")
     */
    public function perfilOferta($id)
    {   

        $em = $this->getDoctrine()->getManager();
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$id));
        if (!$oferta){
            $this->addFlash('fracaso','Error, no se encontró el oferta solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        $colaboraciones = $oferta->getColaboracions();
        $votosTipo0 = 0;
        $votosTipo1 = 0;
        $votosFin = 0;
        foreach ($colaboraciones as $colaboracion) {
            if ($colaboracion->getTipo() == 'voto') {
                if ($colaboracion->getTipoVoto() == 0) {
                    $votosTipo0++;
                } elseif ($colaboracion->getTipoVoto() == 1) {
                    $votosTipo1++;
                }
            }
        }
        foreach ($colaboraciones as $colaboracion) {
            if ($colaboracion->getTipo() == 'baja') {
                $votosFin++;
            }
        }
        
        return $this->render('app/oferta/perfil.html.twig', [
            'controller_name' => 'Perfil de Oferta',
            'oferta' => $oferta,
            'votosp' => $votosTipo0,
            'votosn' => $votosTipo1,
            'votosfin' => $votosFin,
        ]);
    }

    /**
     * @Route("/borraroferta-{id}", name="app_oferta_borrar")
     */
    public function bajaOferta($id)
    {   
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager();
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$id));
        if (!$oferta){
            $this->addFlash('fracaso','Error, no se encontró el oferta solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        $oferta->setEstado(0);
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','La oferta fue dada de baja correctamente');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se pudo dar de baja la oferta');
             return $this->redirectToRoute('app_inicio');    
        }
    }

    /**
     * @Route("/editaroferta-{id}", name="app_oferta_editar", methods={"GET","POST"})
     */
    public function editarOferta(Request $request, $id)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager(); 
        $oferta =$em->getRepository("App:Oferta")->findOneBy(array('id'=>$id));
        if(!$oferta){
            $this->addFlash('fracaso','Error, no se encuentra el oferta solicitado');
            return $this->redirectToRoute('app_inicio');
        }
        if($oferta->getUser()){
          if($oferta->getUser() != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no es el mismo que realizó la carga');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $ofertas= $em->getRepository("App:Oferta")->findAll();
        
        $oferta->setOfertas($ofertas);

        $form = $this->createForm(OfertaType::class, $oferta);
        $form->handleRequest($request);       
        if ($form->isSubmitted() && $form->isValid()) {
            $hoy= new \DateTime();
            $oferta->setFechaUpdate($hoy);
            $fechaVto = clone $hoy;
            $fechaVto->modify('+14 days');
            $oferta->setFechaVto($fechaVto);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('exito','¡Los cambios se han guardado correctamente!');
            return $this->redirectToRoute('app_inicio');
        }
        
        return $this->render('app/oferta/edit.html.twig', [
            'emi' => $em,
            'ofertas' => $ofertas,
            'oferta' => $oferta,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $gtin
     * @Route("/productojson/{gtin}", name="producto_json")
     */
    public function productojson($gtin)
    {
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository(Producto::class)->createQueryBuilder('u')
                        ->select('u.id','u.descripcionProducto','u.marcaProducto', 'u.netContent')
                        ->where('u.gtin like :gtin')
                        ->setParameter('gtin',$gtin)
                        ->setMaxResults(1)
                        ->getQuery(); 
        $result = $producto->getArrayResult();    
        $res= json_encode($result,true);
        return new Response($res);
    }

    /**
     * @Route("/registrar-producto-new", name="app_producto_registro_new")
     */
    public function productoRegistroNew(Request $request)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_producto_succeful', array('gtin' => $gtin));  
        }
        $producto = new Producto();
        $em = $this->getDoctrine()->getManager();
        $producto->setEm($em);
        $form = $this->createForm(ProductoType::class , $producto);
        $form-> handleRequest($request);
        if($form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();
            $producto->setEstadoProducto(1);
            $gtin= $producto->getGtin();
            if ($this->getUser()){
                $producto->setUser($this->getUser()); 
            }else{
                $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                return $this->redirectToRoute('app_producto_succeful', array('gtin' => $gtin));
            }
            $em->persist($producto);
            $em->flush();
            $this->addFlash('exito','¡El producto fue registrado de manera exitosa!');
            return $this->redirectToRoute('app_producto_succeful', array('gtin' => $gtin));
        }
        return $this->render('app/oferta/producto/new.html.twig', [
            'controller_name' => 'Registro de Producto',
            'formulario' => $form->createView(),
            'producto' => $producto
        ]);
    }

    /**
     * @Route("/producto-succesful-{gtin}", name="app_producto_succeful")
     */
    public function productoSucceful($gtin)
    {
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository("App:Producto")->findOneBy(array('gtin'=>$gtin));
        return $this->render('app/oferta/producto/succeful.html.twig', [
            'producto' => $producto
        ]);
    }
    /**
     * @Route("/voto-o-{idoferta}-{idusuario}-{tipo}", name="app_voto_oferta")
     */
    public function votoOferta($idoferta,$idusuario,$tipo)
    {
      
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$idoferta));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder votar.');
            return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
        }
        if($usuario->getName() == 'usuario_eliminado'){
             $this->addFlash('fracaso','Error, el id de usuario pertenece a un usuario eliminado');
                return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
        }
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$idoferta));
        if (!$oferta){
            $this->addFlash('fracaso','Error, no se encontró la oferta solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        if($oferta->getEstado() == 0){
            $this->addFlash('fracaso','Error, la oferta que quieres votar no esta activa');
            return $this->redirectToRoute('app_inicio');     
        }   
        //creo confianzas si no existen
        $confianzasRepository = $em->getRepository(Confianza::class);
        $tiposYnombres = [
            ['tipo' => 'oferta', 'nombre' => 'desconfianza', 'limiteSuperior'=>'-4', 'limiteInferior'=>'-9999'],
            ['tipo' => 'oferta', 'nombre' => 'bajo','limiteSuperior'=>'-3', 'limiteInferior'=>'-1'],
            ['tipo' => 'oferta', 'nombre' => 'intermedio', 'limiteSuperior'=>'0', 'limiteInferior'=>'0'],
            ['tipo' => 'oferta', 'nombre' => 'medio', 'limiteSuperior'=>'1', 'limiteInferior'=>'4'],
            ['tipo' => 'oferta', 'nombre' => 'confiable', 'limiteSuperior'=>'5', 'limiteInferior'=>'9999'],
        ];
        foreach ($tiposYnombres as $tipoYnombre) {
            $confianza = $confianzasRepository->findOneBy($tipoYnombre);
            if ($confianza === null) {
                $confianza = new Confianza();
                $confianza->setTipo($tipoYnombre['tipo']);
                $confianza->setNombre($tipoYnombre['nombre']);
                $confianza->setLimiteInferior($tipoYnombre['limiteInferior']); 
                $confianza->setLimiteSuperior($tipoYnombre['limiteSuperior']); 
                $em->persist($confianza);
            }
        }
        $em->flush();

        if($oferta->getUser() == $usuario){
             $this->addFlash('fracaso','Error, no puedes votar la oferta que has registrado');
                return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
        }
        foreach ($oferta->getColaboracions() as $colaboracion) {
            if ($colaboracion->getUser() === $usuario && $colaboracion->getTipo() === 'voto') {
                $this->addFlash('fracaso','Error, usted ya ha votado en esta oferta');
                return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
            }
        }
        $userOferta = $oferta->getUser();

        //trato Colaboraciones
        $colaboraciones = $oferta->getColaboracions()->filter(function ($colaboracion) {
            return $colaboracion->getTipo() === 'voto';
        });
        $sumatoriaPuntajes = 0;
        foreach ($colaboraciones as $colaboracion) {
            if($colaboracion->getTipoVoto() == 1){
               $sumatoriaPuntajes = $sumatoriaPuntajes - 1;
            }else{
                $sumatoriaPuntajes = $sumatoriaPuntajes + 1; 
            }
        }
        if($tipo == 'p'){
            $tipoVoto = 0;$sumatoriaPuntajes = $sumatoriaPuntajes + 1;
        }else{
           $tipoVoto = 1;$sumatoriaPuntajes = $sumatoriaPuntajes -1;
        }
        $colaboracion = new Colaboracion();
        $colaboracion->setPuntaje(1); // Establece el puntaje deseado
        $colaboracion->setTipo('voto');
        $colaboracion->setFecha(new \DateTime());
        $colaboracion->setDescripcion('+1 por voto de oferta'); 
        $colaboracion->setTipoVoto($tipoVoto); 
        $oferta->addColaboracion($colaboracion);
        //puntuo al usuario
            $usuario = $em->getRepository(User::class)->find($idusuario);
            $usuColab= $usuario->getPuntosColab();
            $usuRep=  $usuario->getPuntosRep(); 
            if ($usuColab == null){ $usuColab= 0; }
            if ($usuRep == null){ $usuRep= 0; }
            $usuario->setPuntosColab($usuColab+1); 
            $usuario->setPuntosRep($usuRep+1); 
            $usuario->addColaboracion($colaboracion); 
            $em->persist($usuario);
        $this->addFlash('exito','¡Has ganado +1 punto por tu colaboración!');    
        //trato la confianza
        switch (true) {
            case ($sumatoriaPuntajes >= -9999 && $sumatoriaPuntajes <= -4):
                $nombre = 'desconfianza';
                break;
            case ($sumatoriaPuntajes >= -3 && $sumatoriaPuntajes <= -1):
                $nombre = 'bajo';
                break;
            case ($sumatoriaPuntajes >= 0 && $sumatoriaPuntajes <= 0):
                $nombre = 'intermedio';
                break;
            case ($sumatoriaPuntajes >= 1 && $sumatoriaPuntajes <= 4):
                $nombre = 'medio';
                break;
            case ($sumatoriaPuntajes >= 5 && $sumatoriaPuntajes <= 9999):
                $nombre = 'confiable';
                break;
            default:
                $nombre = '';
        }
        $confianzaEncajada = $confianzasRepository->createQueryBuilder('c')
            ->where('c.tipo = :tipo AND c.nombre = :nombre')
            ->setParameter('tipo', 'oferta')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
        $oferta->setConfianza($confianzaEncajada);
        //si entra en desconfianza
        if($sumatoriaPuntajes < -4){
            $hoy= new \DateTime();
            $oferta->setFechaVto($hoy);
            $oferta->setEstado(0);
            //penalizar al usuario que subió, hay que felicitar al votante
            
            $usuOColab= $userOferta->getPuntosColab();
            $usuORep=  $userOferta->getPuntosRep(); 
            if ($usuOColab == null){ $usuOColab= 0; }
            if ($usuORep == null){ $usuORep= 0; }
            $userOferta->setPuntosColab($usuOColab-3); 
            $userOferta->setPuntosRep($usuORep-3); 
            //----------agregar colaboración mala --------
            $colab = new Colaboracion();
            $colab->setPuntaje(-3); // Establece el puntaje deseado
            $colab->setTipo('mala');
            $colab->setFecha(new \DateTime());
            $colab->setDescripcion('-3 por oferta en desconfianza'); 
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
                    $usuarioDumy->setEmail('usuario_eliminado@keprecios.com');
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
            $em->persist($usuario);
            $em->persist($oferta);
            $em->flush(); 
            $this->addFlash('fracaso','La oferta que votaste ha entrado en desconfianza, por lo que ya no estará disponible para su búsqueda'); 
            return $this->redirectToRoute('app_inicio');

        }
        //si llega a oferta top
        if($sumatoriaPuntajes > 5){
            $oferta->setEsTop(1);
            //premiar?
            $usuOColab= $userOferta->getPuntosColab();
            $usuORep=  $userOferta->getPuntosRep(); 
            if ($usuOColab == null){ $usuOColab= 0; }
            if ($usuORep == null){ $usuORep= 0; }
            $userOferta->setPuntosColab($usuOColab+3); 
            $userOferta->setPuntosRep($usuORep+3); 
            $colabok = new Colaboracion();
            $colabok->setPuntaje(+3); // Establece el puntaje deseado
            $colabok->setTipo('premio');
            $colabok->setFecha(new \DateTime());
            $colabok->setDescripcion('+3 por alcanzar oferta hot'); 
            $colabok->setTipoVoto(0); 
            $em->persist($colabok);
            $userOferta->addColaboracion($colabok); 
            $em->persist($userOferta);
            $em->persist($usuario);
            $em->flush();
        }
        $em->persist($oferta);
        $em->flush();
        return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
    }
    /**
     * @Route("/baja-o-{idoferta}-{idusuario}", name="app_fin_oferta")
     */
    public function finOferta($idoferta,$idusuario)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$idoferta));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder informar el fin de la oferta.');
            return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
        }
        if($usuario->getName() == 'usuario_eliminado'){
             $this->addFlash('fracaso','Error, el id de usuario pertenece a un usuario eliminado');
                return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
        }
        $oferta = $em->getRepository("App:Oferta")->findOneBy(array('id'=>$idoferta));
        if (!$oferta){
            $this->addFlash('fracaso','Error, no se encontró la oferta solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        if($oferta->getEstado() == 0){
            $this->addFlash('fracaso','Error, la oferta que quieres votar no esta activa');
            return $this->redirectToRoute('app_inicio');     
        }   
        //creo confianzas si no existen
        $confianzasRepository = $em->getRepository(Confianza::class);
        $tiposYnombres = [
            ['tipo' => 'oferta', 'nombre' => 'desconfianza', 'limiteSuperior'=>'-4', 'limiteInferior'=>'-9999'],
            ['tipo' => 'oferta', 'nombre' => 'bajo','limiteSuperior'=>'-3', 'limiteInferior'=>'-1'],
            ['tipo' => 'oferta', 'nombre' => 'intermedio', 'limiteSuperior'=>'0', 'limiteInferior'=>'0'],
            ['tipo' => 'oferta', 'nombre' => 'medio', 'limiteSuperior'=>'1', 'limiteInferior'=>'4'],
            ['tipo' => 'oferta', 'nombre' => 'confiable', 'limiteSuperior'=>'5', 'limiteInferior'=>'9999'],
        ];
        foreach ($tiposYnombres as $tipoYnombre) {
            $confianza = $confianzasRepository->findOneBy($tipoYnombre);
            if ($confianza === null) {
                $confianza = new Confianza();
                $confianza->setTipo($tipoYnombre['tipo']);
                $confianza->setNombre($tipoYnombre['nombre']);
                $confianza->setLimiteInferior($tipoYnombre['limiteInferior']); 
                $confianza->setLimiteSuperior($tipoYnombre['limiteSuperior']); 
                $em->persist($confianza);
            }
        }
        $em->flush(); 
        foreach ($oferta->getColaboracions() as $colaboracion) {
            if ($colaboracion->getUser() === $usuario && $colaboracion->getTipo() === 'baja') {
                $this->addFlash('fracaso','Error, usted ya ha informado el fin de esta oferta');
                return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);
            }
        }
        $userOferta = $oferta->getUser();
        //trato Colaboraciones
        $colaboraciones = $oferta->getColaboracions()->filter(function ($colaboracion) {
            return $colaboracion->getTipo() === 'baja';
        });
        $sumatoriaPuntajes = 0;
        foreach ($colaboraciones as $colaboracion) {
            $sumatoriaPuntajes += $colaboracion->getPuntaje();
        }
        $colaboracion = new Colaboracion();
        $colaboracion->setPuntaje(1); // Establece el puntaje deseado
        $colaboracion->setTipo('baja');
        $colaboracion->setFecha(new \DateTime());
        $colaboracion->setDescripcion('+1 por informar el fin de una oferta'); 
        $colaboracion->setTipoVoto(0); 
        $oferta->addColaboracion($colaboracion);           
        //puntuo al usuario
            $usuario = $em->getRepository(User::class)->find($idusuario);
            $usuColab= $usuario->getPuntosColab();
            $usuRep=  $usuario->getPuntosRep(); 
            if ($usuColab == null){ $usuColab= 0; }
            if ($usuRep == null){ $usuRep= 0; }
            $usuario->setPuntosColab($usuColab+1); 
            $usuario->setPuntosRep($usuRep+1); 
            $usuario->addColaboracion($colaboracion); 
            $em->persist($usuario);
        $this->addFlash('exito','¡Has ganado +1 punto por tu colaboración!'); 
        $sumatoriaPuntajes=$sumatoriaPuntajes + 1;
        //si alcanzó la cantidad de votos se da de baja
        if($sumatoriaPuntajes > 3){
            $hoy= new \DateTime();
            $oferta->setFechaVto($hoy);
            $oferta->setEstado(0);
            //si finalizó en desconfianza
            if( $oferta->getConfianza() != null){
                $nombreConfianza = $oferta->getConfianza()->getNombre();
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
                                $usuarioDumy->setEmail('usuario_eliminado@keprecios.com');
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
                        $colab->setDescripcion('+2 puntos por oferta finalizada en confiable'); 
                        $colab->setTipoVoto(0); 
                        $em->persist($colab);
                        $userOferta->addColaboracion($colab); 
                        $em->persist($userOferta);
                        $em->persist($oferta);  
                        $em->flush();     
                    }
            }
            //informar baja de oferta
            $em->persist($oferta);
            $em->flush(); 
            $this->addFlash('fracaso','La oferta que has informado se ha dado de baja, ya no estará disponible para su búsqueda'); 
            return $this->redirectToRoute('app_inicio');
           ;
        }
        $em->persist($oferta);
        $em->flush();
        return $this->redirectToRoute('app_oferta_perfil', ['id' => $idoferta]);

    }

}
