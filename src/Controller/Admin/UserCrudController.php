<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DatetimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Usuario')
            ->setEntityLabelInPlural('Usuarios')
            ->setSearchFields(['id', 'name', 'email', 'nombrePersona', 'apellidoPersona', 'roles']);
    }

    public function configureFields(string $pageName): iterable
    {
        $badges = [];
        foreach ($this->roles as $key => $value) {
            $badges[$value] = 'success';
        }
        $roles = ChoiceField::new('roles')->setChoices($this->roles)->allowMultipleChoices(true)->renderExpanded(true)->renderAsBadges($badges);
        $id = IntegerField::new('id', 'ID');
        $name = TextField::new('name');
        $email = TextField::new('email');
        $nombre = TextField::new('nombrePersona');
        $apellido = TextField::new('apellidoPersona');
        $telefono = TextField::new('telefono');
        $telefonoAlternativo = TextField::new('telefonoAlternativo');
        $domicilio = TextField::new('domicilio');
        $localidadDomicilio = AssociationField::new('localidadDomicilio');
        $codigoPostal = TextField::new('codigoPostal');
        $fechaNacimiento = DateField::new('fechaNacimiento');
        $fechaRegistro = DateField::new('fechaRegistro');
        $ultimaConexion = DateTimeField::new('fechaRegistro');
        $latitud = NumberField::new('latitud');
        $longitud = NumberField::new('longitud');
        $sexo = ChoiceField::new('sexo')
              ->setChoices(['Masculino' => 'Masculino', 'Femenino' => 'Femenino', 'No binario' => 'No binario']);
        $estado = ChoiceField::new('estado')
              ->setChoices(['ACTIVO' => 'ACTIVO', 'SUSPENDIDO' => 'SUSPENDIDO', 'BANEADO' => 'BANEADO', '' => '']);
        $image = TextAreaField::new('imageFile')
              ->setFormType(VichImageType::class)
              ->setLabel('Imagen')           
              ->setFormTypeOption('allow_delete',true);
        $password = TextField::new('password');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $email, $nombre, $apellido, $estado];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $email, $nombre, $apellido, $telefono, $telefonoAlternativo, $domicilio, $localidadDomicilio, $codigoPostal, $fechaNacimiento, $fechaRegistro, $ultimaConexion, $latitud, $longitud, $sexo, $estado, $image, $roles];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$id, $name, $email, $nombre, $apellido, $telefono, $telefonoAlternativo, $domicilio, $localidadDomicilio, $codigoPostal, $fechaNacimiento, $fechaRegistro, $ultimaConexion, $latitud, $longitud, $sexo, $estado, $image, $roles];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$id, $name, $email, $nombre, $apellido, $telefono, $telefonoAlternativo, $domicilio, $localidadDomicilio, $codigoPostal, $fechaNacimiento, $fechaRegistro, $ultimaConexion, $latitud, $longitud, $sexo, $estado, $image, $roles];
        }
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
    /**
     * Class constructor.
     */
    private $roles;

    public function __construct($rols)
    {
        $this->roles = $rols;
    }

}
