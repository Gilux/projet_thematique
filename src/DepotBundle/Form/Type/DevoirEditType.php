<?php

namespace DepotBundle\Form\Type;

use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe_Devoir;
use DepotBundle\Entity\UE;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevoirEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ues = $options['ues'];
        $nb_max_etudiant = $options['gd']["nb_max_etudiant"];
        $nb_min_etudiant = $options['gd']["nb_min_etudiant"];
        $if_groupe = $options['gd']["if_groupe"];
        $date_bloquante = $options['gd']["date_bloquante"];

        $builder
            // Concerne un devoir
            ->add('titre', TextType::class, array("label" => "Nom", "required" => true))
            ->add('extensions', null, array("label" => "Choisir les extensions authorisées : ", "required" => true))
            ->add('UE', EntityType::class, array(
                "class" => UE::class,
                "choices" => $ues,
                "label" => "UE : "
            ))
            ->add('intitule', TextareaType::class)
            ->add('fichier', FileType::class, array("label" => "Votre fichier (.zip) : ", "required" => false))

            // Concerne les groupes_devoir
            ->add('if_groupes', CheckboxType::class, array(
                "label" => "Ce devoir est à rendre par groupes",
                "mapped" => false,
                "required" => false,
                "data" => $if_groupe
            ))
            ->add('nb_min_etudiant', IntegerType::class, array("label" => "Minimum", "mapped" => false, "attr" => array("min"=>1), "data" => $nb_min_etudiant))
            ->add('nb_max_etudiant', IntegerType::class, array("label" => "Maximum","mapped" => false, "data" => $nb_max_etudiant))
            ->add('date_bloquante', CheckboxType::class, array(
                "label" => "Les dates de rendus sont-elles bloquantes ?",
                "mapped" => false,
                "required" => false,
                "data" => $date_bloquante
            ))
            ->add('conserve_file', CheckboxType::class, array(
                "label" => "Conserver le fichier",
                "mapped" => false,
                "required" => false,
                "data" => true
            ))


            ->add('save', SubmitType::class, array("label" => "Sauvegarder"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Devoir::class
        ));

        $resolver->setRequired('ues');
        $resolver->setRequired('gd');
    }

}