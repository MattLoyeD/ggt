<?php

namespace Acme\TabBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RSSCache
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Acme\TabBundle\Entity\RSSCacheRepository")
 */
class RSSCache
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
     * @ORM\ManyToOne(targetEntity="Acme\TabBundle\Entity\Link", inversedBy="id")
     */
    private $id_rss;


    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=500)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=400)
     */
    private $image;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $last_update;


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
     * Set title
     *
     * @param string $title
     * @return RSSCache
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return RSSCache
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set RSS Parent ID
     *
     * @param string $id_rss
     * @return RSSCache
     */
    public function setIdRss($id_rss)
    {
        $this->id_rss = $id_rss;
    
        return $this;
    }

    /**
     * Get RSS Parent ID
     *
     * @return string 
     */
    public function getIdRss()
    {
        return $this->id_rss;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return RSSCache
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return RSSCache
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
     * Set image
     *
     * @param string $image
     * @return RSSCache
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set last_update
     *
     * @param \DateTime $lastUpdate
     * @return RSSCache
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->last_update = $lastUpdate;
    
        return $this;
    }

    /**
     * Get last_update
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }


    public function __construct(){
        $this->id_rss = '';
        $this->title = '';
        $this->image = '';
        $this->url = '';
        $this->content = '';
        $this->date = '';
        $this->last_update = new \DateTime('now');
    }

}
