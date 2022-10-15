<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Oferta;
use App\Entity\ListaCompra;
use App\Entity\Producto;
use App\Form\ListaCompraType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ListaCompraController extends AbstractController
{
    /**
     * @Route("/lista-compra", name="app_lista_compra")
     */
    public function ListaCompra(Request $request)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
    	$lista = new ListaCompra();
        if ($this->getUser()){
            $lista->setUser($this->getUser());
        }else{
            $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
            return $this->redirectToRoute('app_inicio');
        }
        $em = $this->getDoctrine()->getManager();
    	$form = $this->createForm(ListaCompraType::class , $lista);
        $form-> handleRequest($request);
    	if($form->isSubmitted() && $form->isValid() ){
            if ($form->getClickedButton() && 'savefind' === $form->getClickedButton()->getName()) {
                $em = $this->getDoctrine()->getManager();
                if ($this->getUser()){
                    $lista->setUser($this->getUser());
                }else{
                    $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                    return $this->redirectToRoute('app_inicio');
                }
                $hoy= new \DateTime();
                $lista->setFechaCreacion($hoy);
                $em->persist($lista);
                $em->flush();

                //$this->addFlash('exito','¡Tu Lista fue guardada exitosamente!');
                
                return $this->redirectToRoute('app_lista_buscar', array('id' => $lista->getId()));
            }
    		$em = $this->getDoctrine()->getManager();
            if ($this->getUser()){
                $lista->setUser($this->getUser());
            }else{
                $this->addFlash('fracaso','Error no tiene la sesión iniciada.');
                return $this->redirectToRoute('app_inicio');
            }
            $hoy= new \DateTime();
            $lista->setFechaCreacion($hoy);
    		$em->persist($lista);
    		$em->flush();
    		$this->addFlash('exito','¡Tu Lista fue guardada exitosamente!');
    		return $this->redirectToRoute('app_inicio');
    	}
        return $this->render('app/lista/new.html.twig', [
            'controller_name' => 'Lista de Compra',
            'formulario' => $form->createView(),
            'lista' => $lista
        ]);
    }

    /**
     * @Route("/perfil-lista-{id}", name="app_lista_perfil")
     */
    public function perfilListaCompra($id)
    {   

        $em = $this->getDoctrine()->getManager();
        $lista = $em->getRepository("App:ListaCompra")->findOneBy(array('id'=>$id));
        if (!$lista){
            $this->addFlash('fracaso','Error, no se encontró la lista de compra solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        return $this->render('app/lista/perfil.html.twig', [
            'controller_name' => 'Perfil de ListaCompra',
            'lista' => $lista,
        ]);
    }

    /**
     * @Route("/borrarlista-{id}", name="app_lista_borrar")
     */
    public function bajaListaCompra($id)
    {   
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager();
        $lista = $em->getRepository("App:ListaCompra")->findOneBy(array('id'=>$id));
        if (!$lista){
            $this->addFlash('fracaso','Error, no se encontró la lista de compra solicitada');
            return $this->redirectToRoute('app_inicio');    
        }
        $em->remove($lista); 
        $flush=$em->flush();
        if ($flush == null) {
            $this->addFlash('exito','La lista de compra fue eliminada correctamente');
             return $this->redirectToRoute('app_inicio');    
        } else {
            $this->addFlash('fracaso','Error, no se pudo eliminar lista');
             return $this->redirectToRoute('app_inicio');    
        }
    }

    /**
     * @Route("/editarlista-{id}", name="app_lista_editar", methods={"GET","POST"})
     */
    public function editarListaCompra(Request $request, $id)
    {
        if (!$this->getUser()){
            $this->addFlash('fracaso','Debe iniciar sesión para realizar esta acción');
            return $this->redirectToRoute('app_inicio');  
        }
        $em = $this->getDoctrine()->getManager(); 
        $lista =$em->getRepository("App:ListaCompra")->findOneBy(array('id'=>$id));
        if(!$lista){
            $this->addFlash('fracaso','Error, no se encuentra la lista de compra solicitada');
            return $this->redirectToRoute('app_inicio');
        }
        if($lista->getUser()){
          if($lista->getUser() != $this->getUser()){
            $this->addFlash('fracaso','Error, el usuario no es el mismo que realizó la carga');
            return $this->redirectToRoute('app_inicio');
          }            
        }
        $form = $this->createForm(ListaCompraType::class, $lista);
        $form->handleRequest($request);       
      // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getClickedButton() && 'savefind' === $form->getClickedButton()->getName()) {
                $this->getDoctrine()->getManager()->flush();
                //$this->addFlash('exito','¡Los cambios se han guardado correctamente!');

                return $this->redirectToRoute('app_lista_buscar', array('id' => $id));
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('exito','¡Los cambios se han guardado correctamente!');
            return $this->redirectToRoute('app_inicio');
        }
        
        return $this->render('app/lista/edit.html.twig', [
            'lista' => $lista,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/buscarlista-{id}", name="app_lista_buscar")
     */
    public function buscarListaCompra( $id)
    {
        $em = $this->getDoctrine()->getManager();
        $lista = $em->getRepository("App:ListaCompra")->findOneBy(array('id'=>$id));
        if (!$lista){
            $this->addFlash('fracaso','Error, no se encontró la lista de compra solicitada');
            return $this->redirectToRoute('app_inicio');    
        }        
        $lineas = $lista->getLineasProductos();
        $productos = new ArrayCollection();
        foreach ($lineas as $linea) {
           $productos[] = $linea->getProducto();
        }
        $comerces = $em->getRepository("App:Comercio")->findAll();
        $comercios = $em->getRepository("App:ListaCompra")->createQueryBuilder('lc')
          ->select('c.id','c.nombreComercio','sum(o.monto*lp.cantidad) as montoTotal')
          ->innerJoin('lc.lineasProductos', 'lp')
          ->innerJoin('lp.producto', 'p')
          ->innerJoin('p.ofertas', 'o')
          ->innerJoin('o.comercio', 'c')
          ->setParameter('productos',$productos)
          ->andWhere("o.producto in (:productos)")
          ->setParameter('lista',$lista->getId())
          ->andWhere("lc.id = :lista")
          ->andWhere("o.estado = 1")
          ->groupBy('c.nombreComercio')
          ->setParameter('cantp',count($lineas)-1)
          ->having('COUNT(c.nombreComercio)>:cantp')
          ->orderBy('montoTotal')
          ->getQuery()
          ->getResult();
        $comerciosp = $em->getRepository("App:ListaCompra")->createQueryBuilder('lc')
          ->select('c.id','c.nombreComercio','sum(o.monto*lp.cantidad) as montoTotal','count(lp.producto) as cantProd')
          ->innerJoin('lc.lineasProductos', 'lp')
          ->innerJoin('lp.producto', 'p')
          ->innerJoin('p.ofertas', 'o')
          ->innerJoin('o.comercio', 'c')
          ->setParameter('productos',$productos)
          ->andWhere("o.producto in (:productos)")
          ->setParameter('lista',$lista->getId())
          ->andWhere("lc.id = :lista")
          ->andWhere("o.estado = 1")
          ->groupBy('c.nombreComercio')
          ->setParameter('cantp',count($lineas))
          ->having('COUNT(c.nombreComercio)<:cantp')
          ->addOrderBy('cantProd', 'DESC')
          ->addOrderBy('montoTotal', 'ASC')
          ->getQuery()
          ->getResult();
        return $this->render('app/buscar_lista.html.twig', [
            'lista' => $lista,
            'comercios' => $comercios, 
            'comerciosp' => $comerciosp, 
            'comerces' => $comerces
        ]);
    }
}
