<?php

namespace Acme\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\OneToMany(targetEntity="Acme\TabBundle\Entity\Link", mappedBy="link")
     * @ORM\OrderBy({"status" = "ASC", "date" = "DESC"})
     */
    private $link;


    public function __construct(){
        parent::__construct();
        $this->link = new \Doctrine\Common\Collections\ArrayCollection;
    }

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="name", type="string", length=255)
    //  */
    // private $name;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="mail", type="string", length=255)
    //  */
    // private $mail;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="password", type="string", length=255)
    //  */
    // private $password;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="secret", type="string", length=255)
    //  */
    // private $secret;


    // public function __construct()
    // {
    //     parent::__construct();
    //     // your own logic
    // }


    // /**
    //  * Get id
    //  *
    //  * @return integer 
    //  */
    // public function getId()
    // {
    //     return $this->id;
    // }

    // /**
    //  * Set name
    //  *
    //  * @param string $name
    //  * @return User
    //  */
    // public function setName($name)
    // {
    //     $this->name = $name;
    
    //     return $this;
    // }

    // /**
    //  * Get name
    //  *
    //  * @return string 
    //  */
    // public function getName()
    // {
    //     return $this->name;
    // }

    // /**
    //  * Set mail
    //  *
    //  * @param string $mail
    //  * @return User
    //  */
    // public function setMail($mail)
    // {
    //     $this->mail = $mail;
    
    //     return $this;
    // }

    // /**
    //  * Get mail
    //  *
    //  * @return string 
    //  */
    // public function getMail()
    // {
    //     return $this->mail;
    // }

    // /**
    //  * Set password
    //  *
    //  * @param string $password
    //  * @return User
    //  */
    // public function setPassword($password)
    // {
    //     $this->password = $password;
    
    //     return $this;
    // }

    // /**
    //  * Get password
    //  *
    //  * @return string 
    //  */
    // public function getPassword()
    // {
    //     return $this->password;
    // }

    // /**
    //  * Set secret
    //  *
    //  * @param string $secret
    //  * @return User
    //  */
    // public function setSecret($secret)
    // {
    //     $this->secret = $secret;
    
    //     return $this;
    // }

    // /**
    //  * Get secret
    //  *
    //  * @return string 
    //  */
    // public function getSecret()
    // {
    //     return $this->secret;
    // }
}
