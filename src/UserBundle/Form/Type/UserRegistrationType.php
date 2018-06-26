<?php

namespace UserBundle\Form\Type;

use DepotBundle\DepotBundle;
use DepotBundle\Entity\UE;
use FOS\UserBundle\Model\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use DepotBundle\Entity\Groupe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserRegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', TextType::class, array("label" => "Prénom"))
            ->add('last_name', TextType::class, array("label" => "Nom"))
            ->add('email', EmailType::class, array("label" => "E-mail"))
            ->add('compte', ChoiceType::class, array(
                'choices' => array(
                    'Enseignant' => 'ROLE_ENSEIGNANT',
                    'Étudiant' => 'ROLE_ETUDIANT'
                ),
                'label' => 'Vous faites un demande pour un compte : ',
                'mapped' => false
            ))
            ->add('ues', EntityType::class, array(
                'class' => 'DepotBundle:UE',
                'choice_label' => function ($category) {
                    return $category->getCode() . ' ' . $category->getNom();
                },
                'multiple' => true
            ))
            ->add('submit', SubmitType::class, array("label" => "Valider"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User',
        ));

    }
}