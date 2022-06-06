<?php

namespace App\Form;

use App\Entity\File;
use DateTime;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType as TypeFileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();
        $currentDate = new DateTime();

        $pathIsRequired = ($entity->getPath()) ? false : true; 

        $builder
            ->add('event_num', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Número de evento',
                    'class' => 'form-control',
                ]
            ])
            ->add('description', CKEditorType::class, [
                'config' => array('toolbar' => 'my_toolbar_1'),
                'label' => false,
                'attr' => [
                    'placeholder' => 'Descripción',
                    'class' => 'form-control',
                    'rows' => '5',
                ]
            ])
            ->add('path', TypeFileType::class, [
                'required' => $pathIsRequired,
                'label' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                "mapped" => false,
                "constraints" => [
                    new All([
                        "constraints" => [
                            new ConstraintsFile([
                                'maxSize' => '1548M',
                                'maxSizeMessage' => 'El tamaño máximo del archivo es 1.5GB',
                            ]),
                        ],
                    ])
                ]
            ])
//            ->add(ini_get('session.upload_progress.name'), HiddenType::class, [
//                'mapped' => false,
//                'attr' => [
//                    "value" => ""
//                ],
//            ])
            ->add('set_expiration_date', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                
                'label' => "Fecha de expiración",
                'choices' => [
                    'Por defecto (5 días)' => false,
                    'Escoger' => true,
                ],
                'expanded' => true,
                'data' => false,
            ])
            ->add('expiration_date', DateType::class, [
                'required' => false,
                'label' => false,
                'widget' => 'single_text',
                'attr'   => [
                    'min' => ( $currentDate->modify("+1 days") )->format('Y-m-d'),
                    'max' => ( $currentDate->modify("+1 month") )->format('Y-m-d'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
