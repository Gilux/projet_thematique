<?php

namespace DepotBundle\Entity;

/**
 * FillExtension
 */
class FileExtension
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $devoirs;

    public function __construct()
    {
        $this->devoirs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set extension
     *
     * @param string $extension
     *
     * @return FileExtension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Add devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     *
     * @return FileExtension
     */
    public function addDevoir(\DepotBundle\Entity\Devoir $devoir)
    {
        $this->devoirs[] = $devoir;

        return $this;
    }

    /**
     * Remove Devoir
     *
     * @param \DepotBundle\Entity\Devoir $devoir
     */
    public function removeDevoir(\DepotBundle\Entity\Devoir $devoir)
    {
        $this->devoirs->removeElement($devoir);
    }

    /**
     * Get Devoir
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevoirs()
    {
        return $this->devoirs;
    }

    public function __toString()
    {
        return $this->extension;
    }
}

