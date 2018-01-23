<?php

namespace DepotBundle\Entity;

/**
 * Groupe_Devoir
 */
class Groupe_Devoir
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date_a_rendre;

    /**
     * @var boolean
     */
    private $date_bloquante = false;

    /**
     * @var integer
     */
    private $nb_max_etudiant;

    /**
     * @var integer
     */
    private $nb_min_etudiant;

    /**
     * @var \DepotBundle\Entity\Groupe
     */
    private $groupe;

    /**
     * @var \DepotBundle\Entity\Devoir
     */
    private $devoir;


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
     * Set dateARendre
     *
     * @param \DateTime $dateARendre
     *
     * @return Groupe_Devoir
     */
    public function setDateARendre($dateARendre)
    {
        $this->date_a_rendre = $dateARendre;

        return $this;
    }

    /**
     * Get dateARendre
     *
     * @return \DateTime
     */
    public function getDateARendre()
    {
        return $this->date_a_rendre;
    }

    /**
     * Set dateBloquante
     *
     * @param boolean $dateBloquante
     *
     * @return Groupe_Devoir
     */
    public function setDateBloquante($dateBloquante)
    {
        $this->date_bloquante = $dateBloquante;

        return $this;
    }

    /**
     * Get dateBloquante
     *
     * @return boolean
     */
    public function getDateBloquante()
    {
        return $this->date_bloquante;
    }

    /**
     * Set nbMaxEtudiant
     *
     * @param integer $nbMaxEtudiant
     *
     * @return Groupe_Devoir
     */
    public function setNbMaxEtudiant($nbMaxEtudiant)
    {
        $this->nb_max_etudiant = $nbMaxEtudiant;

        return $this;
    }

    /**
     * Get nbMaxEtudiant
     *
     * @return integer
     */
    public function getNbMaxEtudiant()
    {
        return $this->nb_max_etudiant;
    }

    /**
     * Set nbMinEtudiant
     *
     * @param integer $nbMinEtudiant
     *
     * @return Groupe_Devoir
     */
    public function setNbMinEtudiant($nbMinEtudiant)
    {
        $this->nb_min_etudiant = $nbMinEtudiant;

        return $this;
    }

    /**
     * Get nbMinEtudiant
     *
     * @return integer
     */
    public function getNbMinEtudiant()
    {
        return $this->nb_min_etudiant;
    }

    /**
     * Set groupe
     *
     * @param \DepotBundle\Entity\Groupe $groupe
     *
     * @return Groupe_Devoir
     */
    public function setGroupe(\DepotBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \DepotBundle\Entity\Groupe
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     *
     * @return Groupe_Devoir
     */
    public function setDevoir(\DepotBundle\Entity\Devoir $devoir = null)
    {
        $this->devoir = $devoir;

        return $this;
    }

    /**
     * Get devoir
     *
     * @return \DepotBundle\Entity\Devoir
     */
    public function getDevoir()
    {
        return $this->devoir;
    }
}

