<?php

namespace App\Controller\Admin;

use App\Entity\Voucher;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DatetimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class VoucherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Voucher::class;
    }

  
    public function configureFields(string $pageName): iterable
    {

           $nombre = TextField::new('nombre');
           $descripcion = TextEditorField::new('descripcion');
           $image = TextAreaField::new('imageFile')
              ->setFormType(VichImageType::class)
              ->setLabel('Imagen')
              ->onlyOnForms()           
              ->setFormTypeOption('allow_delete',true);
           $fechaCreacion = DatetimeField::new('fechaCreacion');
           $costo= IntegerField::new('costo');
           $cantidad = IntegerField::new('duracion','Cantidad');
           $responsable = AssociationField::new('responsable');
           $entidad = TextField::new('entidad');
           $estado = ChoiceField::new('estado')
              ->setChoices(['ACTIVO' => 'ACTIVO', 'INACTIVO' => 'INACTIVO'])->setLabel('Estado');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$nombre, $descripcion, $fechaCreacion,$entidad, $costo ,$cantidad, $responsable, $estado];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$nombre, $descripcion, $image,$entidad, $costo ,$cantidad, $responsable, $estado ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$nombre, $descripcion, $image,$entidad, $costo ,$cantidad, $responsable, $estado  ];
        }
    }
    
}
