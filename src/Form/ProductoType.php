<?php

namespace App\Form;

use App\Entity\Producto;
use App\Entity\User;
use App\Entity\Pais;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gtin' , TextType::class )
            ->add('marcaProducto' , TextType::class )
            ->add('descripcionProducto' , TextareaType::class )
            ->add('categoriaProducto' , ChoiceType::class, [
                        'choices'  => [
                            '' => '',
                            'Alimentos' => 'Alimentos',
                            'Bazar' => 'Bazar',
                            'Juguetería' => 'Juguetería',
                            'Farmacia' => 'Farmacia',
                            'Limpieza' => 'Limpieza',
                            'Librería' => 'Librería',
                            'Ropas' => 'Ropas',
                            'Otro' => 'Otro',
                        ],
             ])
            ->add('netContent' , TextType::class,array(
                        
            ) )
            ->add('companiaProducto' , TextType::class,array(
                        'required' => false
            ) )
            ->add('pais', EntityType::class, [
                'class' => Pais::class,
            ])
            ->add('imgUrl' , TextType::class ,array(
                        'required' => false
            ))
            ->add('imageFile', VichImageType::class, array(
                'label' => 'Imagen (jpeg/png)',
                'data_class' => null,
                'required' => false,
                'empty_data' => '',
                'allow_delete' => true,
                'download_link' => true, // not mandatory, default is true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
