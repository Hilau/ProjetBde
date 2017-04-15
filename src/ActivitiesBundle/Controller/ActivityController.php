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
use ActivitiesBundle\Entity\ActivitiesVote;
use Doctrine\ORM\EntityRepository;

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
		
		return $this->render('ActivitiesBundle::activity.html.twig', array(
			'titre'=> $activity->getName(),
			'description' => $activity->getDescription(),
			//'date' => DateTime::format ( $activity->getDate() ),
			'vote' =>$activity->getVote(),
			'photos' => $photos
			));
	}

	/**
	* @Route("showActivitiesVote", name="showActivitiesVote")
	*/
	public function showActivitiesVoteAction(){
		$listActivitiesIdea = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityIdea')->findAll();
		$listActivitiesVote = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivitiesVote');

		$dates = [];

		foreach($listActivitiesIdea as $activity)
		{
			$activityDates = $listActivitiesVote->findByActivity($activity);

			foreach($activityDates as $date)
			{
				$dates[$activity->getId()][] = $date->getDate();
			}
		}

		return $this->render('ActivitiesBundle::listActivityVote.html.twig', array(
				'listActivitiesIdea' => $listActivitiesIdea,
				'dates' => $dates

			));
	}

	/**
	* @Route("summary", name="summaryActivity")
	*/
	public function showSummaryActivityAction(){
		return $this->render('ActivitiesBundle::summaryActivity.html.twig');
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

}
?>