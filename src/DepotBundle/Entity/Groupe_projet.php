<?php

namespace DepotBundle\Entity;

/**
 * Groupe_projet
 */
class Groupe_projet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DepotBundle\Entity\Devoir
     */
    private $devoir;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users_groupes_projets;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fichier;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \DateTime
     */
    private $date;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users_groupes_projets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     *
     * @return Groupe_projet
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

    /**
     * Add user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Groupe_projet
     */
    public function addUser(\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * Remove user
     *
     * @param \UserBundle\Entity\User $user
     */
    public function removeUser(\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Groupe_projet
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**

     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



    /**
     * Set fichier
     *
     * @param string $fichier
     *
     * @return Groupe_projet
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Groupe_projet
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersGroupesProjets()
    {
        return $this->users_groupes_projets;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $users_groupes_projets
     */
    public function setUsersGroupesProjets($users_groupes_projets)
    {
        $this->users_groupes_projets = $users_groupes_projets;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
