<?php

namespace ActivitiesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ActivityController extends Controller 
{
	/**
	 * @Route("all", name="activitiesShow")
	 */
	public function showAllAction(){
		return $this->render('ActivitiesBundle::listActivity.html.twig');
	}

	/**
	 * @Route("activity", name="activityShow")
	 */
	public function showActivityAction(){
		return $this->render('ActivitiesBundle::activity.html.twig');
	}

	/**
	* @Route("vote", name="activitiesVote")
	*/
	public function showActivitiesVoteAction(){
		return $this->render('ActivitiesBundle::listActivityVote.html.twig');
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
	public function formActivityIdeaAction(){
		return $this->render('ActivitiesBundle::formActivityIdea.html.twig');
	}

}



?>