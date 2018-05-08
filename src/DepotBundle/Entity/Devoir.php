<?php

namespace DepotBundle\Entity;

use DepotBundle\DepotBundle;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\User;

/**
 * Devoir
 */
class Devoir
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @Assert\NotNull()
     */
    private $intitule;

    /**
     * @var string
     * @Assert\NotNull()
     */
    private $titre;

    /**
     * @var string
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"application/zip"},
     *     mimeTypesMessage = "Veuillez téléverser un fichier du type .zip"
     * )
     */
    private $fichier;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupes_projets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupe_devoir;

    /**
     * @var \DepotBundle\Entity\UE
     */
    private $UE;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $extensions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $commentaires;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupes_projets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groupe_devoir = new \Doctrine\Common\Collections\ArrayCollection();
        $this->extensions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commentaires = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set intitule
     *
     * @param string $intitule
     *
     * @return Devoir
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Devoir
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set fichier
     *
     * @param string $fichier
     *
     * @return Devoir
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return string
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Add groupesProjet
     *
     * @param \DepotBundle\Entity\Groupe_projet $groupesProjet
     *
     * @return Devoir
     */
    public function addGroupesProjet(\DepotBundle\Entity\Groupe_projet $groupesProjet)
    {
        $this->groupes_projets[] = $groupesProjet;

        return $this;
    }

    /**
     * Remove groupesProjet
     *
     * @param \DepotBundle\Entity\Groupe_projet $groupesProjet
     */
    public function removeGroupesProjet(\DepotBundle\Entity\Groupe_projet $groupesProjet)
    {
        $this->groupes_projets->removeElement($groupesProjet);
    }

    /**
     * Get groupesProjets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupesProjets()
    {
        return $this->groupes_projets;
    }

    /**
     * Add groupeDevoir
     *
     * @param \DepotBundle\Entity\Groupe_Devoir $groupeDevoir
     *
     * @return Devoir
     */
    public function addGroupeDevoir(\DepotBundle\Entity\Groupe_Devoir $groupeDevoir)
    {
        $this->groupe_devoir[] = $groupeDevoir;

        return $this;
    }

    /**
     * Remove groupeDevoir
     *
     * @param \DepotBundle\Entity\Groupe_Devoir $groupeDevoir
     */
    public function removeGroupeDevoir(\DepotBundle\Entity\Groupe_Devoir $groupeDevoir)
    {
        $this->groupe_devoir->removeElement($groupeDevoir);
    }

    /**
     * Get groupeDevoir
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupeDevoir()
    {
        return $this->groupe_devoir;
    }

    /**
     * Set uE
     *
     * @param \DepotBundle\Entity\UE $uE
     *
     * @return Devoir
     */
    public function setUE(\DepotBundle\Entity\UE $uE = null)
    {
        $this->UE = $uE;

        return $this;
    }

    /**
     * Get uE
     *
     * @return \DepotBundle\Entity\UE
     */
    public function getUE()
    {
        return $this->UE;
    }

    /**
     * Add extension
     *
     * @param \DepotBundle\Entity\FileExtension $extension
     *
     * @return Devoir
     */
    public function addExtension(\DepotBundle\Entity\FileExtension $extension )
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Remove extension
     *
     * @param \DepotBundle\Entity\FileExtension $extension
     */
    public function removeExtension(\DepotBundle\Entity\FileExtension $extension)
    {
        $this->extensions->removeElement($extension);
    }

    /**
     * Get extension
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $commentaires
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;
    }



}
