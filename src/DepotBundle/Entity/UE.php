<?php

namespace DepotBundle\Entity;

/**
 * UE
 */
class UE
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $devoirs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->devoirs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return UE
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return UE
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Add devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     *
     * @return UE
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
  
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupes;


    /**
     * Add groupe
     *
     * @param \DepotBundle\Entity\Groupe $groupe
     *
     * @return UE
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
}
