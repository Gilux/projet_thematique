<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var int
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;


    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $commentaires;


    /**
     * Add commentaire
     *
     * @param \DepotBundle\Entity\Commentaire $commentaire
     *
     * @return User
     */
    public function addCommentaire(\DepotBundle\Entity\Commentaire $commentaire)
    {
        $this->commentaires[] = $commentaire;

        return $this;
    }

    /**
     * Remove commentaire
     *
     * @param \DepotBundle\Entity\Commentaire $commentaire
     */
    public function removeCommentaire(\DepotBundle\Entity\Commentaire $commentaire)
    {
        $this->commentaires->removeElement($commentaire);
    }

    /**
     * Get commentaires
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }
}
