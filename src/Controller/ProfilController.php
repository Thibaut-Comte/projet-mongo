<?php
/**
 * Created by PhpStorm.
 * User: Thibaut
 * Date: 03/11/2019
 * Time: 13:58
 */

namespace App\Controller;


use App\Document\User;
use App\Form\ProfilType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfilController extends AbstractController
{

    /**
     * @Route("/signup", methods={"GET","POST"})
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder, DocumentManager $dm): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->redirectToRoute('app_product_all');
        }
        $user = new User();
        $form = $this->createForm(ProfilType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $encoded = $encoder->encodePassword($user, $user->getRawPassword());
            $user->setPassword($encoded);

            $dm->persist($user);
            $dm->flush();

            $this->addFlash('success', 'Vous pouvez désormais vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profil/signup.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/profil")
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, DocumentManager $dm, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form= $this->createForm(ProfilType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($user->getRawPassword()) {
                    $encoded = $encoder->encodePassword($user, $user->getRawPassword());
                    $user->setPassword($encoded);
                }

                $dm->flush();
                $this->addFlash('success', 'Votre compte a bien été modifié.');
            } catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur est survenue");
            }
            return $this->redirectToRoute('app_profil_edit', array(
                'id' => $user->getId(),
            ));
        }

        return $this->render('profil/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

}