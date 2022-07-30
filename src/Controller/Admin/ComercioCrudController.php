<?php

namespace App\Controller\Admin;

use App\Entity\Comercio;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DatetimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ComercioCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comercio::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Comercio')
            ->setEntityLabelInPlural('Comercios')
            ->setSearchFields(['id', 'cuitComercio', 'nombreComercio', 'emailComercio', 'direccionComercio']);
    }

    public function configureFields(string $pageName): iterable
    {

        $id = IntegerField::new('id', 'ID');
        $nombre = TextField::new('nombreComercio')->setLabel('Nombre');
        $cuit = IntegerField::new('cuitComercio')->setLabel('CUIT');
        $email = TextField::new('emailComercio')->setLabel('Correo electrónico');
        $descripcion = TextField::new('descripcionComercio')->setLabel('Descripción');
        $hsap = TimeField::new('horaAperturaComercio')->setLabel('Hora apertura');
        $hscrr =TimeField::new('horaCierreComercio')->setLabel('Hora cierre');
        $fechaRegistro =TimeField::new('fechaRegistroComercio')->setLabel('Fecha de registro');
        $latitud = NumberField::new('latitudComercio')->setLabel('Latitud');
        $longitud = NumberField::new('longitudComercio')->setLabel('Longitud');
        $url = TextField::new('url')->setLabel('Url o sitio web');
        $telefono = TextField::new('telefonoComercio')->setLabel('Telefono');
        $direccion = TextField::new('direccionComercio')->setLabel('Dirección');
        $localidad = AssociationField::new('localidad')->setLabel('Localidad')
              ->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder
                    ->innerJoin('entity.provincia','p')
                        ->innerJoin('p.pais','c')
                        ->where('entity.nombre like :resi')
                        ->setParameter('resi','RESISTENCIA')
                        ->orderBy('c.nombre', 'ASC')
                        ->addOrderBy('p.nombre', 'ASC')
                        ->addOrderBy('entity.nombre', 'ASC')        
                        
            ; 
              });
        $estado = ChoiceField::new('estadoComercio')
              ->setChoices(['PENDIENTE' => 'PENDIENTE', 'ACTIVO' => 'ACTIVO', 'BAJA' => 'BAJA'])->setLabel('Estado');
        $motivo = TextField::new('motivoRechazo')->setLabel('Motivo de rechazo');
        $image = TextAreaField::new('imageFile')
              ->setFormType(VichImageType::class)
              ->setLabel('Imagen')           
              ->setFormTypeOption('allow_delete',true);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$cuit, $nombre, $direccion, $localidad, $estado];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$nombre, $cuit, $email , $descripcion, $hsap ,$hscrr,$fechaRegistro ,$latitud ,$longitud ,$url ,$telefono ,$direccion ,$localidad ,$estado,$motivo ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$estado, $nombre, $cuit, $email , $descripcion, $hsap ,$hscrr ,$latitud ,$longitud ,$url ,$telefono ,$direccion ,$localidad, $image,$motivo  ];
        }
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }


}
