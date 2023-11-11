<?php

namespace App\Controller\Admin;

use App\Entity\Cupon;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CuponCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cupon::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
