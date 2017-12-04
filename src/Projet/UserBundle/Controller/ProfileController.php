<?php

namespace Projet\UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProfileController extends BaseController {

	/**
	 * @ParamConverter("user", options={"mapping": {"user_id": "id"}})
	 */
	public function showAction(\Projet\UserBundle\Entity\User $user=null){
		
		if ($user === null) {
			$user = $this->getUser();
			if (!is_object($user) || !$user instanceof UserInterface) {
            	throw new AccessDeniedException('This user does not have access to this section.');
        	}
		}
        
        return $this->render('@FOSUser/Profile/show.html.twig', array(
            'user' => $user,
        ));
	}

}