<?php

namespace App\Controller;

use App\Repository\UserRepository;
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

//     #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
//     public function getSingleProduct(Product $product, SerializerInterface $serializer): JsonResponse {

//         $jsonProduct = $serializer->serialize($product, 'json');
//         return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
//    }
}
