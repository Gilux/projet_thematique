<?php

namespace DepotBundle\Controller;

use DepotBundle\Entity\Commentaire;
use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\Groupe_Devoir;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;


class DevoirController extends Controller
{
    public function showAction(Devoir $devoir)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')) {
            return $this->forward('DepotBundle:Devoir:showEtudiant', ["devoir" => $devoir]);
        }
        else if($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            return $this->forward('DepotBundle:Devoir:showEnseignant', ["devoir" => $devoir]);
        }
        else {
            throw $this->createNotFoundException();
        }
    }

    public function showEtudiantAction(Devoir $devoir) {
        $user = $this->getUser();
        $groupes_devoirs = $this->getDoctrine()->getRepository("DepotBundle:Groupe_Devoir")->findBy([
            "devoir" => $devoir,
        ]);

        $groupes = [];

        $userDansGroupe = false;
        $groupeDevoirUser = null;

        // Tourner parmi les groupes de l'ue concernée par le devoir
        foreach ($groupes_devoirs as $gd) {

            $g = $gd->getGroupe();
            foreach($g->getUsers() as $u) {
                if(!$userDansGroupe) {
                    if($u->getId() == $user->getId()) {
                        $userDansGroupe = true;
                        $groupeDevoirUser = $gd;
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


        return $this->render('DepotBundle:Devoir:showEtudiant.html.twig', ["devoir" => $devoir, "groupe_devoir" => $groupeDevoirUser, "minmax_groups" => $minmax_groups, "groupes_projet" => $groupes_projet]);
    }

    public function showEnseignantAction(Devoir $devoir) {
        return $this->render('DepotBundle:Devoir:showEnseignant.html.twig', ["devoir" => $devoir]);
    }

    public function sendNotification(User $user, Groupe_Devoir $groupeDevoir)
    {
        $ueName = $groupeDevoir->getGroupe()->getUE();
        $groupeName = $groupeDevoir->getGroupe()->getName();
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
                            'titre' => $groupeDevoir->getDevoir()->getTitre(),
                            'date_a_rendre' => $groupeDevoir->getDateARendre(),
                        ]
                    )
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        //notifications
        $manager = $this->get('mgilet.notification');
        $notif = $manager->createNotification('Nouveau devoir');
        $notif->setMessage($ueName . '/' . $groupeName . ' : ' . $groupeDevoir->getDevoir()->getTitre() . '');
        $notif->setLink('http://symfony.com/');
        $manager->addNotification(array($user), $notif, true);
    }

    public function newAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
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
        }
        else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    public function getGroupeAction(Request $request)
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

    private function getErrorMessages(FormInterface $form)
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
    public function editAction(Request $request, Devoir $devoir)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());
            $temp_devoir = $devoir;

            $gd = array();
            $groupes = array();
            foreach ($devoir->getGroupeDevoir() as $groupe_devoir) {
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
            $temp_groupes_devoirs = $devoir->getGroupeDevoir();


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


                //Supprimer les Groupes Devoirs
                foreach ($devoir->getGroupeDevoir() as $gd) {
                    $devoir->removeGroupeDevoir($gd);
                    $this->getDoctrine()->getManager()->remove($gd);
                }

                //Ajouter
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
                        $groupe_devoir->setGroupe($groupe[0]);
                        $groupe_devoir->setDevoir($devoir);
                        $groupe_devoir->setDateARendre($date_rendu);
                        $groupe_devoir->setDateBloquante($editForm->get("date_bloquante")->getData());
                        $groupe_devoir->setNbMaxEtudiant($editForm->get("nb_max_etudiant")->getData());
                        $groupe_devoir->setNbMinEtudiant($editForm->get("nb_min_etudiant")->getData());

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($groupe_devoir);

                        $devoir->addGroupeDevoir($groupe_devoir);
                    }
                }

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
                    /*return $this->render('DepotBundle:Devoir:edit.html.twig', array(
                        'devoir' => $devoir,
                        'user' => $user,
                        'edit_form' => $editForm->createView(),
                        'errors' => $errors,
                        'delete_form' => $deleteForm->createView(),
                    ));*/
                }

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
        }
        else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param Request $request
     * @param Devoir $devoir
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Devoir $devoir)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT')) {
            $form = $this->createDeleteForm($devoir);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                //Supprimer les Groupes Devoirs
                foreach ($devoir->getGroupeDevoir() as $gd) {
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
        }
        else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * Creates a form to delete a devoir entity.
     *
     * @param Devoir $devoir The devoir entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Devoir $devoir)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_devoir', array('id' => $devoir->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    public function downloadAction($filename)
    {
        $file = $this->getParameter('documents_devoirs_directory').'/'.$filename;

        return $this->file($file, "Documents.zip");
    }
}
