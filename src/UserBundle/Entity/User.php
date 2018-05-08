<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;

/**
 * @UniqueEntity(fields="email", message="Cette adresse e-mail est déjà utilisée")
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="groupe_devoir")
 * @Notifiable(name="groupe_devoir")
 */
class User extends BaseUser implements NotifiableInterface
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $devoirs;


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
    private $ues;


    /**
     * Add ue
     *
     * @param \DepotBundle\Entity\UE $ue
     *
     * @return User
     */
    public function addUes(\DepotBundle\Entity\UE $ue)
    {
        $this->ues[] = $ue;

        return $this;
    }

    /**
     * Remove ue
     *
     * @param \DepotBundle\Entity\UE $ue
     */
    public function removeUes(\DepotBundle\Entity\UE $ue)
    {
        $this->ues->removeElement($ue);
    }

    /**
     * Get ue
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUes()
    {
        return $this->ues;
    }

    /**
     * Add ue
     *
     * @param \DepotBundle\Entity\UE $ue
     *
     * @return User
     */
    public function addUe(\DepotBundle\Entity\UE $ue)
    {
        $this->ues[] = $ue;

        return $this;
    }

    /**
     * Remove ue
     *
     * @param \DepotBundle\Entity\UE $ue
     */
    public function removeUe(\DepotBundle\Entity\UE $ue)
    {
        $this->ues->removeElement($ue);
    }

    /**
     * Add devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     *
     * @return User
     */
    public function addDevoir(\DepotBundle\Entity\Devoir $devoir)
    {
        $this->devoirs[] = $devoir;
        return $this;
    }

    /**
     * Remove devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     */
    public function removeDevoir(\DepotBundle\Entity\Devoir $devoir)
    {
        $this->devoirs->removeElement($devoir);
    }

    /**
     * Get devoirs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevoirs()
    {
        return $this->devoirs;
    }
}
