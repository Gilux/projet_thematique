<?php

namespace DepotBundle\Controller;

use DepotBundle\Entity\Commentaire;
use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\Groupe_Devoir;
use DepotBundle\Entity\Groupe_projet;
use DepotBundle\Entity\UserGroupeProjet;
use Mgilet\NotificationBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use UserBundle\Entity\User;


class DevoirController extends Controller
{
    public function showAction(Devoir $devoir)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')) {
            return $this->forward('DepotBundle:Devoir:showEtudiant', ["devoir" => $devoir]);
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            return $this->forward('DepotBundle:Devoir:showEnseignant', ["devoir" => $devoir]);
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function showEtudiantAction(Devoir $devoir)
    {
        $user = $this->getUser();
        $groupes_devoirs = $this->getDoctrine()->getRepository("DepotBundle:Groupe_Devoir")->findBy([
            "devoir" => $devoir,
        ]);

        $userDansGroupe = false;
        $groupeDevoirUser = null;

        // Tourner parmi les groupes de l'ue concernée par le devoir
        foreach ($groupes_devoirs as $gd) {

            $g = $gd->getGroupe();
            foreach ($g->getUsers() as $u) {
                if (!$userDansGroupe) {
                    if ($u->getId() == $user->getId()) {
                        $userDansGroupe = true;
                        $groupeDevoirUser = $gd;
                        $groupe = $g;
                    }
                }
            }
        }

        $groupes_projet = $this->getDoctrine()->getRepository("DepotBundle:Groupe_projet")->findByDevoir($devoir);
        $usersInGroupeDevoir = $groupeDevoirUser->getGroupe()->getUsers()->count();
        $groupeRenduUtility = $this->get("utility.grouperendu");
        $minmax_groups = $groupeRenduUtility->getMinMaxGroups(
            $groupeDevoirUser->getNbMinEtudiant(),
            $groupeDevoirUser->getNbMaxEtudiant(),
            $usersInGroupeDevoir
        );

        //Algo permettant de savoir si l'utilisateur appartient déjà à un groupe
        //fixme remplacer lalgo par le findByDevoirAnduser groupe projet
        $uAppartientGroupe = false;
        $ogroupes_projets = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["devoir" => $devoir]);
        foreach ($ogroupes_projets as $ogroupes_projet) {
            $ousers_groupes_projets = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $ogroupes_projet]);
            foreach ($ousers_groupes_projets as $ousers_groupes_projet) {
                if ($this->getUser()->getId() == $ousers_groupes_projet->getUser()->getId()) {
                    $uAppartientGroupe = true;
                    $u_groupe_projet = $ogroupes_projet;
                }
            }
        }

        //Récupère la date de Rendu et le fichier
        $gp = $this->getDoctrine()->getRepository(Groupe_projet::class)->findByDevoirAndUser($devoir, $user);
        return $this->render('DepotBundle:Devoir:showEtudiant.html.twig', [
            "devoir" => $devoir,
            "user" => $this->getUser(),
            "groupe_devoir" => $groupeDevoirUser,
            "minmax_groups" => $minmax_groups,
            "groupes_projet" => $groupes_projet,
            "u_groupe_projet" => isset($u_groupe_projet) ? $u_groupe_projet : false,
            "u_appartient_groupe" => $uAppartientGroupe,
            "date_rendu" => $gp ? $gp->getDate() : false,
            "fichier_rendu" => $gp ? $gp->getFilename() : false,
            "groupe" => $groupe,
        ]);
    }

    public function showEnseignantAction(Devoir $devoir)
    {
        $groupes_projet = $this->getDoctrine()->getRepository("DepotBundle:Groupe_projet")->findByDevoir($devoir);

        foreach ($groupes_projet as $key => $gp) {
            $date_theorique = $gp->getGroupeDevoir()->getDateARendre();

            $groupes_projet[$key]->date_theorique = $date_theorique;

            if ($gp->getDate()) {
                $groupes_projet[$key]->diff = date_diff($date_theorique, $gp->getDate());
            }
        }

        return $this->render('DepotBundle:Devoir:showEnseignant.html.twig', [
            "devoir" => $devoir,
            "groupes_projets" => $groupes_projet,
        ]);
    }

    public function sendNotification(User $user, Groupe_Devoir $groupeDevoir)
    {
        $ueName = $groupeDevoir->getGroupe()->getUE();
        $groupeName = $groupeDevoir->getGroupe()->getName();
        try {
            $message = (new \Swift_Message('[MIAGE] Vous avez un nouveau devoir concernant : ' . $ueName . '/' . $groupeName . ''))
                ->setFrom([$this->getParameter('mailer_user') => 'Dépôt de devoirs'])
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'Emails/nouveau_devoir.html.twig',
                        array(
                            'first_name' => $user->getFirstName(),
                            'last_name' => $user->getLastName(),
                            'groupe' => $groupeName,
                            'ue' => $ueName,
                            'devoir' => [
                                'id' => $groupeDevoir->getDevoir()->getId(),
                                'titre' => $groupeDevoir->getDevoir()->getTitre(),
                                'date_a_rendre' => $groupeDevoir->getDateARendre(),
                            ]
                        )
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);
        } catch (\Exception $e) {
            $this->addFlash("error", "Une erreur est survenue.");
        }

        //notifications
        $manager = $this->get('mgilet.notification');
        $notif = $manager->createNotification('Nouveau devoir');
        $notif->setMessage($ueName . '/' . $groupeName . ' : ' . $groupeDevoir->getDevoir()->getTitre() . '');
        $notif->setLink('http://symfony.com/');
        $manager->addNotification(array($user), $notif, true);


    }

    public function newAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());

            $devoir = new Devoir();
            $form = $this->createForm('DepotBundle\Form\Type\DevoirNewType', $devoir, array('ues' => $user->getUes()));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                // Récupération des données concernant les groupes
                $data = $request->request->get('groupes');

                // Ajouter le créateur et la date de création
                $devoir->setUser($user);
                $devoir->setCreated(new \DateTime("now"));


                // Persister l'objet Devoir
                $em = $this->getDoctrine()->getManager();
                $em->persist($devoir);
                $em->flush();


                $groupes = [];

                for ($i = 0; $i < count($data); $i++) {
                    // Si le groupe est coché
                    if (isset($data[$i]['id'])) {
                        // Récupération de l'identifiant du groupe
                        $id = key($data[$i]['id']);
                        // Récupération de la date de rendu saisie
                        $date_rendu = new \DateTime($data[$i]["date"]);

                        // Hydratation de l'objet Devoir
                        $groupe_devoir = new Groupe_Devoir();
                        $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findById($id);
                        $groupes[] = $groupe;
                        $groupe_devoir->setGroupe($groupe[0]);
                        $groupe_devoir->setDevoir($devoir);
                        $groupe_devoir->setDateARendre($date_rendu);
                        $groupe_devoir->setDateBloquante($form->get("date_bloquante")->getData());
                        $groupe_devoir->setNbMaxEtudiant($form->get("nb_max_etudiant")->getData());
                        $groupe_devoir->setNbMinEtudiant($form->get("nb_min_etudiant")->getData());
                        $em->persist($groupe_devoir);

                        //Si le devoir est individuel
                        if ($form->get("nb_max_etudiant")->getData() == 1 && $form->get("nb_min_etudiant")->getData() == 1) {
                            //Créer les groupe_projets
                            foreach ($groupe[0]->getUsers()->getValues() as $user) {
                                $groupe_projet = new Groupe_projet();
                                $groupe_projet->setDevoir($devoir);
                                $groupe_projet->setName($user->getLastName());
                                $groupe_projet->setGroupe($groupe[0]);
                                $groupe_projet->setGroupeDevoir($groupe_devoir);
                                $em->persist($groupe_projet);

                                $user_groupe_projet = new UserGroupeProjet();
                                $user_groupe_projet->setUser($user);
                                $user_groupe_projet->setGroupeProjet($groupe_projet);
                                $user_groupe_projet->setStatus(1);
                                $user_groupe_projet->setLeader(1);
                                $em->persist($user_groupe_projet);
                            }
                        }

                        //récupérer les données des utilisateurs de tous les groupes set une notification + envoyer un mail
                        foreach ($groupe[0]->getUsers()->getValues() as $user) {
                            $this->sendNotification($user, $groupe_devoir);
                        }

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($groupe_devoir);

                        $devoir->addGroupeDevoir($groupe_devoir);
                    }
                }


                // Si aucun groupe n'a été coché
                if (count($devoir->getGroupeDevoir()) == 0) {
                    $errors[] = "Aucun groupe n'a été coché";

                    return $this->render('DepotBundle:Devoir:new.html.twig', array(
                        'devoir' => $devoir,
                        'user' => $user,
                        'add_form' => $form->createView(),
                        'errors' => $errors
                    ));
                }

                if ($devoir->getFichier() != null) {
                    $f = new File($devoir->getFichier());
                    $fname = $this->generateUniqueFileName() . '.' . $f->guessExtension();
                    $f->move($this->getParameter("documents_devoirs_directory"), $fname);
                    $devoir->setFichier($fname);
                }

                // Insertion en base de données
                $em->flush();


                return $this->redirectToRoute('depot_homepage');
            } else if ($form->isSubmitted() && !$form->isValid()) {
                $errors = $this->getErrorMessages($form);

                return $this->render('DepotBundle:Devoir:new.html.twig', array(
                    'devoir' => $devoir,
                    'user' => $user,
                    'add_form' => $form->createView(),
                    'errors' => $errors
                ));
            }

            return $this->render('DepotBundle:Devoir:new.html.twig', array(
                'devoir' => $devoir,
                'user' => $user,
                'add_form' => $form->createView()
            ));
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function renduAction(Devoir $devoir, Groupe_projet $groupe_projet)
    {
        $fileName = $this->get('kernel')->getRootDir() . '/../web/uploads/rendus_' . date('dmYhis') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($fileName, \ZipArchive::CREATE) === true) {
            $users = $groupe_projet->getUsersGroupesProjets();
            foreach ($users as $u) {
                $noms[] = $u->getUser()->getLastName();
            }

            if (!is_null($groupe_projet->getFichier())) {
                $filepath = $this->getParameter("depots_devoirs_directory") . "/" . $groupe_projet->getFichier();
                $fileName = $this->get('kernel')->getRootDir() . '/../web/uploads/rendus_' . date('dmYhis') . '.zip';
                $filename = implode("_", $noms) . "." . pathinfo($filepath, PATHINFO_EXTENSION);
                if (file_exists($filepath)) {

                    $zip->addFile($filepath, $filename);
                } else {
                    die("fatal");
                }
            }
            $zip->close();

            $response = new BinaryFileResponse($fileName);

            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            return $response;

        } else {
            echo "Erreur";
            die();
        }
    }


    public
    function rendusAction(Devoir $devoir)
    {
        $fileName = $this->get('kernel')->getRootDir() . '/../web/uploads/rendus_' . date('dmYhis') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($fileName, \ZipArchive::CREATE) === true) {
            $rendus = $this->getDoctrine()->getRepository("DepotBundle:Groupe_projet")->findBy(["devoir" => $devoir]);

            foreach ($rendus as $rendu) {
                $noms = [];

                $users = $rendu->getUsersGroupesProjets();
                foreach ($users as $u) {
                    $noms[] = $u->getUser()->getLastName();
                }

                if (!is_null($rendu->getFichier())) {
                    $filepath = $this->getParameter("depots_devoirs_directory") . "/" . $rendu->getFichier();
                    $filename = implode("_", $noms) . "." . pathinfo($filepath, PATHINFO_EXTENSION);

                    if (file_exists($filepath)) {
                        $zip->addFile($filepath, $filename);
                    } else {
                        die("fatal");
                    }
                }
            }
            $zip->close();

            $response = new BinaryFileResponse($fileName);

            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            return $response;
        } else {
            echo "Erreur";
            die();
        }
    }

    /**
     * @return string
     */
    private
    function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    public
    function getGroupeAction(Request $request)
    {
        if ($request->request->get('ue_id')) {
            $groupes = $this->getDoctrine()->getRepository(Groupe::class)->findBy(["UE" => $request->request->get('ue_id')]);

            if ($request->request->get('groupes_checked')) {
                $temp_groupes = $request->request->get('groupes_checked');
            }

            $data = "";

            for ($i = 0; $i < count($groupes); $i++) {
                $checked = false;
                $date_a_rendre = false;
                if (isset($temp_groupes)) {
                    for ($j = 0; $j < count($temp_groupes); $j++) {
                        if ($temp_groupes[$j]["groupe"] == $groupes[$i]->getId()) {
                            $checked = true;
                            $date_a_rendre = $temp_groupes[$j]["date"];
                        }
                    }
                }

                $data .= $this->render('DepotBundle:Devoir:groupes.html.twig', array(
                    "id" => $groupes[$i]->getId(),
                    "name" => $groupes[$i]->getName(),
                    "it" => $i,
                    "checked" => $checked,
                    "date_a_rendre" => $date_a_rendre
                ))->getContent();
            }

            return new Response($data);
        }

        return $this->render('DepotBundle:Devoir:new.html.twig');
    }

    private
    function getErrorMessages(FormInterface $form)
    {
        $errors = array();

        //this part get global form errors (like csrf token error)
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        //this part get errors for form fields
        /** @var Form $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $options = $child->getConfig()->getOptions();
                //there can be more than one field error, that's why implode is here
                $errors[$options['label'] ? $options['label'] : ucwords($child->getName())] = implode('; ', $this->getErrorMessages($child));
            }
        }

        return $errors;
    }

    /**
     * @param Request $request
     * @param Devoir $devoir
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public
    function editAction(Request $request, Devoir $devoir)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());
            $temp_devoir = $devoir;
            $temp_groupes_devoirs = array();
            foreach ($devoir->getGroupeDevoir() as $gd) {
                array_push($temp_groupes_devoirs, $gd);
            }

            $gd = array();
            $groupes = array();
            foreach ($temp_groupes_devoirs as $groupe_devoir) {
                $gd['nb_max_etudiant'] = $groupe_devoir->getNbMaxEtudiant();
                $gd['nb_min_etudiant'] = $groupe_devoir->getNbMinEtudiant();
                $gd['if_groupe'] = true;
                $d = $groupe_devoir->getDateARendre();
                array_push($groupes, array("groupe" => $groupe_devoir->getGroupe(), "date_a_rendre" => $d->format('Y-m-d H:i:s')));
                if ($groupe_devoir->getNbMaxEtudiant() == 1 && $groupe_devoir->getNbMinEtudiant() == 1) {
                    $gd['if_groupe'] = false;
                }
                $gd["date_bloquante"] = $groupe_devoir->getDateBloquante();

                $devoir->addGroupeDevoir($groupe_devoir);
            }

            if ($devoir->getFichier() != null) {
                //Sauvegarder le nom du fichier
                $temp_filename = $devoir->getFichier();

                $devoir->setFichier(new File($this->getParameter("documents_devoirs_directory") . "/" . $devoir->getFichier()));
            }

            $deleteForm = $this->createDeleteForm($devoir);
            $editForm = $this->createForm('DepotBundle\Form\Type\DevoirEditType', $devoir, array('ues' => $user->getUEs(), 'gd' => $gd));
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                // Récupération des données concernant les groupes
                $data = $request->request->get('groupes');

                /********************************
                 * SI LE NOMBRE D'ETUDIANT CHANGE
                 * ALORS IL FAUT SUPPRIMER TOUS LES 
                 * GROUPES PROJETS et USERS GROUPES PROJETS
                 */
                if($editForm->get("nb_max_etudiant")->getData() != $gd['nb_max_etudiant'] || $editForm->get("nb_min_etudiant")->getData() || $gd['nb_min_etudiant']);
                {
                    foreach ($temp_groupes_devoirs as $tgd)
                    {
                        $gps = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["groupe" => $tgd->getGroupe()]);
                        foreach($gps as $gp)
                        {
                            $ugps = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $gp]);
                            foreach($ugps as $ugp)
                            {
                                $em = $this->getDoctrine()->getManager();
                                $em->remove($ugp);
                                $em->flush();
                            }

                            $em = $this->getDoctrine()->getManager();
                            $em->remove($gp);
                            $em->flush();
                        }
                        $tgd->setNbMaxEtudiant($editForm->get("nb_max_etudiant")->getData());
                        $tgd->setNbMinEtudiant($editForm->get("nb_min_etudiant")->getData());
                        $this->getDoctrine()->getManager()->flush();
                        
                        //Si le devoir est individuel
                        if ($editForm->get("nb_max_etudiant")->getData() == 1 && $editForm->get("nb_min_etudiant")->getData() == 1) {
                            $em = $this->getDoctrine()->getManager();
                            $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findById($tgd->getGroupe()->getId());
                            //Créer les groupe_projets
                            foreach ($groupe[0]->getUsers()->getValues() as $user) {
                                $groupe_projet = new Groupe_projet();
                                $groupe_projet->setDevoir($devoir);
                                $groupe_projet->setName($user->getLastName());
                                $groupe_projet->setGroupe($groupe[0]);
                                $groupe_projet->setGroupeDevoir($tgd);
                                $em->persist($groupe_projet);

                                $user_groupe_projet = new UserGroupeProjet();
                                $user_groupe_projet->setUser($user);
                                $user_groupe_projet->setGroupeProjet($groupe_projet);
                                $user_groupe_projet->setStatus(1);
                                $user_groupe_projet->setLeader(1);
                                $em->persist($user_groupe_projet);
                            }
                        }
                    }
                }
                /**********************
                 * FIN
                 */
                

                /************************
                 * SI LES DATES ONT ETE CHANGEES
                 * ALORS IL FAUT LES METTRE A JOURS
                 * 
                 * SI LES GROUPES DEVOIRS CHANGENT
                 * ALORS IL FAUT SOIT SUPPRIMER OU
                 * AJOUTER DES GROUPES PROJETS ET 
                 * USERS GROUPES PROJETS
                 */
                $flag = array();
                for ($i = 0; $i < count($data); $i++) {
                    // Si le groupe est coché
                    if (isset($data[$i]['id'])) {
                        // Récupération de l'identifiant du groupe
                        $id = key($data[$i]['id']);
                        // Récupération de la date de rendu saisie
                        $date_rendu = new \DateTime($data[$i]["date"]);
                        if(isset($temp_groupes_devoirs[$i]))
                        {
                            if($id == $temp_groupes_devoirs[$i]->getGroupe()->getId() && $date_rendu == $temp_groupes_devoirs[$i]->getDateARendre())
                            {
                                array_push($flag, true);
                            }
                            else
                            {
                                array_push($flag, false);
                            }
                        }
                        else
                        {
                            array_push($flag, false);
                        }
                    }
                    else
                    {
                        array_push($flag, false);
                    }
                }
                
                $f = true;
                for($i=0;$i<count($flag);$i++)
                {
                    if($flag[$i] == false)
                        $f = false;
                }
                //Gestion des groupes devoir
                if(!$f)
                {
                    // Si aucun groupe n'a été coché
                    if (count($devoir->getGroupeDevoir()) == 0) {
                        $errors["groupes_error"] = "Aucun groupe n'a été coché";

                        foreach ($devoir->getGroupeDevoir() as $gd) {
                            $devoir->removeGroupeDevoir($gd);
                        }
                        foreach ($temp_groupes_devoirs as $gd) {
                            $devoir->addGroupeDevoir($gd);
                        }

                        return $this->redirectToRoute('edit_devoir', array("id" => $devoir->getId()));
                    }
                    
                    $temp_g_id_actu = array();
                    $dates_rendus = array(); //Utilisé seulement en cas d'ajout;
                    for ($i = 0; $i < count($data); $i++) {
                        // Si le groupe est coché
                        if (isset($data[$i]['id'])) {
                            array_push($temp_g_id_actu, key($data[$i]['id']));
                            array_push($dates_rendus, array("id_groupe"=>key($data[$i]['id']), "date"=>new \DateTime($data[$i]["date"])));
                            // Récupération de l'identifiant du groupe
                            $id = key($data[$i]['id']);
                            // Récupération de la date de rendu saisie
                            $date_rendu = new \DateTime($data[$i]["date"]);
                            
                            foreach ($temp_groupes_devoirs as $tgd)
                            {
                                if($tgd->getGroupe()->getId() == $id)
                                {
                                    $tgd->setDateARendre($date_rendu);
                                    $this->getDoctrine()->getManager()->flush();
                                }
                            }
                        }
                    }
                    $temp_g_id = array();
                    foreach ($temp_groupes_devoirs as $tgd)
                    {
                        array_push($temp_g_id, $tgd->getGroupe()->getId());
                    }
                    
                    //SUPPRESSION
                    if(count($temp_g_id)>count($temp_g_id_actu))
                    {
                        $r = array_diff($temp_g_id, $temp_g_id_actu);
                        $r = array_values($r);
                        for($i=0;$i<count($r);$i++)
                        {
                            foreach ($temp_groupes_devoirs as $tgd)
                            {
                                if($tgd->getGroupe()->getId() == $r[$i])
                                {
                                    $gps = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["groupe" => $tgd->getGroupe()]);
                                    foreach($gps as $gp)
                                    {
                                        $ugps = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $gp]);
                                        foreach($ugps as $ugp)
                                        {
                                            $em = $this->getDoctrine()->getManager();
                                            $em->remove($ugp);
                                            $em->flush();
                                        }
                                        
                                        $em = $this->getDoctrine()->getManager();
                                        $em->remove($gp);
                                        $em->flush();
                                    }
                                    $em = $this->getDoctrine()->getManager();
                                    $em->remove($tgd);
                                    $em->flush();
                                }
                            }
                        }
                    }
                    //AJOUT
                    else
                    {
                        $r = array_diff($temp_g_id_actu,$temp_g_id);
                        $r = array_values($r);
                        for($i=0;$i<count($dates_rendus);$i++)
                        {
                            for($j=0;$j<count($r);$j++)
                            {
                                if($dates_rendus[$i]['id_groupe'] == $r[$j])
                                {
                                    $groupe_devoir = new Groupe_Devoir();
                                    $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findById($r[$j]);
                                    $groupe_devoir->setGroupe($groupe[0]);
                                    $groupe_devoir->setDevoir($devoir);
                                    $groupe_devoir->setDateARendre($dates_rendus[$i]['date']);
                                    $groupe_devoir->setDateBloquante($editForm->get("date_bloquante")->getData());
                                    $groupe_devoir->setNbMaxEtudiant($editForm->get("nb_max_etudiant")->getData());
                                    $groupe_devoir->setNbMinEtudiant($editForm->get("nb_min_etudiant")->getData());

                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($groupe_devoir);

                                    $devoir->addGroupeDevoir($groupe_devoir);
                                    
                                    //Si le devoir est individuel
                                    if ($editForm->get("nb_max_etudiant")->getData() == 1 && $editForm->get("nb_min_etudiant")->getData() == 1) {
                                        //Créer les groupe_projets
                                        foreach ($groupe[0]->getUsers()->getValues() as $user) {
                                            $groupe_projet = new Groupe_projet();
                                            $groupe_projet->setDevoir($devoir);
                                            $groupe_projet->setName($user->getLastName());
                                            $groupe_projet->setGroupe($groupe[0]);
                                            $groupe_projet->setGroupeDevoir($groupe_devoir);
                                            $em->persist($groupe_projet);

                                            $user_groupe_projet = new UserGroupeProjet();
                                            $user_groupe_projet->setUser($user);
                                            $user_groupe_projet->setGroupeProjet($groupe_projet);
                                            $user_groupe_projet->setStatus(1);
                                            $user_groupe_projet->setLeader(1);
                                            $em->persist($user_groupe_projet);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                /*************************
                 * FIN
                 */

                if ($devoir->getFichier() != null) {
                    $f = new File($devoir->getFichier());
                    $fname = $this->generateUniqueFileName() . '.' . $f->guessExtension();
                    $f->move($this->getParameter("documents_devoirs_directory"), $fname);
                    $devoir->setFichier($fname);

                    if (isset($temp_filename)) {
                        $fileSystem = new Filesystem();
                        $fileSystem->remove(array($this->getParameter("documents_devoirs_directory") . "/" . $temp_filename));
                    }
                } else {
                    if ($editForm->get("conserve_file")->getData()) {
                        $devoir->setFichier($temp_filename);
                    } else {
                        if (isset($temp_filename)) {
                            $fileSystem = new Filesystem();
                            $fileSystem->remove(array($this->getParameter("documents_devoirs_directory") . "/" . $temp_filename));
                        }
                    }
                }


                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('edit_devoir', array('id' => $devoir->getId()));
            }
            return $this->render('DepotBundle:Devoir:edit.html.twig', array(
                'devoir' => $devoir,
                'edit_form' => $editForm->createView(),
                'groupes' => $groupes,
                'delete_form' => $deleteForm->createView(),
            ));
        } else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param Request $request
     * @param Devoir $devoir
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function deleteAction(Request $request, Devoir $devoir)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            $form = $this->createDeleteForm($devoir);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                //Supprimer les Groupes Devoirs, Groupe Projets et Users Groupes Projets
                foreach ($devoir->getGroupeDevoir() as $gd) {
                    $gps = $this->getDoctrine()->getRepository(Groupe_projet::class)->findBy(["groupe" => $gd->getGroupe()]);
                    foreach($gps as $gp)
                    {
                        $ugps = $this->getDoctrine()->getRepository(UserGroupeProjet::class)->findBy(["groupe_projet" => $gp]);
                        foreach($ugps as $ugp)
                        {
                            $em->remove($ugp);
                        }

                        $em->remove($gp);
                    }
                    
                    $devoir->removeGroupeDevoir($gd);
                    
                    $em->remove($gd);

                }
                $em->flush();

                //Supprimer le fichier
                if ($devoir->getFichier() != null) {
                    $fileSystem = new Filesystem();
                    $fileSystem->remove(array($this->getParameter("documents_devoirs_directory") . "/" . $devoir->getFichier()));
                }

                //Supprimer le devoir
                $em->remove($devoir);
                $em->flush();
            }
            return $this->redirectToRoute('depot_homepage');
        } else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * Allows to drop a file
     * POST only / User must be authenticated
     *
     * @param Devoir $devoir
     */
    public
    function depotAction(Request $request, Devoir $devoir)
    {
        // todo verifier la date d'upload si elle est valide
        // todo récupérer pour cela le groupe_devoir avec la date a rendre

        $file = $request->files->get("file");

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed_extensions_raw = $devoir->getExtensions()->toArray();

        $allowed_extensions = [];

        foreach ($allowed_extensions_raw as $ae) {
            $allowed_extensions[] = $ae->getExtension();
        }

        dump($extension);
        dump($allowed_extensions);

        $valid_extension = false;

        if (in_array($extension, $allowed_extensions)) {
            $valid_extension = true;
        }

        if (!$valid_extension) {
            return new JsonResponse(array("status" => "mauvaise extension"), 400);
        }


        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        // moves the file to the directory where brochures are stored
        $file->move(
            $this->getParameter('depots_devoirs_directory'),
            $fileName
        );

        $groupesProjetRepository = $this->getDoctrine()
            ->getRepository("DepotBundle:Groupe_projet");

        $groupeProjet = $groupesProjetRepository->findByDevoirAndUser($devoir, $this->getUser());

        $groupeProjet->setFileName($file->getClientOriginalName());
        $groupeProjet->setFichier($fileName);
        $groupeProjet->setDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($groupeProjet);
        $em->flush();

        return new JsonResponse(array("status" => "ok"));


    }

    /**
     * Creates a form to delete a devoir entity.
     *
     * @param Devoir $devoir The devoir entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private
    function createDeleteForm(Devoir $devoir)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_devoir', array('id' => $devoir->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    public
    function downloadAction($filename)
    {
        $file = $this->getParameter('documents_devoirs_directory') . '/' . $filename;

        return $this->file($file, "Documents.zip");
    }
}
