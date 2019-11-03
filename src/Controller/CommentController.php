<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 01/11/2019
 * Time: 19:07
 */

namespace App\Controller;


use App\Document\Comment;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/comment")
 * Class CommentController
 * @package App\Controller
 */
class CommentController extends AbstractController
{

    /**
     * @Route("/delete/{commentId}/{productId}")
     * @param Request $request
     * @param $commentId
     * @param $productId
     * @param DocumentManager $dm
     * @return RedirectResponse|AccessDeniedException
     * @throws MongoDBException
     */
    public function delete(Request $request, $commentId, $productId, DocumentManager $dm)
    {
        $token = $request->query->get('token');
        //TODO : Ne supprime pas pour moi

        if (!$this->isCsrfTokenValid('delete_comment', $token)) {
            return $this->createAccessDeniedException();
        }
        $product = $dm->getRepository(Comment::class)->find($commentId);
        $dm->remove($product);
        $dm->flush();
        $this->addFlash('success', 'Commentaire supprimé');
        return $this->redirectToRoute('app_product_show', array(
            'id' => $productId
        ));
    }

}