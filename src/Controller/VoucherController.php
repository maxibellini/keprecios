<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Voucher;
use App\Entity\Cupon;
use App\Entity\Confianza;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\QrcodeService;

class VoucherController extends AbstractController
{

    private $urlGenerator;
    public function __construct(UrlGeneratorInterface $urlGenerator,QrcodeService $qrcodeService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->qrcodeService = $qrcodeService;
    }
    /**
     * @Route("/vouchers", name="app_vouchers")
     */
    public function Vouchers()
    {
        $em = $this->getDoctrine()->getManager();
        $vouchers = $em->getRepository("App:Voucher")->findBy(array('estado'=> 'ACTIVO'));
        return $this->render('app/voucher/vouchers.html.twig', [
            'vouchers' => $vouchers,
        ]);
    }
    /**
     * @Route("/perfilvoucher-{id}", name="app_voucher_perfil")
     */
    public function perfilVoucher($id)
    {   

        $em = $this->getDoctrine()->getManager();
        $voucher = $em->getRepository("App:Voucher")->findOneBy(array('id'=>$id));
        if (!$voucher){
            $this->addFlash('fracaso','Error, no se encontró el voucher solicitado');
            return $this->redirectToRoute('app_inicio');    
        }

        return $this->render('app/voucher/perfil.html.twig', [
            'voucher' => $voucher,
        ]);
    }
    /**
     * @Route("/canjear-{idvoucher}-{idusuario}", name="app_canjear_voucher")
     */
    public function canjearPuntos($idvoucher,$idusuario)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        $voucher = $em->getRepository("App:Voucher")->findOneBy(array('id'=>$idvoucher));
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder cnajear puntos.');
            return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);
        }
        if (!$voucher){
            $this->addFlash('fracaso','Error, no se encontró el voucher solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if($voucher->getEstado() != 'ACTIVO'){
            $this->addFlash('fracaso','Error, el voucher no esta activo');
            return $this->redirectToRoute('app_inicio');     
        }   
        if($voucher->getDuracion() != null && $voucher->getDuracion() < 1){
            $this->addFlash('fracaso','Error, el voucher ya no tiene cupones disponibles');
            return $this->redirectToRoute('app_inicio');     
        }
        if ($usuario->getPuntosColab() == null || $voucher->getCosto() == null ) {
            $this->addFlash('fracaso','Error durante el canjeo.');
            return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);

        }
        if($voucher->getResponsable() === $usuario){
            $this->addFlash('fracaso','Error, no puedes canjear este voucher ya que eres el responsable del mismo.');
            return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);
        }
        if ($usuario->getPuntosColab() < $voucher->getCosto()) {
            $this->addFlash('fracaso','Error, no tienes suficientes puntos para canjear este voucher.');
            return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);

        }

        $usuario->setPuntosColab($usuario->getPuntosColab() - $voucher->getCosto());
        // Obtener el último cupón
        $ultimoCupon = $em->getRepository(Cupon::class)->findOneBy([], ['nroCupon' => 'DESC']);
        // Obtener el número del último cupón y sumarle 1
        $nuevoNroCupon = $ultimoCupon ? $ultimoCupon->getNroCupon() + 1 : 1;
        // Crear una nueva instancia de Cupon y asignar el nuevo número
        $nuevoCupon = new Cupon();
        $nuevoCupon->setNroCupon($nuevoNroCupon);
        // Generar un número aleatorio de 8 cifras
        $numeroAleatorio = mt_rand(10000000, 99999999);
        $nuevoCupon->setSemilla($numeroAleatorio);
        $hoy= new \DateTime();
        $nuevoCupon->setFechaCreacion($hoy);
        $fechaVto = clone $hoy;
        $fechaVto->modify('+7 days');
        $nuevoCupon->setFechaVto($fechaVto);
        $nuevoCupon->setEstado('SIN CANJEAR');
        $nuevoCupon->setUser($usuario);
        $nuevoCupon->setVoucher($voucher);
        $em->persist($nuevoCupon);
        $usuario->addCupone($nuevoCupon);
        $voucher->setDuracion($voucher->getDuracion() - 1);
        if($voucher->getDuracion()<=0){
            $voucher->setEstado('INACTIVO');
            $voucher->setDuracion(0);
        }
        dd($voucher);
        $em->flush(); 
        $userId = $usuario->getId();
        $url = $this->urlGenerator->generate('app_user_perfil', ['id' => $userId]);
        $this->addFlash('exito','¡Felicitaciones! has obtenido un cupón de descuento para este voucher.<br> Ya podés canjearlo cuando quieras <br>Descargalo desde <a href='.$url.'>tu perfil</a> en la pestaña "Mis Cupones de Descuento."');
        return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);

    }
    /**
     * @Route("/validar-cupon-{idcupon}-{nrocupon}-{semilla}", name="app_validar_cupon")
     */
    public function validarCupon($idcupon, $nrocupon,$semilla)
    {   

        $em = $this->getDoctrine()->getManager();
        $cupon = $em->getRepository("App:Cupon")->findOneBy(array('id'=>$idcupon));

        if (!$cupon){
            $this->addFlash('fracaso','Error, no se encontró el cupon solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getVoucher() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene voucher asociado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getUser() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene usuario asociado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getNroCupon() != $nrocupon || $cupon->getSemilla() != $semilla ){
            $this->addFlash('fracaso','Error, el link del cupon es inválido y no corresponde con los datos del cupón solicitado.');
            return $this->redirectToRoute('app_inicio');    
        }
        return $this->render('app/voucher/validar_cupon.html.twig', [
            'cupon' => $cupon,
        ]);
    }
    /**
     * @Route("/canjeo-cupon-{idcupon}-{nrocupon}-{semilla}-{idusuario}", name="app_canjeo_cupon")
     */
    public function canjeoCupon($idcupon, $nrocupon,$semilla,$idusuario)
    {   

        $em = $this->getDoctrine()->getManager();
        $cupon = $em->getRepository("App:Cupon")->findOneBy(array('id'=>$idcupon));
        $usuario = $em->getRepository("App:User")->findOneBy(array('id'=>$idusuario));
        if (!$cupon){
            $this->addFlash('fracaso','Error, no se encontró el cupon solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if (!$usuario){
            $url = $this->generateUrl('app_login_user');
            $this->addFlash('fracaso','Error, debe <a href='.$url.'>iniciar sesión</a> para poder cnajear puntos.');
            return $this->redirectToRoute('app_voucher_perfil', ['id' => $idvoucher]);
        }
        if ($cupon->getVoucher() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene voucher asociado');
            return $this->redirectToRoute('app_inicio');    
        }

        if($cupon->getVoucher()->getResponsable() !== $usuario){
            $this->addFlash('fracaso','Error, no estas como responsable del voucher de este cupón');
            return $this->redirectToRoute('app_inicio');  
        }
        if ($cupon->getUser() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene usuario asociado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getNroCupon() != $nrocupon || $cupon->getSemilla() != $semilla ){
            $this->addFlash('fracaso','Error, el link del cupon es inválido y no corresponde con los datos del cupón solicitado.');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getEstado() != 'SIN CANJEAR'){
            $this->addFlash('fracaso','Error, el cupon no esta disponible para canje');
            return $this->redirectToRoute('app_inicio');    
        }
        $hoy= new \DateTime();
        $cupon->setFechaUso($hoy);
        $cupon->setEstado('CANJEADO');
        $em->flush();
        return $this->render('app/voucher/validar_cupon.html.twig', [
            'cupon' => $cupon,
        ]);
    }
    /**
     * @param QrcodeService $qrcodeService
     * @Route("/pdf-cupon-{idcupon}-{nrocupon}-{semilla}", name="app_pdf_cupon")
     */
    public function qrPdf($idcupon,$nrocupon,$semilla,Request $request)
    {
        $em = $this->getDoctrine()->getManager();        
        $qrCode = null;
        $cupon = $em->getRepository("App:Cupon")->findOneBy(array('id'=>$idcupon));
        if (!$cupon){
            $this->addFlash('fracaso','Error, no se encontró el cupon solicitado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getVoucher() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene voucher asociado');
            return $this->redirectToRoute('app_inicio');    
        }
        if ($cupon->getUser() == null){
            $this->addFlash('fracaso','Error, el cupon no tiene usuario asociado');
            return $this->redirectToRoute('app_inicio');    
        }
        $nrocupon= $cupon->getNroCupon();   
        $urlBase= $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $qrCode = $this->qrcodeService->qrcode($idcupon,$nrocupon,$semilla,$urlBase);

        $extension = '.pdf';
        $title = 'Validador de Cupon';
        $view = 'app/qr-cupon.html.twig';
        $pdfOptions = new Options();
        $pdfOptions->set('isRemoteEnabled', TRUE);
        $pdfOptions->set('defaultFont', 'Arial'); 
        $pdfOptions->set('IsFontSubsettingEnabled', true);
        $pdfOptions->set('IsHtml5ParserEnabled', true);    
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView($view, [
            'title' => $title,
            'cupon' => $cupon,
            'qrCode'=> $qrCode,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();
        $dompdf->stream('Cupon-'.$cupon->getNroCupon().$cupon->getSemilla(), [
            "Attachment" => false
        ]);
    }  
}
