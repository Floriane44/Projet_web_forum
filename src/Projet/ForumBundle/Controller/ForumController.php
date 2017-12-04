<?php

namespace Projet\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Projet\ForumBundle\Entity\Theme;
use Projet\ForumBundle\Entity\Category;
use Projet\ForumBundle\Entity\SubCategory;
use Projet\ForumBundle\Entity\Subject;
use Projet\ForumBundle\Entity\Comment;
use Projet\ForumBundle\Form\ThemeType;
use Projet\ForumBundle\Form\CategoryType;
use Projet\ForumBundle\Form\SubCategoryType;
use Projet\ForumBundle\Form\SubjectType;
use Projet\ForumBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class ForumController extends Controller
{
	
	public function indexAction(){
		$listThemes = $this->getDoctrine()
      		->getManager()
      		->getRepository('ProjetForumBundle:Theme')
      		->getOrderedThemes()
      	;
		
		return $this->render('ProjetForumBundle:Forum:index.html.twig', array(
			'listThemes'		=> $listThemes
		));
	}

	/**
	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
	 */
	public function viewCategoryAction(Category $category){
		$listSubCategories = $category->getSubCategories();

    	return $this->render('ProjetForumBundle:Forum:viewCategory.html.twig', array(
    		'category'	=> $category,
    		'listSubCategories' => $listSubCategories,
    	));
	}

	/**
	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
	 */
	public function viewSubCategoryAction(Category $category, SubCategory $subCategory){
		$listSubjects = $subCategory->getSubject();

		return $this->render('ProjetForumBundle:Forum:viewSubCategory.html.twig', array(
			'category' => $category,
			'subCategory' => $subCategory,
			'listSubjects' => $listSubjects
		));
	}

	/**
	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
	 * @ParamConverter("subject", options={"mapping": {"subject_id": "id"}})
	 */
	public function viewSubjectAction(Category $category, SubCategory $subCategory, Subject $subject, Request $request){
		$listComments = $subject->getComments();

		$comment = new Comment();
		$form = $this->createForm(CommentType::class, $comment);
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {

				$comment->setSubject($subject);
        $comment->setUser($this->getUser());
				$em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        // creating the ACL
        $aclProvider = $this->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($comment);
        try {
          $acl = $aclProvider->findAcl($objectIdentity);
        } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
          $acl = $aclProvider->createAcl($objectIdentity);
        }

	      // retrieving the security identity of the currently logged-in user
    	  $tokenStorage = $this->get('security.token_storage');
        $user = $tokenStorage->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        // grant owner access
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);

        return $this->redirectToRoute('projet_forum_view_subject', array(
          'category_id' => $category->getId(),
          'subCategory_id' => $subCategory->getId(),
          'subject_id' => $subject->getId()
        ));
			}
		}

		return $this->render('ProjetForumBundle:Forum:viewSubject.html.twig', array(
			'category' => $category,
			'subCategory' => $subCategory,
			'subject' => $subject,
			'listComments' => $listComments,
			'form' => $form->createView()
		));
	}

	/**
   * @Security("has_role('ROLE_ADMIN')")
   */
	public function addThemeAction(Request $request){
		$theme = new Theme();
		// On crée le FormBuilder grâce au service form factory
    	$form = $this->createForm(ThemeType::class, $theme);
    	// Si la requête est en POST
    	if ($request->isMethod('POST')) {
      		// On fait le lien Requête <-> Formulaire
      		// À partir de maintenant, la variable $theme contient les valeurs entrées dans le formulaire par l'admin
      		$form->handleRequest($request);
      		// On vérifie que les valeurs entrées sont correctes
      		if ($form->isValid()) {
      			// On enregistre notre objet $theme dans la base de données
        		$em = $this->getDoctrine()->getManager();
        		$em->persist($theme);
        		$em->flush();
        		//$request->getSession()->getFlashBag()->add('notice', 'Thème bien enregistré.');
        		// On redirige vers l'accueil
        		return $this->redirectToRoute('projet_forum_homepage');
        	}
        }
        // À ce stade, le formulaire n'est pas valide car :
    	// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
    	// - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
    	return $this->render('ProjetForumBundle:Forum:addTheme.html.twig', array(
      		'form' => $form->createView()
    	));
	}

	/**
   * @Security("has_role('ROLE_ADMIN')")
   */
	public function addCategoryAction(Request $request){
		$category = new Category();
		// On crée le FormBuilder grâce au service form factory
    	$form = $this->createForm(CategoryType::class, $category);
    	// Si la requête est en POST
    	if ($request->isMethod('POST')) {
      		// On fait le lien Requête <-> Formulaire
      		// À partir de maintenant, la variable $category contient les valeurs entrées dans le formulaire par l'admin
      		$form->handleRequest($request);
      		// On vérifie que les valeurs entrées sont correctes
      		if ($form->isValid()) {
      			// On enregistre notre objet $category dans la base de données
        		$em = $this->getDoctrine()->getManager();
        		$em->persist($category);
        		$em->flush();
        		//$request->getSession()->getFlashBag()->add('notice', 'Catégorie bien enregistré.');
        		// On redirige vers l'accueil
        		return $this->redirectToRoute('projet_forum_homepage');
        	}
        }
        // À ce stade, le formulaire n'est pas valide car :
    	// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
    	// - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
    	return $this->render('ProjetForumBundle:Forum:addCategory.html.twig', array(
      		'form' => $form->createView(),

    	));
	}

	/**
   	 * @Security("has_role('ROLE_ADMIN')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
     */
	public function addSubCategoryAction(Category $category, Request $request){
		$subCategory = new SubCategory();
		// On crée le FormBuilder grâce au service form factory
    	$form = $this->createForm(SubCategoryType::class, $subCategory);
    	// Si la requête est en POST
    	if ($request->isMethod('POST')) {
      		$form->handleRequest($request);
      		// On vérifie que les valeurs entrées sont correctes
      		if ($form->isValid()) {
      			// On assigne la catégorie
      			$subCategory->setCategory($category);
      			// On enregistre notre objet $category dans la base de données
        		$em = $this->getDoctrine()->getManager();
        		$em->persist($subCategory);
        		$em->flush();
        		//$request->getSession()->getFlashBag()->add('notice', 'Catégorie bien enregistré.');
        		// On redirige vers l'accueil
        		return $this->redirectToRoute('projet_forum_view_category', array(
        			'category_id' => $category->getId(),
        		));
        	}
        }
        
    	return $this->render('ProjetForumBundle:Forum:addSubCategory.html.twig', array(
      		'form' => $form->createView(),
          'category' => $category,
    	));
	}

	/**
   	 * @Security("has_role('ROLE_USER')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
   	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
     */
	public function addSubjectAction(Category $category, SubCategory $subCategory, Request $request){
		$subject = new Subject();
		$form = $this->createForm(SubjectType::class, $subject);
		if ($request->isMethod('POST')) {
      		$form->handleRequest($request);

      		if ($form->isValid()) {

      			$subject->setSubCategory($subCategory);
            $subject->setUser($this->getUser());
      			$em = $this->getDoctrine()->getManager();
        		$em->persist($subject);
        		$em->flush();

        		// creating the ACL
            $aclProvider = $this->get('security.acl.provider');
          	$objectIdentity = ObjectIdentity::fromDomainObject($subject);
            try {
            	$acl = $aclProvider->findAcl($objectIdentity);
            } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
              $acl = $aclProvider->createAcl($objectIdentity);
            }

	          // retrieving the security identity of the currently logged-in user
    	      $tokenStorage = $this->get('security.token_storage');
        	  $user = $tokenStorage->getToken()->getUser();
          	$securityIdentity = UserSecurityIdentity::fromAccount($user);

            // grant owner access
          	$acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
          	$aclProvider->updateAcl($acl);

        		return $this->redirectToRoute('projet_forum_view_subject', array(
        			'category_id' => $category->getId(),
        			'subCategory_id' => $subCategory->getId(),
        			'subject_id' => $subject->getId()
        		));
      		}
      	}
      	return $this->render('ProjetForumBundle:Forum:addSubject.html.twig', array(
      		'form' => $form->createView(),
          'category' => $category,
          'subCategory' => $subCategory,
    	));
	}

	/**
   	 * @Security("has_role('ROLE_USER')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
   	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
   	 * @ParamConverter("subject", options={"mapping": {"subject_id": "id"}})
     */
	public function editSubjectAction(Category $category, SubCategory $subCategory, Subject $subject, Request $request){

		$authorizationChecker = $this->get('security.authorization_checker');

    // check for edit access
    if (false === $authorizationChecker->isGranted('EDIT', $subject) and !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
        throw new AccessDeniedException();
    }

		$form = $this->createForm(SubjectType::class, $subject);
		if ($request->isMethod('POST')) {
      		$form->handleRequest($request);

      		if ($form->isValid()) {
      			$em = $this->getDoctrine()->getManager();
        		$em->persist($subject);
        		$em->flush();
        		return $this->redirectToRoute('projet_forum_view_subject', array(
        			'category_id' => $category->getId(),
        			'subCategory_id' => $subCategory->getId(),
        			'subject_id' => $subject->getId()
        		));
      		}
      	}
      	return $this->render('ProjetForumBundle:Forum:editSubject.html.twig', array(
      		'form' => $form->createView(),
          'category' => $category,
          'subCategory' => $subCategory,
          'subject' => $subject
    	));
	}

	/**
   	 * @Security("has_role('ROLE_USER')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
   	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
   	 * @ParamConverter("subject", options={"mapping": {"subject_id": "id"}})
   	 * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     */
	public function editCommentAction(Category $category, SubCategory $subCategory, Subject $subject, Comment $comment, Request $request){

		$authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted('EDIT', $comment) and !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(CommentType::class, $comment);
		if ($request->isMethod('POST')) {
      		$form->handleRequest($request);

      		if ($form->isValid()) {
      			$em = $this->getDoctrine()->getManager();
        		$em->persist($comment);
        		$em->flush();
        		return $this->redirectToRoute('projet_forum_view_subject', array(
        			'category_id' => $category->getId(),
        			'subCategory_id' => $subCategory->getId(),
        			'subject_id' => $subject->getId()
        		));
      		}
      	}
      	return $this->render('ProjetForumBundle:Forum:editComment.html.twig', array(
      		'form' => $form->createView(),
          'category' => $category,
          'subCategory' => $subCategory,
          'subject' => $subject
    	));
	}

	/**
   	 * @Security("has_role('ROLE_USER')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
   	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
   	 * @ParamConverter("subject", options={"mapping": {"subject_id": "id"}})
     */
	public function deleteSubjectAction(Category $category, SubCategory $subCategory, Subject $subject, Request $request){

		$authorizationChecker = $this->get('security.authorization_checker');

        // check for delete access
        if (false === $authorizationChecker->isGranted('DELETE', $subject) and !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException();
        }

		// On crée un formulaire vide, qui ne contiendra que le champ CSRF
    	// Cela permet de protéger la suppression d'annonce contre cette faille
    	$form = $this->get('form.factory')->create();

    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
    		$em = $this->getDoctrine()->getManager();
      		$em->remove($subject);
      		$em->flush();

      		//$request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");
      		return $this->redirectToRoute('projet_forum_view_subCategory', array(
       			'category_id' => $category->getId(),
        		'subCategory_id' => $subCategory->getId(),
        	));
    	}
    
    	return $this->render('ProjetForumBundle:Forum:deleteSubject.html.twig', array(
    		  'category' => $category,
			    'subCategory' => $subCategory,
			    'subject' => $subject,
      		'form' => $form->createView(),
    	));
	}

	/**
   	 * @Security("has_role('ROLE_USER')")
   	 * @ParamConverter("category", options={"mapping": {"category_id": "id"}})
   	 * @ParamConverter("subCategory", options={"mapping": {"subCategory_id": "id"}})
   	 * @ParamConverter("subject", options={"mapping": {"subject_id": "id"}})
   	 * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     */
	public function deleteCommentAction(Category $category, SubCategory $subCategory, Subject $subject, Comment $comment, Request $request){

		    $authorizationChecker = $this->get('security.authorization_checker');

        // check for delete access
        if (false === $authorizationChecker->isGranted('DELETE', $comment) and !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedException();
        }

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
    		$em = $this->getDoctrine()->getManager();
      		$em->remove($comment);
      		$em->flush();
      		//$request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");
      		return $this->redirectToRoute('projet_forum_view_subject', array(
       			'category_id' => $category->getId(),
        		'subCategory_id' => $subCategory->getId(),
        		'subject_id' => $subject->getId(),
        	));
      	}

      	return $this->render('ProjetForumBundle:Forum:deleteComment.html.twig', array(
    		'category' => $category,
			'subCategory' => $subCategory,
			'subject' => $subject,
			'comment' => $comment,
      		'form' => $form->createView(),
    	));
	}

	public function membersAction(){
      $listMembers = $this->getDoctrine()
          ->getManager()
          ->getRepository('ProjetUserBundle:User')
          ->findAll()
      ;

      return $this->render('ProjetForumBundle:Forum:members.html.twig', array(
          'listMembers'    => $listMembers
      ));
  }

  public function aproposAction() {
      return $this->render('ProjetForumBundle:Forum:apropos.html.twig');
  }

}