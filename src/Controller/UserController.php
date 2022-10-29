<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/registrarse", name="app_user_registro")
     */
    public function userRegistro(Request $request, UserPasswordEncoderInterface $encoder)
    {
    	$user = new User();
        $em = $this->getDoctrine()->getManager();
        $user->setEm($em);
    	$form = $this->createForm(UserType::class , $user);
    	$form-> handleRequest($request);
    	if($form->isSubmitted() && $form->isValid() ){
    		$em = $this->getDoctrine()->getManager();
    		$user->setRoles(['ROLE_USER']);
            $user->setEstado('ACTIVO');
            $hoy= new \DateTime();
            $user->setFechaRegistro($hoy);
    		$user->setPassword($passwordEncoder = $encoder->encodePassword($user, $form['password']->getData()));
    		$em->persist($user);
    		$em->flush();
    		$this->addFlash('exito','¡Se ha registrado de manera correcta, ya puedes ingresar con tu nombre de usuario y contraseña!');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/user/registro.html.twig', [
            'controller_name' => 'RegistroController',
            'formulario' => $form->createView()
        ]);
    }

    /**
     * @Route("/perfil-{id}", name="app_user_perfil")
     */
    public function perfilUser($id)
    {   
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array('id'=>$id));
        if (!$user){
            $this->addFlash('fracaso','Error, no se encontró el usuario solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        return $this->render('app/user/perfil.html.twig', [
            'controller_name' => 'Perfil de Usuario',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/borraruser-{id}", name="app_user_borrar")
     */
    public function borrarUser($id)
    {   $this->get('security.token_storage')->setToken(null);
        $this->get('session')->invalidate();
        $this->get('session')->clear();

       
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array('id'=>$id));
        if (!$user){
            $this->addFlash('fracaso','Error, no se encontró el usuario solicitado');
            return $this->redirectToRoute('app_inicio');    
        }

        $em->remove($user);
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','Su usuario fue eliminado correctamente');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se eliminó el usuario');
             return $this->redirectToRoute('app_inicio');    
        }
    }

    /**
     * @Route("/editaruser-{id}", name="app_user_editar", methods={"GET","POST"})
     */
    public function editarUser(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager(); 
        $user =$em->getRepository("App:User")->findOneBy(array('id'=>$id));
        if($user){
          if($user != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no corresponde al usuario logueado');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $users= $em->getRepository("App:User")->findAll();
        $nameusuario= $user->getName();
        $emailusuario= $user->getEmail();
        $user->setUsers($users);
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);       
      // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('exito','¡Tus cambios se han guardado correctamente!');
            return $this->redirectToRoute('app_inicio');
        }
        
        return $this->render('app/user/edit.html.twig', [
            'emi' => $em,
            'users' => $users,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/set-posicion-{id}_{latitud}_{longitud}", name="set_posicion")
     */
    public function setPosicion($id,$latitud,$longitud)
    {
        
        $em = $this->getDoctrine()->getManager(); 
        $user =$em->getRepository("App:User")->findOneBy(array('id'=>$id));
        if($user){
          if($user != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no corresponde al usuario logueado');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $user->setLatitud($latitud);
        $user->setLongitud($longitud);
        $em->flush();
        $res= json_encode($user,true);
        return new Response($res);
    }

}
