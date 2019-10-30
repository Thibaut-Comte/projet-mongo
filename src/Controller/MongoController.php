<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

class MongoController
{
    /**
     * @Route("/mongoTest", methods={"GET"})
     * @param DocumentManager $dm
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function mongoTest(DocumentManager $dm)
    {
        $user = new User();
        $user->setEmail("cocofe@cocofe.fr");
        $user->setFirstname("cocofe");
        $user->setLastname("cocofe");
        $user->setPassword(md5("cocofe"));

        $dm->persist($user);
        $dm->flush();
        return new JsonResponse(array('Status' => 'OK'));
    }

    /**
     * @Route("/get-user", methods={"GET"})
     * @param DocumentManager $dm
     * @return void
     */
    public function getUser(DocumentManager $dm)
    {
        $users = $dm->getRepository(User::class)->findAll();
        dump($users);die;
    }
}