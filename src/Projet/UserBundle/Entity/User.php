<?php

namespace Projet\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="forum_user")
 * @ORM\Entity(repositoryClass="Projet\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
   	* @ORM\OneToOne(targetEntity="Projet\UserBundle\Entity\Image", cascade={"persist", "remove"})
    * @ORM\JoinColumn(nullable=true)
   	* @Assert\Valid()
   	*/
  	protected $image;

  	public function setImage(Image $image = null){
    	$this->image = $image;
  	}

  	public function getImage(){
    	return $this->image;
  	}
}

