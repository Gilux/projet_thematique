<?php

namespace UserBundle\Form\Type;

use DepotBundle\DepotBundle;
use FOS\UserBundle\Model\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use DepotBundle\Entity\Groupe;

class UserRegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['entity_manager'];
        $groupes = $entityManager->getRepository('DepotBundle:Groupe')->findAll();
        $choice_groupes = [];
        foreach ($groupes as $groupe) {
            $choice_groupes[$groupe->getName()] = $groupe->getId();
        }
        $builder->add('first_name', TextType::class, array("label" => "Prénom"))
            ->add('last_name', TextType::class, array("label" => "Nom"))
            ->add('email', EmailType::class, array("label" => "E-mail"))
            ->add('groupes', ChoiceType::class, array(
                'label' => "Groupe",
                'multiple' => true,
                'choices' => $choice_groupes
            ))
            ->add('submit', SubmitType::class, array("label" => "Valider"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User'
        ));
        $resolver->setRequired('entity_manager');

    }
}