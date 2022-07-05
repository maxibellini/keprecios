<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_inicio")
     */
    public function homepage()
    {
        return $this->render('app/homepage.html.twig', [
            'controller_name' => 'Inicio KePrecios',
        ]);
    }


}
