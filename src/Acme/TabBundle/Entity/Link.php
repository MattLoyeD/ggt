<?php

namespace Acme\TabBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Acme\UserBundle\Entity\User;
use Acme\TabBundle\Entity\Category;

/**
 * Link
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Acme\TabBundle\Entity\LinkRepository")
 */
class Link
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

   /**
     * 
     * @ORM\ManyToOne(targetEntity="Acme\UserBundle\Entity\User", inversedBy="user")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="added_date", type="datetime", nullable=true)
     */
    private $added_date;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Acme\TabBundle\Entity\Category", inversedBy="id")
     */
    private $category;


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
     * Set author
     *
     * @param Acme\UserBundle\Entity\User $user
     */
    public function setUser(\Acme\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Set category
     *
     * @param Acme\TabBundle\Entity\Category $category
     */
    public function setCategory(\Acme\TabBundle\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     */
    public function getCategory()
    {
       return  $this->category;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Link
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
     * Set link
     *
     * @param string $link
     * @return Link
     */
    public function setLink($link)
    {
        $this->link = $link;
    
        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Link
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }


    public function __construct(){
        $this->name = '';
        $this->link = '';
        $this->added_date = new \DateTime('now');
        $this->type = 'rss';
        $this->category = new \Doctrine\Common\Collections\ArrayCollection;
    }
}
