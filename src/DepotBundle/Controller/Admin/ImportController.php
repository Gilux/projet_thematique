<?php

namespace DepotBundle\Controller\Admin;

use DepotBundle\Entity\Groupe;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DepotBundle\Entity\UE;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class ImportController extends Controller
{
    public function importAction(Request $request){

        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('Valider', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        
        set_time_limit (0);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = file_get_contents($form['fichier']->getData());
            $json_a = json_decode($file, true);

            $tab_users = array();
            foreach ($json_a["UES"] as $ue)
            {
                if(isset($ue["groupes"])) {
                    foreach ($ue["groupes"] as $groupe) {
                        if(isset($groupe["etudiants"])) {
                            foreach ($groupe["etudiants"] as $etudiant) {
                                if(!array_key_exists($etudiant["email"], $tab_users)) {
                                    $oEtudiant = new User();
                                    $oEtudiant->setUsername($etudiant["email"]);
                                    $oEtudiant->setUsernameCanonical($etudiant["email"]);
                                    $oEtudiant->setEmail($etudiant["email"]);
                                    $oEtudiant->setEmailCanonical($etudiant["email"]);
                                    $oEtudiant->setEnabled(true);
                                    $oEtudiant->setRoles(['ROLE_ETUDIANT']);
                                    $oEtudiant->setFirstName($etudiant["first_name"]);
                                    $oEtudiant->setLastName($etudiant["last_name"]);

                                    $tab_users[$etudiant["email"]] = $oEtudiant;
                                }
                            }
                        }
                    }
                }
                if(isset($ue["profs"])) {
                    foreach ($ue["profs"] as $prof) {
                        if(!array_key_exists($prof["email"], $tab_users)) {
                            $oProf = new User();
                            $oProf->setUsername($prof["email"]);
                            $oProf->setUsernameCanonical($prof["email"]);
                            $oProf->setEmail($prof["email"]);
                            $oProf->setEmailCanonical($prof["email"]);
                            $oProf->setEnabled(true);
                            $oProf->setRoles(['ROLE_ENSEIGNANT']);
                            $oProf->setFirstName($prof["first_name"]);
                            $oProf->setLastName($prof["last_name"]);

                            $tab_users[$prof["email"]] = $oProf;
                        }
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            foreach ($tab_users as $tab_user) {
                $checkUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $tab_user->getEmail()]);
                if(count($checkUser) == 0) {

                    $randomFirstPassword = uniqid();
                    $tab_user->setPlainPassword($randomFirstPassword);

                    $em->persist($tab_user);
                    $em->flush();
                    $message = (new \Swift_Message('[MIAGE] Vos identifiants du dépôt de devoirs en ligne'))
                        ->setFrom([$this->getParameter('mailer_user') => 'Dépôt de devoirs'])
                        ->setTo($tab_user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'Emails/registration.html.twig',
                                array('first_name' => $tab_user->getFirstName(), 'last_name' => $tab_user->getLastName(), 'credentials' => [
                                    'login' => $tab_user->getEmail(),
                                    'first_password' => $randomFirstPassword
                                ])
                            ),
                            'text/html'
                        );
                    //$this->get('mailer')->send($message);
                }
                else{
                    $tab_users[$checkUser->getEmail()] = $checkUser;
                }
            }

            foreach ($json_a["UES"] as $ue)
            {
                $checkUE = $this->getDoctrine()->getRepository(UE::class)->findOneBy(["code" => $ue["code"]]);
                $flag = true;
                
                if(count($checkUE) != 0) {
                    $ifDevoir = $this->getDoctrine()->getRepository(\DepotBundle\Entity\Devoir::class)->findBy(["UE" => $checkUE]);
                    if(count($ifDevoir) != 0)
                    {
                        $flag = false;
                        $this->addFlash("error", "Impossible de modifier l'ue ".$checkUE->getCode()." car un devoir est lié à cette UE. Cette erreur n'est pas bloquante, les autres UE ont pu être modifiée.");
                    }
                    else
                    {
                        $groupes = $this->getDoctrine()->getRepository(Groupe::class)->findBy(["UE" => $checkUE]);
                        foreach ($groupes as $groupe)
                        {
                            $em->remove($groupe);
                            $em->flush();
                        }
                        
                        $em->remove($checkUE);
                        $em->flush();
                    }
                }
                
                if($flag)
                {
                    $oUE = new UE();
                    $oUE->setNom($ue["nom"]);
                    $oUE->setCode($ue["code"]);
                    if(isset($ue["groupes"])) {
                        foreach ($ue["groupes"] as $groupe) {
                            $oGroupe = new Groupe();
                            $oGroupe->setName($groupe["name"]);
                            $oGroupe->setUE($oUE);
                            if(isset($groupe["etudiants"])) {
                                foreach ($groupe["etudiants"] as $etudiant) {
                                    $oGroupe->addUser($tab_users[$etudiant["email"]]->addGroupe($oGroupe));
                                    $em->persist($tab_users[$etudiant["email"]]);
                                }
                            }
                            $em->flush();
                            $oUE->addGroupe($oGroupe);
                        }
                    }
                    if(isset($ue["profs"])) {
                        foreach ($ue["profs"] as $prof) {
                            $oUE->addUser($tab_users[$prof["email"]]->addUE($oUE));
                            $em->persist($tab_users[$prof["email"]]);
                        }
                    }

                    $em->persist($oUE);
                    $em->flush();
                }
            }

            $this->addFlash("success", "L'import des données est terminé.");
        }


        return $this->render('DepotBundle:Admin/Import:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
