<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ShopBundle\Entity\Basket;
use ShopBundle\Entity\Product;
use ShopBundle\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller 
{
	/**
	 * @Route(
	 *			"product/{product_id}",
	 *			name="productShow",
	 * 			defaults={"product_id": 1},
     *     		requirements={     *        		
     *         		"product_id": "\d+"
     *			}
     *		)
	 */
	public function showProductAction($product_id){
		$product = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->find($product_id);

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

		$dateActuelle = new \DateTime("now");
		$interval = clone($dateActuelle);
		$interval->sub(new \DateInterval('P7D'));
		$nouveaute = [];
		$articlePopulaire = [];

		$chaineDescription = [];

		$query = $productRepository->createQueryBuilder('a')                            
                            ->orderBy('a.nbAchat', 'DESC')
                            ->getQuery();

        $bestOfProduct = $query->setMaxResults(2)->getResult();

		foreach ($listArticle as $article) {
			$dateArticle = $article->getDate();
			if($dateArticle >= $interval && $dateArticle <= $dateActuelle)
			{
				$nouveaute[] = $article;
			}
			$chaineDescription[$article->getId()] = substr($article->getDescription(), 0, 35);
		}

		return $this->render('ShopBundle::shopAcceuil.html.twig', array(
			'nouveaute' => $nouveaute,
			'productRepository' => $productRepository,
			'productsInfo' => $productsInfo,
			'categories' => $categories,
			'bestOfProduct' => $bestOfProduct,
			'chaineDescription' => $chaineDescription
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

	/**
	 * @Route("editShop", name="editShop")
	 */
	public function editShopAction(){
		$products= $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findAll();
		$categories= $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findAll();

		return $this->render('ShopBundle::editShop.html.twig', array(
			'products' => $products,
			'categories' => $categories
			));
	}

	/**
	 * @Route("addProduct", name="addProduct")
	 */
	public function addProductAction(Request $request){
		$products= $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findAll();
		$categories= $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findAll();
		$em = $this->getDoctrine()->getManager();
		$product = new Product();

		$form = $this->createFormBuilder($product)
	        ->add('name', TextType::class)
	        ->add('description', TextareaType::class)
	        ->add('price', MoneyType::class)
	        ->add('category', EntityType::class, array(
	        	'class' => 'ShopBundle:Category',
	        	'choice_label' => 'name',
	        	'multiple' => false,
	        	'expanded' => false
	        	))
	        ->add('image', TextType::class)
	        ->getForm();
	        
		$form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {
	       	$product = $form->getData();

	       	$category = $product->getCategory();
	       	$idCat = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findOneByName($category->getName());
	       	
	       	$product->setDate(new \DateTime('now'));
	       	$product->setNbAchat(0);

	       	$product->setCategory($idCat);
	        $em->persist($product);
	        $em->flush();

	        $this->addFlash('success', 'Votre produit a été ajouté');
	    } else if($form->isSubmitted() && !$form->isValid()) {
	    	$this->addFlash('error', 'Votre produit n\'est pas valide');
	    }

	
		return $this->render('ShopBundle::addProduct.html.twig', array(
			'products' => $products,
			'categories' => $categories,
			'form'=> $form->createView()
			));
	}

	/**
	 * @Route("updateProduct/{id}", name="updateProduct")
	 */
	public function updateProductAction(Request $request, $id){
		$em = $this->getDoctrine()->getManager();
		$product = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findOneById($id);

		$form = $this->createFormBuilder($product)
	        ->add('name', TextType::class)
	        ->add('description', TextareaType::class)
	        ->add('price', MoneyType::class)
	        ->add('category', EntityType::class, array(
	        	'class' => 'ShopBundle:Category',
	        	'choice_label' => 'name',
	        	'multiple' => false,
	        	'expanded' => false
	        	))
	        ->add('image', TextType::class)
	        ->getForm();
	        
		$form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {
	       	$product = $form->getData();

	       	$category = $product->getCategory();
	       	$idCat = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Category')->findOneByName($category->getName());
	       	
	       	$product->setDate(new \DateTime('now'));
	       	$product->setNbAchat($product->getNbAchat());

	       	$product->setCategory($idCat);
	        $em->flush();

	        $this->addFlash('success', 'Votre produit a été modifié');
	    } else if($form->isSubmitted() && !$form->isValid()) {
	    	$this->addFlash('error', 'Votre produit n\'est pas valide');
	    }

	
		return $this->render('ShopBundle::updateProduct.html.twig', array(
			'product' => $product,
			'form'=> $form->createView()
			));
	}

	/**
	* @Route("deleteProduct/{id}", name="deleteProduct")
	*/
	public function deletePhotoAction(Request $request, $id){
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') || !$this->container->get('security.authorization_checker')->isGranted('ROLE_TUTEUR')) {
            return new Response("Erreur");
        }
		
		$suppr = $this->getDoctrine()->getManager()->getRepository('ShopBundle:Product')->findOneById($id);
		$this->getDoctrine()->getManager()->remove($suppr);

		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('editShop');
	}

	/**
	* @Route("addToBasket", name="addToBasket")
	*/
	public function addToBaskettAction()
	{
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response("Erreur");
        }

		$request = $this->container->get('request');
		$em = $this->getDoctrine()->getManager();

		if($request->isXmlHttpRequest())
    	{
    		$product_id = $request->query->get('product_id');
    		$user = $this->get('security.context')->getToken()->getUser();
    	
    		$repositoryBasket = $this->getDoctrine()->getRepository('ShopBundle:Basket');

    		$repositoryProduct = $this->getDoctrine()->getRepository('ShopBundle:Product');
    		$product = $repositoryProduct->find($product_id);

    		$basketProduct = $repositoryBasket->findBy(array(
				"user" => $user,
				"product" => $product,
			));

			$data = ["erreur" => "Le produit est déjà dans votre panier"];

			if(count($basketProduct) == 0)
			{
	    		$basket = new Basket();
	    		$basket->setUser($user);
	    		$basket->setProduct($product);

	    		$em->persist($basket);
	    		$em->flush();

	    		$data = ["name" => $product->getName(), "price" => $product->getPrice(), "erreur" => ""];
	    	}

	    	$response = new Response(json_encode($data));
			$response->headers->set('Content-Type', 'application/json');

			return $response;

        }

        else
        {
        	return new Response("Erreur");
        }
	}

}

?>