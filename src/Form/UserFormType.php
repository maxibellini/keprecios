<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Localidad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name' , TextType::class )
            ->add('email' , EmailType::class)  
            ->add('nombrePersona' , TextType::class )
            ->add('apellidoPersona' , TextType::class )
            ->add('sexo' , ChoiceType::class, [
                        'choices'  => [
                            'Masculino' => 'Masculino',
                            'Femenino' => 'Femenino',
                            'No binario' => 'No binario',
                        ],
             ])
            ->add('fechaNacimiento' ,BirthdayType::class)
            ->add('domicilio' , TextType::class ,array(
                        'required' => false
            ))
            ->add('localidadDomicilio', EntityType::class, [
                'class' => Localidad::class,
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('l')
                    ->innerJoin('l.provincia','p')
                        ->innerJoin('p.pais','c')
                        ->orderBy('c.nombre', 'ASC')
                        ->addOrderBy('p.nombre', 'ASC')
                        ->addOrderBy('l.nombre', 'ASC')        
                        
            ;
                }, 
               
            ])
            ->add('codigoPostal' , TextType::class ,array(
                        'required' => false
            ))
            ->add('telefono' , TextType::class ,array(
                        'required' => false
            ))
            ->add('telefonoAlternativo' , TextType::class ,array(
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
            'data_class' => User::class,
        ]);
    }
}
