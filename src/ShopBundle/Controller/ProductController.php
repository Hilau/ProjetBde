<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShopBundle\Entity\Basket;
use ShopBundle\Entity\Category;

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
	public function showAcceuilShopAction(Request $request){
		$listArticle = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findAll();
		$user = $this->get('security.context')->getToken()->getUser();
		$products = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Basket')->findByUser($user);
		$productRepository = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product');
		$productsInfo = [];

		$categories = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findAll();

		/*$product_id = $request->request->get("product_id");
		$newProduct = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->find($product_id);
		$newProduct = new Basket();
		$newProduct->setProduct($product_id);
		$em = $this->getDoctrine()->getManager();
		$em->persist($newProduct);
		$em->flush();*/

			
		foreach($products as $product)
		{
			$productData = $productRepository->find($product);
			$productsInfo[] = [
				"name" => $productData->getName(),
				"price" => $productData->getPrice()
			];
		}

		return $this->render('ShopBundle::shopAcceuil.html.twig', array(
			'listArticle' => $listArticle,
			'productRepository' => $productRepository,
			'productsInfo' => $productsInfo,
			'categories' => $categories
			));
	}

	/**
	 * @Route("categoryShop/{category_id}", name="categorieShopShow")
	 */
	public function showCategoryShopAction(Request $request, $category_id){
		$listArticle = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findByCategory($category_id);
		$categories = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findAll();

		return $this->render('ShopBundle::shopCategory.html.twig', array(
			'listArticle' => $listArticle,
			'categories' => $categories
			));
	}



}

?>