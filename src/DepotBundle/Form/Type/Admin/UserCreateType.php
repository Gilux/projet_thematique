<?php

namespace DepotBundle\Form\Type\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name',      TextType::class, array("label" => "Prénom"))
            ->add('last_name',     TextType::class, array("label" => "Nom"))
            ->add('email',   EmailType::class, array("label" => "E-mail"))
            ->add('roles', ChoiceType::class, array(
                'label' => "Rôles",
                'multiple' => true,
                'choices' => array(
                    'Administrateur' => 'ROLE_ADMIN',
                    'Enseignant' => 'ROLE_ENSEIGNANT',
                    'Étudiant' => 'ROLE_ETUDIANT'
                )
            ))
            ->add('enabled',    CheckboxType::class, array("label" => "Compte activé", "required" => false ));

        $builder->add('ues', EntityType::class, array(
            'class' => 'DepotBundle:UE',
            'label' => 'Vos UEs : ',
            'required' => false,
            'choice_label' => function ($category) {
                return $category->getCode() . ' ' . $category->getNom();
            },
            'multiple' => true
        ));

        $builder->add('save',      SubmitType::class, array("label" => "Sauvegarder"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User'
        ));
    }
}
