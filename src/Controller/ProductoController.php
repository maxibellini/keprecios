<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Producto;
use App\Entity\Colaboracion;
use App\Entity\Confianza;
use App\Form\ProductoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProductoController extends AbstractController
{
    /**
     * @Route("/registrar-producto", name="app_producto_registro")
     */
    public function productoRegistro(Request $request)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
    	$producto = new Producto();
        $em = $this->getDoctrine()->getManager();
        $producto->setEm($em);
    	$form = $this->createForm(ProductoType::class , $producto);
    	$form-> handleRequest($request);
    	if($form->isSubmitted() && $form->isValid() ){
    		$em = $this->getDoctrine()->getManager();
            $producto->setEstadoProducto(1);
            if ($this->getUser()){
                $producto->setUser($this->getUser()); 
            }else{
                $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                return $this->redirectToRoute('app_inicio');
            }

            //COLABORACIÓN
            $colaboracion = new Colaboracion();
            $colaboracion->setPuntaje(1);
            $colaboracion->setFecha(new \DateTime());
            $colaboracion->setTipo('alta'); 
            $colaboracion->setDescripcion('+1 por alta de un producto'); 
            $colaboracion->setTipoVoto(0); 
            $producto->addColaboracion($colaboracion);
            $usuario = $producto->getUser();
            $usuColab= $usuario->getPuntosColab();
            $usuRep=  $usuario->getPuntosRep(); 
            if ($usuColab == null){ $usuColab= 0; }
            if ($usuRep == null){ $usuRep= 0; }
            $usuario->setPuntosColab($usuColab+1); 
            $usuario->setPuntosRep($usuRep+1); 
            $usuario->addColaboracion($colaboracion); 
            $em->persist($usuario);
            $producto->setUser($usuario);
            $colaboracion->setUser($usuario);
            $confianzaRepository = $em->getRepository(Confianza::class);
            $confianza = $confianzaRepository->findOneBy([
                'tipo' => 'producto',
                'nombre' => 'intermedio'
            ]);
            if (!$confianza) {
                // Si no existe, crea una nueva Confianza
                $confianza = new Confianza();
                $confianza->setTipo('producto');
                $confianza->setNombre('intermedio');
                $confianza->setLimiteInferior(0);
                $confianza->setLimiteSuperior(0);
            }
            $em->persist($producto);
            $confianza->addProducto($producto);
            $producto->setConfianza($confianza);
            $em->persist($colaboracion);
            $em->persist($confianza);
            
            $em->flush();
    		$this->addFlash('exito','¡El producto fue registrado de manera exitosa!');
            $this->addFlash('exito','¡Has ganado +1 punto por tu colaboración!');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/producto/new.html.twig', [
            'controller_name' => 'Registro de Producto',
            'formulario' => $form->createView(),
            'producto' => $producto
        ]);
    }

    /**
     * @Route("/perfilproducto-{id}", name="app_producto_perfil")
     */
    public function perfilProducto($id)
    {   

        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository("App:Producto")->findOneBy(array('id'=>$id));
        if (!$producto){
            $this->addFlash('fracaso','Error, no se encontró el producto solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        $colaboraciones = $producto->getColaboracions();
        $votosFin = 0;
        foreach ($colaboraciones as $colaboracion) {
            if ($colaboracion->getTipo() == 'baja') {
                $votosFin++;
            }
        }
        
        return $this->render('app/producto/perfil.html.twig', [
            'controller_name' => 'Perfil de Producto',
            'producto' => $producto,
            'votosfin' => $votosFin,
        ]);
    }

    /**
     * @Route("/bajaproducto-{id}", name="app_producto_baja")
     */
    public function bajaProducto($id)
    {   
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager();
        $producto = $em->getRepository("App:Producto")->findOneBy(array('id'=>$id));
        if (!$producto){
            $this->addFlash('fracaso','Error, no se encontró el producto solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        $producto->setEstadoProducto(0); 
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','El producto fue dado de baja correctamente');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se pudo dar de baja el producto');
             return $this->redirectToRoute('app_inicio');    
        }
    }

    /**
     * @Route("/editarproducto-{id}", name="app_producto_editar", methods={"GET","POST"})
     */
    public function editarProducto(Request $request, $id)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager(); 
        $producto =$em->getRepository("App:Producto")->findOneBy(array('id'=>$id));
        if(!$producto){
            $this->addFlash('fracaso','Error, no se encuentra el Producto solicitado');
            return $this->redirectToRoute('app_inicio');
        }
        if($producto->getUser()){
          if($producto->getUser() != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no es el mismo que realizó la carga');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $productos= $em->getRepository("App:Producto")->findAll();
        $gtin= $producto->getGtin();
        $producto->setProductos($productos);

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);       
      // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('exito','¡Los cambios se han guardado correctamente!');
            return $this->redirectToRoute('app_inicio');
        }
        
        return $this->render('app/producto/edit.html.twig', [
            'emi' => $em,
            'productos' => $productos,
            'producto' => $producto,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/baja-p-{idproducto}-{idusuario}", name="app_fin_producto")
     */
    public function finProducto($idproducto,$idusuario)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $producto = $em->getRepository("App:Producto")->findOneBy(array('id'=>$idproducto));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder informar la baja del producto.');
            return $this->redirectToRoute('app_producto_perfil', ['id' => $idproducto]);
        }
        $producto = $em->getRepository("App:Producto")->findOneBy(array('id'=>$idproducto));
        if (!$producto){
            $this->addFlash('fracaso','Error, no se encontró el producto solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if($producto->getEstadoProducto() == 0){
            $this->addFlash('fracaso','Error, el producto que quieres dar de baja no esta activo');
            return $this->redirectToRoute('app_inicio');     
        }   

        foreach ($producto->getColaboracions() as $colaboracion) {
            if ($colaboracion->getUser() === $usuario && $colaboracion->getTipo() === 'baja') {
                $this->addFlash('fracaso','Error, usted ya ha informado la baja de este producto');
                return $this->redirectToRoute('app_producto_perfil', ['id' => $idproducto]);
            }
        }
        $userProducto = $producto->getUser();
        //trato Colaboraciones
        $colaboraciones = $producto->getColaboracions()->filter(function ($colaboracion) {
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
        $colaboracion->setDescripcion('+1 por informar la baja de un producto'); 
        $colaboracion->setTipoVoto(0); 
        $producto->addColaboracion($colaboracion);           
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
        $sumatoriaPuntajes = $sumatoriaPuntajes + 1; 
        //si alcanzó la cantidad de votos se da de baja
        if($sumatoriaPuntajes > 4){
            $producto->setEstadoProducto(0);
            //informar baja del producto
            $em->persist($producto);
            $em->flush(); 
            $this->addFlash('fracaso','El producto has informado se ha dado de baja, ya no estará disponible para su búsqueda'); 
            return $this->redirectToRoute('app_inicio');
           ;
        }
        $em->persist($producto);
        $em->flush();
        return $this->redirectToRoute('app_producto_perfil', ['id' => $idproducto]);

    }

}
