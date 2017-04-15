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
	* @Route("activityToVote", name="activitiesVote")
	*/
	public function showActivitiesVoteAction(){
		$listActivitiesVote = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:ActivityIdea')->findAll();


		return $this->render('ActivitiesBundle::listActivityVote.html.twig', array(
			'listActivitiesVote' => $listActivitiesVote));
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

	        $this->addFlash(
	            'success',
	            'Votre idée d\'activité a bien été soumise !'
	        );

	        return $this->redirectToRoute('activityIdea');
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
	* @Route("vote", name="vote")
	*/
	public function VoteAction(Request $request){
		$id = $request->request->get("id");
		$activity = $this->getDoctrine()->getManager()->getRepository('ActivitiesBundle:Activity')->find($id);

		$activity->setVote($activity->getVote()+1);

		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('activitiesVote');
	}

}



?>