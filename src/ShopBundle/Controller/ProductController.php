<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProductController extends Controller 
{
	/**
	 * @Route("product", name="productShow")
	 */
	public function showProductAction(){
		return $this->render('ShopBundle::product.html.twig');
	}

	/**
	 * @Route("acceuilShop", name="acceuilShopShow")
	 */
	public function showAcceuilShopAction(){
		return $this->render('ShopBundle::shopAcceuil.html.twig');
	}

	/**
	 * @Route("categoryShop", name="categorieShopShow")
	 */
	public function showCategoryShopAction(){
		return $this->render('ShopBundle::shopCategory.html.twig');
	}

}

?>