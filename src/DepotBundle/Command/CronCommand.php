<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DepotBundle\Command;

use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\Groupe_projet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Description of CronUtility
 *
 * @author vincent
 */
class CronCommand extends ContainerAwareCommand {
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('depot:rappel:devoir')

            // the short description shown while running "php bin/console list"
            ->setDescription('Envoie une notification et un email aux étudiants n\'ayant pas rendu leurs devoirs.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Envoie une notification et un email aux étudiants n\'ayant pas rendu leurs devoirs.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $groupes_devoirs = $doctrine->getRepository(\DepotBundle\Entity\Groupe_Devoir::class)->findAll();

        $now = new \DateTime('now');
        $now->setTime("00", "00", "00");
        $now->add(new \DateInterval('P1D'));

        foreach ($groupes_devoirs as $gd) {
            if($gd->getDateARendre() == $now)
            {
                foreach ($gd->getGroupe()->getUsers() as $user) {
                    $groupes_projets = $doctrine->getRepository(Groupe_projet::class)->findByDevoirAndUser($gd->getDevoir(), $user);

                    if($groupes_projets != false)
                    {
                        if($groupes_projets->getFichier() == NULL && $groupes_projets->getFilename() == NULL)
                        {
                            $message = (new \Swift_Message('[MIAGE] La date de rendue d\'un devoir  concernant : ' . $gd->getDevoir()->getUe()->getNom() . ' / ' . $gd->getGroupe()->getName() . ' approche !'))
                                ->setFrom([$this->getContainer()->getParameter('mailer_user') => 'Dépôt de devoirs'])
                                ->setTo($user->getEmail())
                                ->setBody(
                                    $this->getContainer()->get('templating')->render(
                                        'Emails/devoir_a_rendre.html.twig',
                                        array(
                                            'first_name' => $user->getFirstName(),
                                            'last_name' => $user->getLastName(),
                                            'groupe' => $gd->getGroupe()->getName(),
                                            'ue' => $gd->getDevoir()->getUe()->getNom(),
                                            'devoir' => [
                                                'id' => $gd->getDevoir()->getId(),
                                                'titre' => $gd->getDevoir()->getTitre(),
                                                'date_a_rendre' => $gd->getDateARendre(),
                                            ]
                                        )
                                    ),
                                    'text/html'
                                );
                            $this->getContainer()->get('mailer')->send($message);

                            //notifications
                            $manager = $this->getContainer()->get('mgilet.notification');
                            $notif = $manager->createNotification('Un devoir un rendre');
                            $notif->setMessage($gd->getDevoir()->getUe()->getNom() . ' / ' . $gd->getGroupe()->getName() . ' : ' . $gd->getDevoir()->getTitre() . '');
                            $notif->setLink('http://symfony.com/');
                            $manager->addNotification(array($user), $notif, true);
                        }
                    }
                    else
                    {
                        $message = (new \Swift_Message('[MIAGE] La date de rendue d\'un devoir  concernant : ' . $gd->getDevoir()->getUe()->getNom() . ' / ' . $gd->getGroupe()->getName() . ' approche !'))
                            ->setFrom([$this->getContainer()->getParameter('mailer_user') => 'Dépôt de devoirs'])
                            ->setTo($user->getEmail())
                            ->setBody(
                                $this->getContainer()->get('templating')->render(
                                    'Emails/devoir_a_rendre.html.twig',
                                    array(
                                        'first_name' => $user->getFirstName(),
                                        'last_name' => $user->getLastName(),
                                        'groupe' => $gd->getGroupe()->getName(),
                                        'ue' => $gd->getDevoir()->getUe()->getNom(),
                                        'devoir' => [
                                            'id' => $gd->getDevoir()->getId(),
                                            'titre' => $gd->getDevoir()->getTitre(),
                                            'date_a_rendre' => $gd->getDateARendre(),
                                        ],
                                        "appartient_groupe" => true
                                    )
                                ),
                                'text/html'
                            );
                        $this->getContainer()->get('mailer')->send($message);

                        //notifications
                        $manager = $this->getContainer()->get('mgilet.notification');
                        $notif = $manager->createNotification('Un devoir un rendre');
                        $notif->setMessage($gd->getDevoir()->getUe()->getNom() . ' / ' . $gd->getGroupe()->getName() . ' : ' . $gd->getDevoir()->getTitre() . '');
                        $notif->setLink('http://symfony.com/');
                        $manager->addNotification(array($user), $notif, true);
                    }
                }
            }
        }
    }
}
