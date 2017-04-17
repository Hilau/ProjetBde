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
	public function showProductAction(Request $request){
		$id = $request->request->get("id");
		$product = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->find($id);
		return $this->render('ShopBundle::product.html.twig', array(
			'id' => $product->getId(),
			'name' => $product->getName(),
			'description' => $product->getDescription(),
			'price' => $product->getPrice()  
			));
	}

	/**
	 * @Route("acceuilShop", name="acceuilShopShow")
	 */
	public function showAcceuilShopAction(){
		$listArticle = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findAll();
		return $this->render('ShopBundle::shopAcceuil.html.twig', array('listArticle' => $listArticle));
	}

	/**
	 * @Route("categoryShop", name="categorieShopShow")
	 */
	public function showCategoryShopAction(){
		$listArticle = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findAll();
		return $this->render('ShopBundle::shopCategory.html.twig', array('listArticle' => $listArticle));
	}



}

?>