<?php

namespace DepotBundle\Entity;

/**
 * Groupe
 */
class Groupe
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupe_devoir;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupe_devoir = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Groupe
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
     * Add groupeDevoir
     *
     * @param \DepotBundle\Entity\Groupe_Devoir $groupeDevoir
     *
     * @return Groupe
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
     * Add user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Groupe
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
     * @var \DepotBundle\Entity\UE
     */
    private $UE;


    /**
     * Set uE
     *
     * @param \DepotBundle\Entity\UE $uE
     *
     * @return Groupe
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
