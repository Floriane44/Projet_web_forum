<?php

namespace Projet\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubCategory
 *
 * @ORM\Table(name="sub_category")
 * @ORM\Entity(repositoryClass="Projet\ForumBundle\Repository\SubCategoryRepository")
 */
class SubCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Projet\ForumBundle\Entity\Category", inversedBy="subCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Projet\ForumBundle\Entity\Subject", mappedBy="subCategory")
     */
    private $subject;

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
     * Set name
     *
     * @param string $name
     *
     * @return SubCategory
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
     * Set category
     *
     * @param \Projet\ForumBundle\Entity\Category $category
     *
     * @return SubCategory
     */
    public function setCategory(\Projet\ForumBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Projet\ForumBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subject = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add subject
     *
     * @param \Projet\ForumBundle\Entity\Subject $subject
     *
     * @return SubCategory
     */
    public function addSubject(\Projet\ForumBundle\Entity\Subject $subject)
    {
        $this->subject[] = $subject;

        return $this;
    }

    /**
     * Remove subject
     *
     * @param \Projet\ForumBundle\Entity\Subject $subject
     */
    public function removeSubject(\Projet\ForumBundle\Entity\Subject $subject)
    {
        $this->subject->removeElement($subject);
    }

    /**
     * Get subject
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
