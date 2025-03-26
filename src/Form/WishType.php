<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Wish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class WishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Votre rêve',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Décrivez-le...',
                'required' => false,
            ])
            ->add('author', TextType::class, [
                'label' => 'Votre nom',
            ])
            ->add('isPublished', CheckboxType::class, [
                'required' => false,
                'label' => 'Voulez-vous publier ?',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie...',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image en format JPG ou PNG',
                    ])
                ],
        ])

        ;

        // Ajouter un écouteur d'événement pour gérer dynamiquement le champ de suppression d'image
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $wish = $event->getData();
            $form = $event->getForm();

            // Vérifier si c'est une modification et si une image existe
            if ($wish && $wish->getId() !== null && $wish->getImageName()) {
                $form->add('deleteImage', CheckboxType::class, [
                    'label' => 'Supprimer l\'image',
                    'required' => false,
                    'mapped' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wish::class,
        ]);
    }
}
