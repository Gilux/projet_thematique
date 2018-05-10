<?php

namespace DepotBundle\Entity;

/**
 * Commentaire
 */
class Commentaire
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $texte;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \UserBundle\Entity\User
     */
    private $user;

    /**
     * @var Devoir
     */
    private $devoir;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $commentaires_fils;

    /**
     * @var \DepotBundle\Entity\Commentaire
     */
    private $commentaire_parent;


    public function __construct()
    {
        $this->commentaires_fils = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set texte
     *
     * @param string $texte
     *
     * @return Commentaire
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Commentaire
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Commentaire
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Devoir
     */
    public function getDevoir()
    {
        return $this->devoir;
    }

    /**
     * @param Devoir $devoir
     */
    public function setDevoir($devoir)
    {
        $this->devoir = $devoir;
    }

    /**
     * Add commentaires_fils
     *
     * @param \DepotBundle\Entity\Commentaire $commentaires_fils
     *
     * @return Commentaire
     */
    public function addCommentaireFils(\DepotBundle\Entity\Commentaire $commentaires_fils)
    {
        $this->commentaires_fils[] = $commentaires_fils;

        return $this;
    }

    /**
     * Remove commentaires_fils
     *
     * @param \DepotBundle\Entity\Commentaire $commentaires_fils
     */
    public function removeCommentaireFils(\DepotBundle\Entity\Commentaire $commentaires_fils)
    {
        $this->commentaires_fils->removeElement($commentaires_fils);
    }

    /**
     * Get commentaires_fils
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentairesFils()
    {
        return $this->commentaires_fils;
    }

    /**
     * Set commentaire_parent
     *
     * @param \DepotBundle\Entity\Commentaire $commentaire_parent
     *
     * @return Commentaire
     */
    public function setCommentaireParent(\DepotBundle\Entity\Commentaire $commentaire_parent = null)
    {
        $this->commentaire_parent = $commentaire_parent;

        return $this;
    }

    /**
     * Get commentaire_parent
     *
     * @return Commentaire
     */
    public function getCommentaireParent()
    {
        return $this->commentaire_parent;
    }
}
