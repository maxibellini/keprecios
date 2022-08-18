<?php

namespace App\Controller\Admin;

use App\Entity\Oferta;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DatetimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OfertaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Oferta::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Oferta')
            ->setEntityLabelInPlural('Ofertas')
            ->setSearchFields(['id', 'cuitProducto', 'nombreProducto', 'emailProducto', 'direccionProducto']);
    }

    public function configureFields(string $pageName): iterable
    {

        $id = IntegerField::new('id', 'ID');
        $monto = NumberField::new('monto')->setLabel('Monto');
        $descripcionOferta = TextField::new('descripcionOferta')->setLabel('Descripción');
        $tipoDescuento = ChoiceField::new('tipoDescuento')
              ->setChoices(['Unitario' => 'Unitario',
                            'Compra múltiple' => 'Compra múltiple'])->setLabel('Tipo de descuento');
        $stock = ChoiceField::new('stock')
              ->setChoices(['Alto' => 'Alto',
                            'Medio' => 'Medio',
                            'Bajo' => 'Bajo',
                            'Sin stock' => 'Sin stock'])->setLabel('Stock'); 
        $comercio = AssociationField::new('comercio')->setLabel('Comercio');
        $producto = AssociationField::new('producto')->setLabel('Producto');
        $user = AssociationField::new('user')->setLabel('User');
        $fechaCarga = DateTimeField::new('fechaCarga')
               ->setLabel('Fecha de carga')
               ->setFormat('y-MM-dd')->renderAsText();
        $fechaUpdate = DateTimeField::new('fechaUpdate')
               ->setLabel('Fecha de modificación')
               ->setFormat('y-MM-dd')->renderAsText();
        $motivoBaja = TextField::new('motivoBaja')->setLabel('Motivo de baja');
        $estado = BooleanField::new('estado', 'Estado');      
        if (Crud::PAGE_INDEX === $pageName) {
            return [$comercio,  $producto , $monto,  $descripcionOferta, $user ];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [ $monto,  $descripcionOferta, $tipoDescuento,  $stock,  $comercio, $producto , $user , $fechaCarga,   $fechaUpdate, $motivoBaja  ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$monto,  $descripcionOferta, $tipoDescuento,  $stock,  $comercio, $producto , $user ,$motivoBaja   ];
        }
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }


}
