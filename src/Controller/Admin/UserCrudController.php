<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('User')
            ->setSearchFields(['id', 'email', 'roles']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $email = TextField::new('email');
        $password = TextField::new('password');
        $id = IntegerField::new('id', 'ID');
        $roles = TextField::new('roles');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $email];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $email, $roles, $password];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $email, $password];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $email, $password];
        }
    }
}
