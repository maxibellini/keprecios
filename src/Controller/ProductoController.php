<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Producto;
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
    		$em->persist($producto);
    		$em->flush();
    		$this->addFlash('exito','¡El producto fue registrado de manera exitosa!');
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
        return $this->render('app/producto/perfil.html.twig', [
            'controller_name' => 'Perfil de Producto',
            'producto' => $producto,
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


}
