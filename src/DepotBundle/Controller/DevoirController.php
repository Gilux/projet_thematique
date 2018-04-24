<?php

namespace DepotBundle\Controller;

use DepotBundle\Entity\Devoir;
use DepotBundle\Entity\Groupe;
use DepotBundle\Entity\Groupe_Devoir;
use Mgilet\NotificationBundle\Controller\NotificationController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use UserBundle\Entity\User;

class DevoirController extends Controller
{
    public function indexAction()
    {
        return $this->render('DepotBundle:Devoir:index.html.twig');
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

        $user = $this->getDoctrine()->getRepository("UserBundle:User")->find($this->getUser());

        $devoir = new Devoir();
        $form = $this->createForm('DepotBundle\Form\Type\DevoirNewType', $devoir, array('ues' => $user->getUes()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (count($form->get("fichiers")->getData()) != 0) {
                /**
                 * Upload des fichiers
                 */
                // Création d'une archive ZIP
                $zip = new \ZipArchive();
                // Nom de l'archive ZIP
                $filename = $this->getParameter("documents_devoirs_directory") . "/" . $this->generateUniqueFileName() . ".zip";

                // Ouverture de l'archive
                if ($zip->open($filename, \ZipArchive::CREATE) !== TRUE) {
                    exit("Impossible d'ouvrir le fichier <$filename>\n");
                }

                // Tableau des types MIME autorisées
                $extension_autorisee = array(
                    "application/pdf",
                    "application/x-pdf",
                    "application/zip",
                    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                    "application/msword"
                );
                $files = $form->get("fichiers")->getData();
                $filesToRemove = array();
                foreach ($files as $file) {
                    $f = new File($file);

                    // Si le type MIME est bon
                    if (in_array($f->getMimeType(), $extension_autorisee)) {
                        // Générer un nom pour le fichier
                        $fname = $this->generateUniqueFileName() . '.' . $f->guessExtension();
                        // Move le fichier dans le bon dossier
                        $f->move($this->getParameter("documents_devoirs_directory"), $fname);

                        // Ajouter le fichier à l'archive ZIP
                        $zip->addFile($this->getParameter("documents_devoirs_directory") . "/" . $fname, $fname);

                        // Alimenter le tableau des fichiers à supprimer après la création de l'archive
                        array_push($filesToRemove, $this->getParameter("documents_devoirs_directory") . "/" . $fname);
                    } else // Si le type MIME n'est pas bon, renvoie une erreur
                    {
                        $errors[] = "Votre fichier n'est pas conforme aux attentes";

                        return $this->render('DepotBundle:Devoir:new.html.twig', array(
                            'devoir' => $devoir,
                            'user' => $user,
                            'add_form' => $form->createView(),
                            'errors' => $errors
                        ));
                    }
                }
                // Fermer l'archive
                $zip->close();

                // Supprimer les fichiers du dossier
                $fileSystem = new Filesystem();
                $fileSystem->remove($filesToRemove);

                // Ajouter le chemin du fichier crée à l'objet Devoir.
                $devoir->setFichier($filename);
            }

            // Récupération des données concernant les groupes
            $data = $request->request->get('groupes');

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

            $data = "";
            for ($i = 0; $i < count($groupes); $i++) {
                $data .= $this->render('DepotBundle:Devoir:groupes.html.twig', array(
                    "id" => $groupes[$i]->getId(),
                    "name" => $groupes[$i]->getName(),
                    "it" => $i
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
}
