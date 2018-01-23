<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @UniqueEntity(fields="email", message="Cette adresse e-mail est déjà utilisée")
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
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupes;


    /**
     * Add groupe
     *
     * @param \DepotBundle\Entity\Groupe $groupe
     *
     * @return User
     */
    public function addGroupe(\DepotBundle\Entity\Groupe $groupe)
    {
        $this->groupes[] = $groupe;

        return $this;
    }

    /**
     * Remove groupe
     *
     * @param \DepotBundle\Entity\Groupe $groupe
     */
    public function removeGroupe(\DepotBundle\Entity\Groupe $groupe)
    {
        $this->groupes->removeElement($groupe);
    }

    /**
     * Get groupes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupes()
    {
        return $this->groupes;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupes_projet;


    /**
     * Add groupesProjet
     *
     * @param \DepotBundle\Entity\Groupe_projet $groupesProjet
     *
     * @return User
     */
    public function addGroupesProjet(\DepotBundle\Entity\Groupe_projet $groupesProjet)
    {
        $this->groupes_projet[] = $groupesProjet;

        return $this;
    }

    /**
     * Remove groupesProjet
     *
     * @param \DepotBundle\Entity\Groupe_projet $groupesProjet
     */
    public function removeGroupesProjet(\DepotBundle\Entity\Groupe_projet $groupesProjet)
    {
        $this->groupes_projet->removeElement($groupesProjet);
    }

    /**
     * Get groupesProjet
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupesProjet()
    {
        return $this->groupes_projet;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $notifications;


    /**
     * Add notification
     *
     * @param \DepotBundle\Entity\Notification $notification
     *
     * @return User
     */
    public function addNotification(\DepotBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \DepotBundle\Entity\Notification $notification
     */
    public function removeNotification(\DepotBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
