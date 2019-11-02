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
use App\Service\FileUploader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class ProductController
 * @package App\Controller
 * @Route("/product")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/")
     * @param DocumentManager $dm
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function all(DocumentManager $dm, PaginatorInterface $paginator, Request $request)
    {
        $categories = $dm->getRepository(Category::class)->findAll();
        $category = null;
        if ($request->query->get('category')) {
            $category = $dm->getRepository(Category::class)->findOneBy(['id' => $request->query->get('category')]);
        }
        $queryBuilder = $dm->getRepository(Product::class)->findAllByCategory($category);

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('product/index.html.twig', array(
            'pagination' => $pagination, 'categories' => $categories
        ));
    }

    /**
     * @Route("/add")
     * @param DocumentManager $dm
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     * @throws MongoDBException
     */
    public function create(DocumentManager $dm, Request $request, FileUploader $fileUploader)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
//            $date = new DateTime();
//            $product->setDateInsert($date);
            $imageFile = $form['image']->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $product->setImageFilename($imageFileName);
            }

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
     * @param DocumentManager $dm
     * @param $id
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function show(DocumentManager $dm, $id, Request $request): Response
    {
        $product = $dm->getRepository(Product::class)->find($id);

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @return RedirectResponse|AccessDeniedException
     * @throws MongoDBException
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
     * @return RedirectResponse|Response
     * @throws MongoDBException
     */
    public function update($id, DocumentManager $dm, Request $request)
    {
        $product = $dm->getRepository(Product::class)->find($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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