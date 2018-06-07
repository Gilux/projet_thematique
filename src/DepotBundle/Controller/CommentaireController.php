<?php
/**
 * Created by PhpStorm.
 * User: vincentpochon
 * Date: 10/05/2018
 * Time: 10:30
 */

namespace DepotBundle\Controller;


use DepotBundle\Entity\Commentaire;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentaireController extends Controller
{
    public function newAction(Request $request)
    {
        parse_str($request->getContent(), $data);

        // Test sur le nom du champs du comentaire pour savoir
        // si c'est un commentaire fils ou un commentaire parent
        if (isset($data["comment_nochild"])) {

            $commentaire = $data["comment_nochild"];
            $commentaire = array_values($commentaire);
            $texte = $commentaire[0];

            $data["input"] = array_values($data["input"]);
            $parent = $data["input"][0];

            // Récupérer le Commentaire parent
            $commentaire_parent = $this->getDoctrine()->getRepository("DepotBundle:Commentaire")->find($parent);
        } else if (isset($data["comment_withchild"])) {

            $texte = $data["comment_withchild"];

            // Assigner le commentaire parent à NULL
            $commentaire_parent = null;
        }
        $devoir = $this->getDoctrine()->getRepository("DepotBundle:Devoir")->find($data["devoir"]);

        $commentaire = new Commentaire();
        $commentaire->setDate(new \DateTime());
        $commentaire->setTexte($texte);
        $commentaire->setUser($this->getUser());
        $commentaire->setCommentaireParent($commentaire_parent);
        $commentaire->setDevoir($devoir);

        $em = $this->getDoctrine()->getManager();
        $em->persist($commentaire);
        $em->flush();

        return $this->redirectToRoute("show_devoir", array("devoir" => $devoir->getId()));
    }
}