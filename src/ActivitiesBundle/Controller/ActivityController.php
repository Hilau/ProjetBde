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
use ActivitiesBundle\Entity\ActivityIdea;
use ActivitiesBundle\Entity\ActivityUser;
use ActivitiesBundle\Entity\ActivitiesVote;
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
	 * @Route("activity", name="activityShow")
	 */
	public function showActivityAction(Request $request){
		$id = $request->request->get("id");

		$activity = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->find($id);
		$photos = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityPhoto')->findBy(array('activity'=>$activity));

		

		$em = $this->getDoctrine()->getManager();
	    $activityUser = new ActivityUser();
	    $user = $this->get('security.context')->getToken()->getUser();

		$form = $this->createFormBuilder($activityUser)
	        ->add('user', TextType::class)
	        ->add('activity', TextType::class)
	        ->add('save', 'submit')
	        ->getForm();
	        
		$form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {	        
	        $activityUser = $form->getData();
	        $activityUser->setUser($user);
	       	        
	        $em->persist($activityUser);
	        $em->flush();


	        $this->addFlash(
	            'success',
	            'Vous êtes inscrit !'
	        );

	        // return $this->redirectToRoute('activityShow');
	    }

	    else if($form->isSubmitted() && !$form->isValid())
	    {
	    	$this->addFlash(
	            'error',
	            'Error !'
	        );
	    }
			

		return $this->render('ActivitiesBundle::activity.html.twig', array(
			'titre'=> $activity->getName(),
			'description' => $activity->getDescription(),
			//'date' => DateTime::format ( $activity->getDate() ),
			'vote' =>$activity->getVote(),
			'photos' => $photos,
			'form' => $form->createView(),
			));
	}

	/**
	* @Route("showActivitiesVote", name="showActivitiesVote")
	*/
	public function showActivitiesVoteAction(){
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
	public function showSummaryActivityAction(){
		$activities = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->findAll();
		return $this->render('ActivitiesBundle::summaryActivity.html.twig', array(
			"activities" => $activities
			));
	}

	/**
	* @Route("activityIdea", name="activityIdea")
	*/
	public function formActivityIdeaAction(Request $request)
	{
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
	* @Route("showPhotoGallery", name="showPhotoGallery")
	*/
	public function showPhotoGalleryAction(){
		$photos = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityPhoto')->findAll();

		return $this->render('ActivitiesBundle::moderationPhotos.html.twig', array(
			"photos" => $photos
			));
	}

	/**
	* @Route("deletephoto", name="deletePhoto")
	*/
	public function deletePhotoAction(Request $request){

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