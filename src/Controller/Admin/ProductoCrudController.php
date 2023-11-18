<?php

namespace App\Controller\Admin;

use App\Entity\Producto;
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

class ProductoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Producto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Producto')
            ->setEntityLabelInPlural('Productos')
            ->setSearchFields(['id', 'cuitProducto', 'nombreProducto', 'emailProducto', 'direccionProducto']);
    }

    public function configureFields(string $pageName): iterable
    {

        $id = IntegerField::new('id', 'ID');
        $gtin = TextField::new('gtin')->setLabel('Código de producto');
        $descripcion = TextAreaField::new('descripcionProducto')->setLabel('Descripción');
        $marca = TextField::new('marcaProducto')->setLabel('Marca');
        $netcontent = TextField::new('netContent')->setLabel('Contenido neto');
        $compania = TextField::new('companiaProducto')->setLabel('Comapañía');
        $categoria= ChoiceField::new('categoriaProducto')
              ->setChoices([ '' => '',
                            'Alimentos' => 'Alimentos',
                            'Bazar' => 'Bazar',
                            'Juguetería' => 'Juguetería',
                            'Farmacia' => 'Farmacia',
                            'Limpieza' => 'Limpieza',
                            'Librería' => 'Librería',
                            'Ropas' => 'Ropas',
                            'Otro' => 'Otro'])->setLabel('Categoría');
        $pais = AssociationField::new('pais')->setLabel('País');
        $image = TextAreaField::new('imageFile')
              ->setFormType(VichImageType::class)
              ->setLabel('Imagen')           
              ->setFormTypeOption('allow_delete',true);
        $imgurl = TextField::new('imgUrl')->setLabel('Url de imagen');
        $estado = BooleanField::new('estadoProducto', 'Estado');


        if (Crud::PAGE_INDEX === $pageName) {
            return [$gtin, $descripcion, $marca, $netcontent, $compania, $categoria,$estado];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $gtin, $descripcion, $marca, $netcontent, $compania, $categoria, $pais,$estado, $image, $imgurl  ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$id, $gtin, $descripcion, $marca, $netcontent, $compania, $categoria, $pais, $estado, $image, $imgurl   ];
        }
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }


}
