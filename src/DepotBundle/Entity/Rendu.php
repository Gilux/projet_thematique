<?php

namespace DepotBundle\Entity;

/**
 * Rendu
 */
class Rendu
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fichier;

    /**
     * @var \DateTime
     */
    private $date;


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
     * Set fichier
     *
     * @param string $fichier
     *
     * @return Rendu
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
     * @return Rendu
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
}
