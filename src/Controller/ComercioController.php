<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comercio;
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
    		$em->persist($comercio);
    		$em->flush();
    		$this->addFlash('exito','¡La solicitud de comercio fue registrada de manera exitosa!'.PHP_EOL.'Cuando se apruebe ya podrán cargar ofertas en él');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/comercio/new.html.twig', [
            'controller_name' => 'Registro de Comercio',
            'formulario' => $form->createView(),
            'comercio' => $comercio
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
        return $this->render('app/comercio/perfil.html.twig', [
            'controller_name' => 'Perfil de Comercio',
            'comercio' => $comercio,
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
        $em->remove($comercio); 
        //$comercio->setEstadoComercio('BAJA');
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','La solicitud fue eliminada correctamente');
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


}
