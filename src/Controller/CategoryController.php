<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 16:49
 */

namespace App\Controller;


use App\Document\Category;
use App\Form\CategoryType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category")
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{

    /**
     * @Route("/add")
     */
    public function addAction(DocumentManager $dm, Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $dm->persist($category);
            $dm->flush();
            return $this->redirectToRoute('app_category_all');
        }

        return $this->render('category/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/all")
     * @param DocumentManager $dm
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function all(DocumentManager $dm)
    {
        $categories = $dm->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * @Route("/{id}", methods={"GET"})
     * @param Category $category
     * @return Response
     */
    public function show(DocumentManager $dm, $id): Response
    {
        $category = $dm->getRepository(Category::class)->find($id);

        if (!$category) return $this->createNotFoundException();

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{id}/delete")
     * @param Request $request
     * @param Id
     * @return RedirectResponse|AccessDeniedException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function delete(Request $request, $id, DocumentManager $dm)
    {
        $token = $request->query->get('token');
        if (!$this->isCsrfTokenValid('delete_category', $token)) {
            return $this->createAccessDeniedException();
        }
        $category = $dm->getRepository(Category::class)->find($id);
        $dm->remove($category);
        $dm->flush();
        $this->addFlash('success', 'Catégorie supprimée');
        return $this->redirectToRoute('app_category_all');
    }

    /**
     * @Route("update/{id}")
     * @param $id
     * @param DocumentManager $dm
     */
    public function update($id, DocumentManager $dm, Request $request)
    {
        $category = $dm->getRepository(Category::class)->find($id);
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $dm->flush();
            return $this->redirectToRoute('app_category_show', array(
                'id' => $category->getId()
            ));
        }

        return $this->render('category/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

}