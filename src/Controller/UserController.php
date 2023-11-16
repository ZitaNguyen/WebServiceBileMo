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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUserList(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        Request $request,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $cachedData = $cache->getItem('user_list_cache'); // Define a unique cache key

        if (!$cachedData->isHit()) {
            // If cache miss, retrieve the data and store it in the cache
            $userList = $userRepository->findBy(['client' => $this->getUser()]);
            $limit = $_GET['limit'] ?? 5;// Limit per page
            $userList = $paginator->paginate(
                $userList, // Query data
                $request->query->getInt('page', 1), // Page parameter
                $limit // Limit per page
            );

            $jsonUserList = $serializer->serialize($userList, 'json');

            if ($jsonUserList === "[]") {
                $cachedData->set(['message' => 'Pas résultats sur cette page.']);
            } else {
                $cachedData->set($jsonUserList);
            }

            $cache->save($cachedData);
        } else {
            // If cache hit, retrieve the data from the cache
            $jsonUserList = $cachedData->get();
        }

        if ($jsonUserList === "[]")
            return new JsonResponse(['message' => 'Pas résultats sur cette page.'], Response::HTTP_OK);

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
