<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Comercio;
use App\Entity\Producto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/kp-admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setFaviconPath('favicon.png')

            ->setTitle('<img src="../assets/images/mini-logo.png" style="height: 2.1rem" align="left" alt="Bienvenido" />');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateTimeFormat('dd-MM-yyyy')
            ->setDateFormat('dd-MM-yyyy')
            ->setTimeFormat('H:mm')
            ->setPaginatorPageSize(10);
    }
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Comercios', 'fas fa-building', Comercio::class);
        yield MenuItem::linkToCrud('Productos', 'fas fa-shopping-cart', Producto::class);
    }
}
