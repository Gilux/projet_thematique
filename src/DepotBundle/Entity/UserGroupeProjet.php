<?php

namespace DepotBundle\Entity;
use UserBundle\Entity\User;

/**
 * UE
 */
class UserGroupeProjet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var int
     */
    private $status;


    /**
     * @var Groupe_projet
     */
    private $groupe_projet;


    /**
     * @var User
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return "";
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Groupe_projet
     */
    public function getGroupeProjet()
    {
        return $this->groupe_projet;
    }

    /**
     * @param Groupe_projet $groupe_projet
     */
    public function setGroupeProjet($groupe_projet)
    {
        $this->groupe_projet = $groupe_projet;
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



}
