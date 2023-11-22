<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comercio;
use App\Entity\Colaboracion;
use App\Entity\Confianza;
use App\Entity\Suspension;
use App\Form\ComercioType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ComercioController extends AbstractController
{
    /**
     * @Route("/solicitar-comercio", name="app_comercio_registro")
     */
    public function comercioRegistro(Request $request, UserPasswordEncoderInterface $encoder)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
    	$comercio = new Comercio();
        $em = $this->getDoctrine()->getManager();
        $comercio->setEm($em);
    	$form = $this->createForm(ComercioType::class , $comercio);
    	$form-> handleRequest($request);
    	if($form->isSubmitted() && $form->isValid() ){
    		$em = $this->getDoctrine()->getManager();
            $comercio->setEstadoComercio('PENDIENTE');
            $hoy= new \DateTime();
            $comercio->setFechaRegistroComercio($hoy);
            if ($this->getUser()){
                $comercio->setUser($this->getUser()); 
            }else{
                $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                return $this->redirectToRoute('app_inicio');
            }
            //COLABORACIÓN
            $colaboracion = new Colaboracion();
            $colaboracion->setPuntaje(1);
            $colaboracion->setFecha(new \DateTime());
            $colaboracion->setTipo('alta'); 
            $colaboracion->setDescripcion('+1 por solicitud de comercio'); 
            $colaboracion->setTipoVoto(0); 
            $comercio->addColaboracion($colaboracion);
            $usuario = $comercio->getUser();
            $usuColab= $usuario->getPuntosColab();
            $usuRep=  $usuario->getPuntosRep(); 
            if ($usuColab == null){ $usuColab= 0; }
            if ($usuRep == null){ $usuRep= 0; }
            $usuario->setPuntosColab($usuColab+1); 
            $usuario->setPuntosRep($usuRep+1); 
            $usuario->addColaboracion($colaboracion); 
            $em->persist($usuario);
            $comercio->setUser($usuario);
            $colaboracion->setUser($usuario);
            $confianzaRepository = $em->getRepository(Confianza::class);
            $confianza = $confianzaRepository->findOneBy([
                'tipo' => 'comercio',
                'nombre' => 'intermedio'
            ]);
            if (!$confianza) {
                // Si no existe, crea una nueva Confianza
                $confianza = new Confianza();
                $confianza->setTipo('comercio');
                $confianza->setNombre('intermedio');
                $confianza->setLimiteInferior(0);
                $confianza->setLimiteSuperior(0);
            }
            $em->persist($comercio);
            $confianza->addComercio($comercio);
            $comercio->setConfianza($confianza);
            $em->persist($colaboracion);
            $em->persist($confianza);
            
            $em->flush();
    		$this->addFlash('exito','¡La solicitud de comercio fue registrada de manera exitosa!'.PHP_EOL.'Cuando se apruebe ya podrán cargar ofertas en él');
            $this->addFlash('exito','¡Has ganado +1 punto por tu colaboración!');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/comercio/new.html.twig', [
            'controller_name' => 'Registro de Comercio',
            'formulario' => $form->createView(),
            'comercio' => $comercio
        ]);
    }
    /**
     * @Route("/comercios-potenciales", name="app_comercios_potenciales")
     */
    public function comerciosPotenciales()
    {
        $em = $this->getDoctrine()->getManager();
        $comercios = $em->getRepository("App:Comercio")->findBy(array('estadoComercio'=> 'PENDIENTE'));
        return $this->render('app/comercio/comercios_potenciales.html.twig', [
            'comercios' => $comercios,
        ]);
    }
    /**
     * @Route("/perfilcomercio-{id}", name="app_comercio_perfil")
     */
    public function perfilComercio($id)
    {   

        $em = $this->getDoctrine()->getManager();
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$id));
        if (!$comercio){
            $this->addFlash('fracaso','Error, no se encontró el comercio solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        $colaboraciones = $comercio->getColaboracions();
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
        return $this->render('app/comercio/perfil.html.twig', [
            'controller_name' => 'Perfil de Comercio',
            'comercio' => $comercio,
            'votosp' => $votosTipo0,
            'votosn' => $votosTipo1,
            'votosfin' => $votosFin,
        ]);
    }

    /**
     * @Route("/bajacomercio-{id}", name="app_comercio_baja")
     */
    public function bajaComercio($id)
    {   
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager();
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$id));
        if (!$comercio){
            $this->addFlash('fracaso','Error, no se encontró el comercio solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if($comercio->getEstadoComercio() == 'ACTIVO'){
            $comercio->setEstadoComercio('BAJA');
        }elseif($comercio->getEstadoComercio() == 'PENDIENTE'){
            $colaboracionesAsociadas = $comercio->getColaboracions();
            foreach ($colaboracionesAsociadas as $colaboracion) {
                $colaboracion->setSujeto((string)$comercio);
                $colaboracion->setComercio(null);
            }
            $em->remove($comercio); 
        }elseif($comercio->getEstadoComercio() == 'BAJA'){  
            $this->addFlash('fracaso','Error, el comercio ya esta dado de baja');
             return $this->redirectToRoute('app_inicio');  
        }     
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','La baja fue exitosa');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se pudo eliminar la solicitud');
             return $this->redirectToRoute('app_inicio');    
        }
    }

    /**
     * @Route("/editarsolicitud-{id}", name="app_comercio_editar", methods={"GET","POST"})
     */
    public function editarComercio(Request $request, $id)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager(); 
        $comercio =$em->getRepository("App:Comercio")->findOneBy(array('id'=>$id));
        if(!$comercio){
            $this->addFlash('fracaso','Error, no se encuentra el Comercio solicitado');
            return $this->redirectToRoute('app_inicio');
        }
        if($comercio->getUser()){
          if($comercio->getUser() != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no es el mismo que realizó la solicitud');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $comercios= $em->getRepository("App:Comercio")->findAll();
        $nameusuario= $comercio->getNombreComercio();
        $emailusuario= $comercio->getEmailComercio();
        $comercio->setComercios($comercios);

        $form = $this->createForm(ComercioType::class, $comercio);
        $form->handleRequest($request);       
      // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('exito','¡Tus cambios se han guardado correctamente!');
            return $this->redirectToRoute('app_inicio');
        }
        
        return $this->render('app/comercio/edit.html.twig', [
            'emi' => $em,
            'comercios' => $comercios,
            'comercio' => $comercio,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/distance*{latitudeFrom}*{longitudeFrom}*{latitudeTo}*{longitudeTo}", name="get_distance")
     */
    function getDistance($latitudeFrom,$longitudeFrom,$latitudeTo,$longitudeTo){ 
        //Get latitude and longitude from geo data
        $unit='K';
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K") {
            $res= round($miles * 1.609344, 2).' km';
        } else if ($unit == "N") {
            $res= round($miles * 0.8684, 2).' nm';
        } else {
             $res=round($miles,2).' mi';
        }
        $resx= json_encode($res,true);
        return new Response($resx);
    }
    /**
     * @Route("/voto-c-{idcomercio}-{idusuario}-{tipo}", name="app_voto_comercio")
     */
    public function votoComercio($idcomercio,$idusuario,$tipo)
    {
      
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$idcomercio));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder votar.');
            return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
        }
        if($usuario->getName() == 'usuario_eliminado'){
             $this->addFlash('fracaso','Error, el id de usuario pertenece a un usuario eliminado');
                return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
        }
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$idcomercio));
        if (!$comercio){
            $this->addFlash('fracaso','Error, no se encontró el comercio solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if($comercio->getEstadoComercio() != 'PENDIENTE'){
            $this->addFlash('fracaso','Error, el comercio que quieres votar no esta disponible para votar.');
            return $this->redirectToRoute('app_inicio');     
        }   
        //creo confianzas si no existen
        $confianzasRepository = $em->getRepository(Confianza::class);
        $tiposYnombres = [
            ['tipo' => 'comercio', 'nombre' => 'desconfianza', 'limiteSuperior'=>'-4', 'limiteInferior'=>'-9999'],
            ['tipo' => 'comercio', 'nombre' => 'bajo','limiteSuperior'=>'-3', 'limiteInferior'=>'-1'],
            ['tipo' => 'comercio', 'nombre' => 'intermedio', 'limiteSuperior'=>'0', 'limiteInferior'=>'0'],
            ['tipo' => 'comercio', 'nombre' => 'medio', 'limiteSuperior'=>'1', 'limiteInferior'=>'4'],
            ['tipo' => 'comercio', 'nombre' => 'confiable', 'limiteSuperior'=>'5', 'limiteInferior'=>'9999'],
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

        if($comercio->getUser() == $usuario){
             $this->addFlash('fracaso','Error, no puedes votar tu propia solicitud de comercio');
                return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
        }
        foreach ($comercio->getColaboracions() as $colaboracion) {
            if ($colaboracion->getUser() === $usuario && $colaboracion->getTipo() === 'voto') {
                $this->addFlash('fracaso','Error, usted ya ha votado en esta solicitud de comercio');
                return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
            }
        }
        $userComercio = $comercio->getUser();

        //trato Colaboraciones
        $colaboraciones = $comercio->getColaboracions()->filter(function ($colaboracion) {
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
        $colaboracion->setDescripcion('+1 por voto de alta de comercio'); 
        $colaboracion->setTipoVoto($tipoVoto); 
        $comercio->addColaboracion($colaboracion);
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
            ->setParameter('tipo', 'comercio')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
        $comercio->setConfianza($confianzaEncajada);
        //si entra en desconfianza
        if($sumatoriaPuntajes < -4){
            $comercio->setEstadoComercio('BAJA');
            //penalizar al usuario que subió
            $usuOColab= $userComercio->getPuntosColab();
            $usuORep=  $userComercio->getPuntosRep(); 
            if ($usuOColab == null){ $usuOColab= 0; }
            if ($usuORep == null){ $usuORep= 0; }
            $userComercio->setPuntosColab($usuOColab-10); 
            $userComercio->setPuntosRep($usuORep-10); 
            //----------agregar colaboración mala --------
            $colab = new Colaboracion();
            $colab->setPuntaje(-10); // Establece el puntaje deseado
            $colab->setTipo('mala');
            $colab->setFecha(new \DateTime());
            $colab->setDescripcion('-10 por solicitud de comercio en desconfianza'); 
            $colab->setTipoVoto(0);
            $colab->setComercio($comercio);
            $em->persist($colab);
            $userComercio->addColaboracion($colab);
                    if($userComercio->getPuntosRep() < -4 ){
                        if($userComercio->getCantFaltas() == null){
                            $userComercio->setCantFaltas(1);
                        }
                        $catnFaltasUser = $userComercio->getCantFaltas()+1;
                        $userComercio->setCantFaltas($catnFaltasUser);
                        $userComercio->setEstado('SUSPENDIDO');
                        $suspension = new Suspension();
                        $hoy = new \DateTime();
                        $fechaVto = clone $hoy;
                        $fechaVto->modify('+3 days');
                        $suspension->setFechaCreacion($hoy);
                        $suspension->setFechaVto($fechaVto); 
                        $suspension->setDescripción('por colaboración mala en puntaje menor a -4 puntos');
                        $suspension->setEstado('ACTIVA');
                        $suspension->setUser($userComercio);
                        $userComercio->addSuspension($suspension);
                        $em->persist($suspension);

                    } 
            $em->persist($userComercio);
            if($userComercio->getCantFaltas() > 2){
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
                    $productos = $userComercio->getProductos();
                    foreach ($productos as $producto) {
                        $producto->setUser($usuarioDumy);
                    }
                    $comercios = $userComercio->getComercio();
                    foreach ($comercios as $comercio) {
                        $comercio->setUser($usuarioDumy);
                    }
                    $ofertas = $userComercio->getOfertas();
                    foreach ($ofertas as $oferta) {
                        $oferta->setUser($usuarioDumy);
                    }
                    $colaboracions = $userComercio->getColaboracions();
                    foreach ($colaboracions as $colaboracion) {
                        $colaboracion->setUser($usuarioDumy);
                    }
                    $vouchers = $userComercio->getVouchers();
                    foreach ($vouchers as $voucher) {
                        $voucher->setResponsable($usuarioDumy);
                    }
                    $cupons = $userComercio->getCupones();
                    foreach ($cupons as $cupon) {
                        $cupon->setUser($usuarioDumy);
                    }
                    $suspensions = $userComercio->getSuspensions();
                    foreach ($suspensions as $suspension) {
                        $suspension->setUser($usuarioDumy);
                    }
                $em->remove($userComercio);
            }
            $em->persist($usuario);
            $em->persist($comercio);

            $em->flush(); 
            $this->addFlash('fracaso','El comercio que votaste ha entrado en desconfianza y se ha dado de baja, por lo que ya no estará disponible.'); 
            return $this->redirectToRoute('app_inicio');

        }
        //si llega a "Muy alta" se da de alta el comercio
        if($sumatoriaPuntajes > 4){
            $comercio->setEstadoComercio('ACTIVO');

            //premiar?
            $usuOColab= $userComercio->getPuntosColab();
            $usuORep=  $userComercio->getPuntosRep(); 
            if ($usuOColab == null){ $usuOColab= 0; }
            if ($usuORep == null){ $usuORep= 0; }
            $userComercio->setPuntosColab($usuOColab+15); 
            $userComercio->setPuntosRep($usuORep+15); 
            $colabok = new Colaboracion();
            $colabok->setPuntaje(+15); // Establece el puntaje deseado
            $colabok->setTipo('premio');
            $colabok->setFecha(new \DateTime());
            $colabok->setDescripcion('+15 por lograr alta de comercio'); 
            $colabok->setTipoVoto(0); 
            $colabok->setComercio($comercio);
            $em->persist($colabok);
            $userComercio->addColaboracion($colabok); 
            $em->persist($userComercio);
            $em->persist($usuario);
            $em->flush();
            $this->addFlash('exito','¡El comercio que votaste ha alcanzado la confianza necesaria y se ha dado de alta de manera exitosa!'); 
        }
        $em->persist($comercio);
        $em->flush();
        return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
    }
    /**
     * @Route("/baja-c-{idcomercio}-{idusuario}", name="app_fin_comercio")
     */
    public function finComercio($idcomercio,$idusuario)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$idcomercio));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder informar la baja del comercio.');
            return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
        }
        if($usuario->getName() == 'usuario_eliminado'){
             $this->addFlash('fracaso','Error, el id de usuario pertenece a un usuario eliminado');
                return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
        }
        $comercio = $em->getRepository("App:Comercio")->findOneBy(array('id'=>$idcomercio));
        if (!$comercio){
            $this->addFlash('fracaso','Error, no se encontró el comercio solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if($comercio->getEstadoComercio() != 'ACTIVO'){
            $this->addFlash('fracaso','Error, el comercio no esta activo');
            return $this->redirectToRoute('app_inicio');     
        }   

        foreach ($comercio->getColaboracions() as $colaboracion) {
            if ($colaboracion->getUser() === $usuario && $colaboracion->getTipo() === 'baja') {
                $this->addFlash('fracaso','Error, usted ya ha informado la baja del comercio');
                return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);
            }
        }
        $userComercio = $comercio->getUser();
        //trato Colaboraciones
        $colaboraciones = $comercio->getColaboracions()->filter(function ($colaboracion) {
            return $colaboracion->getTipo() === 'baja';
        });
        $sumatoriaPuntajes = 1;
        foreach ($colaboraciones as $colaboracion) {
            $sumatoriaPuntajes += $colaboracion->getPuntaje();
        }
        $colaboracion = new Colaboracion();
        $colaboracion->setPuntaje(1); // Establece el puntaje deseado
        $colaboracion->setTipo('baja');
        $colaboracion->setFecha(new \DateTime());
        $colaboracion->setDescripcion('+1 por informar la baja de un comercio'); 
        $colaboracion->setTipoVoto(0); 
        $comercio->addColaboracion($colaboracion);           
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
        //si alcanzó la cantidad de votos se da de baja
        if($sumatoriaPuntajes > 4){
            $hoy= new \DateTime();
            $comercio->setEstadoComercio('BAJA');
            //informar baja de comercio
            $em->persist($comercio);
            $em->flush(); 
            $this->addFlash('fracaso','El comercio que informaste se ha dado de baja, ya no estará disponible.'); 
            return $this->redirectToRoute('app_inicio');
        }
        $em->persist($comercio);
        $em->flush();
        return $this->redirectToRoute('app_comercio_perfil', ['id' => $idcomercio]);

    }
 
}
