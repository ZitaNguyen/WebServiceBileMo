<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use ErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findBy(['client' => $this->getUser()]);
        $jsonUserList = $serializer->serialize($userList, 'json');

        return new JsonResponse($jsonUserList, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    public function getSingleProduct(User $user, SerializerInterface $serializer): JsonResponse
    {
        if ($user->getClient() == $this->getUser()) {
            $jsonUser = $serializer->serialize($user, 'json');
            return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        throw new ErrorException("Vous ne pouvez pas accéder à cet utilisateur");
   }
}
