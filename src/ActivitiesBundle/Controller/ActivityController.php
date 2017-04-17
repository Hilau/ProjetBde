<?php

namespace ActivitiesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use ActivitiesBundle\Entity\ActivityIdea;
use ActivitiesBundle\Entity\ActivityUser;
use ActivitiesBundle\Entity\ActivitiesVote;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller 
{
	/**
	 * @Route("all", name="activitiesShow")
	 */
	public function showAllAction(){
		$listActivities = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->findAll();

		return $this->render('ActivitiesBundle::listActivity.html.twig', array('listActivities' => $listActivities));
	}

	/**
	 * @Route("signInActivity/{activity_id}", name="signInActivity")
	 */
	public function signInActivityAction(Request $request, $activity_id)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

		$em = $this->getDoctrine()->getManager();
		$activity = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->find($activity_id);
		$photos = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityPhoto')->findByActivity($activity);
		$commentsRepository = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:PhotoComment');
		$user = $this->get('security.context')->getToken()->getUser();

		$activityUserRepository = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityUser');
		$alreadySignIn = $activityUserRepository->findBy(array(
				"activity" => $activity,
				"user" => $user,
			));

		$commentsInfo = [];

		foreach($photos as $photo)
		{
			$comments = $commentsRepository->findByPhoto($photo);

			foreach($comments as $comment)
			{
				$comment_user = $comment->getUser();
				$user_avatar = $comment_user->getAvatar();

				$commentsInfo[] = ["avatar" => $user_avatar, "comment" => $comment->getComment(), "date" => $comment->getDate()];
			}
		}

		$activityUser = new ActivityUser();

		$form = $this->createFormBuilder($activityUser)	        
	        ->add('S\'inscrire', 'submit')
	        ->getForm();

	    $form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {
	    	if(count($alreadySignIn) == 0)
	    	{
		        $activityUser = $form->getData();
		        $activityUser->setUser($user);
		        $activityUser->setActivity($activity);
		        $activityUser->setOtherParticipation(0);
		       	        
		        $em->persist($activityUser);
		        $em->flush();

		        $this->addFlash(
		            'success',
		            'Vous êtes inscrit !'
		        );
		    }

		    return $this->redirectToRoute('signInActivity', array('activity_id' => $activity_id));
	    }

	    else if($form->isSubmitted() && !$form->isValid())
	    {
	    	$this->addFlash(
	            'error',
	            'Error !'
	        );
	    }			

		return $this->render('ActivitiesBundle::activity.html.twig', array(
				'activity' => $activity,
				'photos' => $photos,
				'form' => $form->createView(),
				'alreadySignIn' => count($alreadySignIn),
				'commentsInfo' => $commentsInfo,
			));
	}

	/**
	* @Route("showActivitiesVote", name="showActivitiesVote")
	*/
	public function showActivitiesVoteAction(){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

		$listActivitiesIdea = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityIdea')->findAll();
		$listActivitiesVote = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivitiesVote');

		$dates = [];
		$votes = [];

		foreach($listActivitiesIdea as $activity)
		{
			$activityDates = $listActivitiesVote->findByActivity($activity);
			$activityVote = $listActivitiesVote->findOneByActivity($activity);

			foreach($activityDates as $date)
			{
				$dates[$activity->getId()][] = $date->getDate();				
			}

			$votes[$activity->getId()] = $activityVote->getVote();
		}

		return $this->render('ActivitiesBundle::listActivityVote.html.twig', array(
				'listActivitiesIdea' => $listActivitiesIdea,
				'dates' => $dates,
				'votes' => $votes,
			));
	}

	/**
	* @Route("summary", name="summaryActivity")
	*/
	public function showSummaryActivityAction()
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') || !$this->container->get('security.authorization_checker')->isGranted('ROLE_BDE')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

		$listActivities = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->findAll();
		$activitiesUsers = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityUser');

		$firstActivity = $listActivities[0];
		$date = $firstActivity->getDate();

		$firstActivityUsers = $activitiesUsers->findByActivity(1);

		$nbInscrit = count($firstActivityUsers);

		$usersInfo = [];

		foreach($firstActivityUsers as $user)
		{
			$usersInfo[] = array($user->getUser()->getNom(), $user->getUser()->getPrenom(), $user->getUser()->getPromotion(), "Aucun", $user->getUser()->getEmail());
		}

		return $this->render('ActivitiesBundle::summaryActivity.html.twig', array(
				"activities" => $listActivities,
				"date" => $date,
				"nbInscrit" => $nbInscrit,
				"usersInfo" => $usersInfo,
			));
	}

	/**
	* @Route("activityIdea", name="activityIdea")
	*/
	public function formActivityIdeaAction(Request $request)
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

		$em = $this->getDoctrine()->getManager();
	    $activityIdea = new activityIdea();
	    $user = $this->get('security.context')->getToken()->getUser();

		$form = $this->createFormBuilder($activityIdea)
	        ->add('name', TextType::class)
	        ->add('description', TextareaType::class)
	        ->add('date', DateTimeType::class)
	        ->getForm();
	        
		$form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {	        
	        $activityIdea = $form->getData();
	        $activityIdea->setUser($user);
	       	        
	        $em->persist($activityIdea);
	        $em->flush();

	        $date = $activityIdea->getDate();
	        $dates = [$date];   

	        $date2 = clone($date);	  
	        $date2->add(new \DateInterval('P1D'))->setTime(10, 0, 0);

	        $dates[] = $date2;

	        $date = clone($date2);
	        $date->add(new \DateInterval('P1D'))->setTime(14, 30, 0);

	        $dates[] = $date;

	        $date2 = clone($date);
	        $date2->add(new \DateInterval('P1D'))->setTime(16, 15, 0);

	        $dates[] = $date2;

	        foreach($dates as $date)
	        {
	        	$activityVote = new activitiesVote();
		        $activityVote->setActivity($activityIdea);
		        $activityVote->setVote(0);
		        $activityVote->setDate($date);

		        $em->persist($activityVote);
		        $em->flush();
	        }

	        $this->addFlash(
	            'success',
	            'Votre idée d\'activité a bien été soumise !'
	        );

	        // return $this->redirectToRoute('activityIdea');
	    }

	    else if($form->isSubmitted() && !$form->isValid())
	    {
	    	$this->addFlash(
	            'error',
	            'Error !'
	        );
	    }

		return $this->render('ActivitiesBundle::formActivityIdea.html.twig', array(
				'form' => $form->createView(),
			));
	}

	/**
	* @Route("activitiesVote", name="activitiesVote")
	*/
	public function VoteAction(Request $request){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response("Erreur");
        }

		$em = $this->getDoctrine()->getManager();
	    $repositoryActivityVote = $this->getDoctrine()->getRepository('ActivitiesBundle:ActivitiesVote');

		$id = $request->request->get("id");
		$date = $request->request->get("horaire");

		$date2 = \DateTime::createFromFormat('Y-m-d H:i:s', $date);

		$activityVote = $repositoryActivityVote->findOneBy(array(
				'activity' => $id,
				'date' => $date2,
			));

		$vote = $activityVote->getVote();
		$activityVote->setVote($vote + 1);

		$em->persist($activityVote);
	    $em->flush();

		return $this->redirectToRoute('showActivitiesVote');
	}

	/**
	* @Route("showActivityLike", name="showActivityLike")
	*/
	public function showActivityLikeAction()
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response("Erreur");
        }

		$request = $this->container->get('request');

		if($request->isXmlHttpRequest())
    	{
    		$activity_id = $request->query->get('activity_id');
    		$date = $request->query->get('date');

			$date2 = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
			$repositoryActivityVote = $this->getDoctrine()->getRepository('ActivitiesBundle:ActivitiesVote');
			$activityVote = $repositoryActivityVote->findOneBy(array(
				'activity' => $activity_id,
				'date' => $date2,
			));

			$votes = $activityVote->getVote();

			return $this->container->get('templating')->renderResponse('ActivitiesBundle::test.html.twig', array(
            	'votes' => $votes,
            ));
        }

        return new Response("Erreur");
			
	}

	/**
	* @Route("showUsersRegistered", name="showUsersRegistered")
	*/
	public function showUsersRegisteredAction(){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response("Erreur");
        }

		$request = $this->container->get('request');

		if($request->isXmlHttpRequest())
    	{
    		$activity_id = $request->query->get('activity_id');
		
			$repositoryActivities = $this->getDoctrine()->getRepository('ActivitiesBundle:Activity');
			$activity = $repositoryActivities->find($activity_id);

			$activitiesUsers = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityUser');
			$activityUser = $activitiesUsers->findByActivity($activity_id);

			$date = $activity->getDate();

			$usersInfo = [];

			foreach($activityUser as $user)
			{
				$usersInfo[] = array($user->getUser()->getNom(), $user->getUser()->getPrenom(), $user->getUser()->getPromotion(), "Aucun", $user->getUser()->getEmail());
			}

			$table = [];

			foreach ($usersInfo as $user) {
				$table[] = 	"<tr>
								<td>".$user[0]."</td>
								<td>".$user[1]."</td>
								<td>".$user[2]."</td>
								<td>".$user[3]."</td>
								<td>".$user[4]."</td>
							</tr>";
			}

			$data = [$date, count($activityUser), $table];

			$response = new Response(json_encode($data));
		    $response->headers->set('Content-Type', 'application/json');

		    return $response;
        }

        return new Response("Erreur");
	}

	/**
	* @Route("showPhotoGallery", name="showPhotoGallery")
	*/
	public function showPhotoGalleryAction(){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') || !$this->container->get('security.authorization_checker')->isGranted('ROLE_TUTEUR')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

		$photos = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityPhoto')->findAll();

		return $this->render('ActivitiesBundle::moderationPhotos.html.twig', array(
			"photos" => $photos
			));
	}

	/**
	* @Route("deletephoto", name="deletePhoto")
	*/
	public function deletePhotoAction(Request $request){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') || !$this->container->get('security.authorization_checker')->isGranted('ROLE_TUTEUR')) {
            return new Response("Erreur");
        }

		$photoASupprimer = $request->request->get('photo');
		
		foreach ($photoASupprimer as $photo) {
			$suppr = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityPhoto')->find($photo);
			$this->getDoctrine()->getManager()->remove($suppr);
		}

		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('showPhotoGallery');
	}

}
?>