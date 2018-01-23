<?php

namespace DepotBundle\Entity;

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
     */
    private $intitule;

    /**
     * @var string
     */
    private $titre;

    /**
     * @var string
     */
    private $fichier;

    /**
     * @var string
     */
    private $extensions_authorisee;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupes_projets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupe_devoir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupes_projets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groupe_devoir = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set extensionsAuthorisee
     *
     * @param string $extensionsAuthorisee
     *
     * @return Devoir
     */
    public function setExtensionsAuthorisee($extensionsAuthorisee)
    {
        $this->extensions_authorisee = $extensionsAuthorisee;

        return $this;
    }

    /**
     * Get extensionsAuthorisee
     *
     * @return string
     */
    public function getExtensionsAuthorisee()
    {
        return $this->extensions_authorisee;
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
     * @var \DepotBundle\Entity\UE
     */
    private $UE;


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
}
