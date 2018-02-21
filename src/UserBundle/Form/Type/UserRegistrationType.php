<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', TextType::class, array("label" => "Prénom"))
        ->add('last_name', TextType::class, array("label" => "Nom"))
        ->add('email', EmailType::class, array("label" => "E-mail"))
        ->add('groupes', ChoiceType::class, array(
            'label' => "UE",
            'multiple' => true,
            'choices' => array(
                'M2.45 - ASI' => '1',
                'M2.34 - Green IT' => '2',
                'M2.6 - Projet thématique' => '3'
            )
        ))
        ->add('submit', SubmitType::class, array("label" => "Valider"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User'
        ));
    }
}