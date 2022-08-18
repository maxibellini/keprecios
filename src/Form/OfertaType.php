<?php

namespace App\Form;

use App\Entity\Producto;
use App\Entity\User;
use App\Entity\Oferta;
use App\Entity\Comercio;
use App\Form\ProductoType;
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

class OfertaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('monto' , NumberType::class )
            ->add('descripcionOferta' , TextType::class )
            ->add('tipoDescuento' , ChoiceType::class, [
                        'choices'  => [
                            'Unitario' => 'Unitario',
                            'Compra múltiple' => 'Compra múltiple'
                        ],
             ])
            ->add('stock' , ChoiceType::class, [
                        'choices'  => [
                            'Alto' => 'Alto',
                            'Medio' => 'Medio',
                            'Bajo' => 'Bajo',
                            'Sin stock' => 'Sin stock'
                        ],
             ])
            ->add('comercio', EntityType::class, [
                'class' => Comercio::class,
            ])

            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'required' => false,
            ])
            /*
            ->add('productonuevo', ProductoType::class, [
                'required' => false,
                'mapped' => false               
            ])
            */
            ->add('motivoBaja' , TextType::class, [
                'required' => false,
            ])
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Oferta::class,
        ]);
    }
}
