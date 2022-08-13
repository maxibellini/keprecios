<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Oferta;
use App\Entity\Producto;
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
        //dd($formp);
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
    		$em->persist($oferta);
    		$em->flush();
    		$this->addFlash('exito','¡Tu oferta fue publicada exitosamente!');
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
        return $this->render('app/oferta/perfil.html.twig', [
            'controller_name' => 'Perfil de Oferta',
            'oferta' => $oferta,
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
        $em->remove($oferta); 
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','El oferta fue dado eliminada correctamente');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se pudo eliminar oferta');
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
      // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $hoy= new \DateTime();
            $oferta->setFechaUpdate($hoy);
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

}
