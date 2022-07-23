<?php

namespace App\Form;

use App\Entity\Comercio;
use App\Entity\User;
use App\Entity\Localidad;
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

class ComercioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cuitComercio' , IntegerType::class )
            ->add('nombreComercio' , TextType::class )
            ->add('descripcionComercio' , TextareaType::class )
            ->add('direccionComercio' , TextType::class )
            ->add('sucursal' , CheckboxType::class,array(
                        'required' => false
            ) )
            ->add('emailComercio' , EmailType::class )
            ->add('horaAperturaComercio' , TimeType::class,array(
                        'required' => false
            ) )
            ->add('horaCierreComercio' , TimeType::class,array(
                        'required' => false
            ) )
            ->add('latitudComercio' , NumberType::class)
            ->add('longitudComercio' , NumberType::class)
            ->add('url' , TextType::class,array(
                        'required' => false
            ) )
            ->add('telefonoComercio' , TextType::class )
            ->add('localidad', EntityType::class, [
                'class' => Localidad::class,
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('l')
                    ->innerJoin('l.provincia','p')
                        ->innerJoin('p.pais','c')
                        ->where('l.nombre like :resi')
                        ->setParameter('resi','RESISTENCIA')
                        ->orderBy('c.nombre', 'ASC')
                        ->addOrderBy('p.nombre', 'ASC')
                        ->addOrderBy('l.nombre', 'ASC')        
                        
            ;
                }, 
               
            ])
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
            'data_class' => Comercio::class,
        ]);
    }
}
