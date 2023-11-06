<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUserList(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $client = $this->getUser();
        $userList = $userRepository->findAllWithPagination($page, $limit, $client);
        $jsonUserList = $serializer->serialize($userList, 'json');

        return new JsonResponse($jsonUserList, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    public function getSingleUser(User $user, SerializerInterface $serializer): JsonResponse
    {
         // check right to access
         $this->denyAccessUnlessGranted(UserVoter::ACCESS, $user);

         $jsonUser = $serializer->serialize($user, 'json');
         return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
   }

    #[Route('/api/users', name: 'addUser', methods: ['POST'])]
    public function addUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setClient($this->getUser());
        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['accept' => 'json'], true);
   }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        // check right to access
        $this->denyAccessUnlessGranted(UserVoter::ACCESS, $user);

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }

}
