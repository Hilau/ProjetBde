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

}



?>