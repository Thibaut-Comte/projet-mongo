<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 14:42
 */

namespace App\Controller;


use App\Document\Category;
use App\Document\Comment;
use App\Document\Product;
use App\Form\CommentType;
use App\Form\ProductType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

class ProductController extends AbstractController
{

    /**
     * @Route("/")
     * @param DocumentManager $dm
     * @return Response
     */
    public function all(DocumentManager $dm)
    {
        $products = $dm->getRepository(Product::class)->findAll();


        return $this->render('product/index.html.twig', array(
            'products' => $products
        ));
    }

    /**
     * @Route("/add")
     * @param DocumentManager $dm
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function create(DocumentManager $dm, Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $date = new DateTime();
//            $product->setDateInsert($date);
            $product->setUser($this->getUser());
            $category = $dm->getRepository(Category::class)->find($request->request->get('product')['category']);
            $category->addProduct($product);
            $dm->persist($product);
            $dm->persist($category);
            $dm->flush();

            return $this->redirectToRoute('app_product_all');
        }

        return $this->render('product/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}", methods={"GET", "POST"})
     * @param Product $product
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function show(DocumentManager $dm, $id, Request $request): Response
    {
        $product = $dm->getRepository(Product::class)->find($id);

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setUser($this->getUser());
            $comment->setProduct($product);
            $dm->persist($comment);
            $dm->flush();

            return $this->redirectToRoute('app_product_show', array(
                'id' => $product->getId()
            ));
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}")
     * @param Request $request
     * @param $id
     * @param DocumentManager $dm
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function delete(Request $request, $id, DocumentManager $dm)
    {
        $token = $request->query->get('token');
        if (!$this->isCsrfTokenValid('delete_product', $token)) {
            return $this->createAccessDeniedException();
        }
        $product = $dm->getRepository(Product::class)->find($id);
        $dm->remove($product);
        $dm->flush();
        $this->addFlash('success', 'Produit supprimé');
        return $this->redirectToRoute('app_product_all');
    }


    /**
     * @Route("/update/{id}")
     * @param $id
     * @param DocumentManager $dm
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function update($id, DocumentManager $dm, Request $request)
    {
        $product = $dm->getRepository(Product::class)->find($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $dm->flush();
            return $this->redirectToRoute('app_product_show', array(
                'id' => $product->getId()
            ));
        }

        return $this->render('product/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

}