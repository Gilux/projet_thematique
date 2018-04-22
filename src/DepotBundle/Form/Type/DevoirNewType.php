<?php

namespace DepotBundle\Form\Type;

use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\UE;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevoirNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ues = $options['ues'];

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
            ->add('fichiers', FileType::class, array("label" => "Documents (.pdf, .zip, .doc, .docx) : ", "multiple" => true, "mapped" => false, "required" => false))

            // Concerne les groupes_devoir
            ->add('if_groupes', CheckboxType::class, array("label" => "Ce devoir est à rendre par groupes", "mapped" => false, "required" => false, "data" => true))
            ->add('nb_min_etudiant', IntegerType::class, array("label" => "Minimum", "mapped" => false))
            ->add('nb_max_etudiant', IntegerType::class, array("label" => "Maximum","mapped" => false))
            ->add('date_bloquante', CheckboxType::class, array("label" => "Les dates de rendus sont-elles bloquantes ?", "mapped" => false, "required" => false))



            ->add('save', SubmitType::class, array("label" => "Sauvegarder"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Devoir::class
        ));

        $resolver->setRequired('ues');
    }

}