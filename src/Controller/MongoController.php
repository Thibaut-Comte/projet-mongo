<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MongoController
{
    /**
     * @Route("/mongoTest", methods={"GET"})
     * @param DocumentManager $dm
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function mongoTest(DocumentManager $dm, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $user->setEmail("cocofe@cocofe.fr");
        $user->setUsername("cocofe@cocofe.fr");
        $user->setFirstname("cocofe");
        $user->setLastname("cocofe");
        $encode = $encoder->encodePassword($user, 'cocofe');
        $user->setPassword($encode);

        $dm->persist($user);
        $dm->flush();
        return new JsonResponse(array('Status' => 'OK'));
    }

    /**
     * @Route("/get-user", methods={"GET"})
     * @param DocumentManager $dm
     * @return Response
     */
    public function getUser(DocumentManager $dm)
    {
        $users = $dm->getRepository(User::class)->findAll();
        return new Response('<html><body></body></html>');
    }
}