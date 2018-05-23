<?php

namespace DepotBundle\Controller\Admin;

use DepotBundle\Entity\UE;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver\SQLAnywhere\SQLAnywhereException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class UEController extends Controller
{
    public function indexAction()
    {

    }

    public function listAction()
    {
        $ues = $this->getDoctrine()->getRepository(UE::class)->findAll();

        return $this->render('DepotBundle:Admin\UE:list.html.twig', array('ues' => $ues ));
    }

    public function editAction(Request $request, UE $ue)
    {
        $editForm = $this->createForm('DepotBundle\Form\Type\Admin\UEType', $ue);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ue_list');
        }
        return $this->render('DepotBundle:Admin\UE:edit.html.twig', array(
            'ue' => $ue,
            'edit_form' => $editForm->createView(),
        ));
    }

    public function addAction(Request $request)
    {
        $ue = new UE();
        $form = $this->createForm('DepotBundle\Form\Type\Admin\UEType', $ue);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ue);
            $em->flush();

            return $this->redirectToRoute('ue_list');
        }

        return $this->render('DepotBundle:Admin\UE:add.html.twig', array(
            'ue' => $ue,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, UE $ue)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($ue);
        try{
            $em->flush();
            return $this->redirectToRoute('ue_list');
        }
        catch (\Doctrine\DBAL\DBALException $e)
        {
            $this->addFlash("error", "Impossible de supprimer cette UE car elle est liée à d'autres composants.");
            return $this->redirectToRoute('ue_list');
        }
    }
}