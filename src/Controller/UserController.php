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
use Hateoas\HateoasBuilder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use OpenApi\Annotations as OA;


class UserController extends AbstractController
{
    /**
     * Function to get a list of users which are registered by the client logged in
     */
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUserList(
        UserRepository $userRepository,
        Request $request,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $userList = $cache->get('user_list_cache_key', function (ItemInterface $item) use ($userRepository) {
            $item->expiresAfter(3600); // Cache expires after 1 hour
            return $userRepository->findBy(['client' => $this->getUser()]);
        });

        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 5;// Limit per page
        $userList = $paginator->paginate(
            $userList, // Query data
            $request->query->getInt('page', 1), // Page parameter
            $limit // Limit per page
        );

        $hateoas = HateoasBuilder::create()->build();
        $jsonUserList = $hateoas->serialize($userList, 'json');

        return new JsonResponse($jsonUserList, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Function to get a user by id
     */
    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    public function getSingleUser(User $user, SerializerInterface $serializer): JsonResponse
    {
         // check right to access
         $this->denyAccessUnlessGranted(UserVoter::ACCESS, $user);

         $jsonUser = $serializer->serialize($user, 'json');
         return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Function to add a new user
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         example={
     *              "firstName": "Toto",
     *              "lastName": "Tester",
     *              "email": "test@email.fr",
     *              "phone": "0623456789",
     *              "address": "123 rue de Test"
     *          },
     *          @OA\Schema (
     *              type="object",
     *              @OA\Property(property="firstName", required=true, description="Votre prénom", type="string"),
     *              @OA\Property(property="lastName", required=true, description="Votre nom", type="string"),
     *              @OA\Property(property="email", required=true, description="Votre email", type="string"),
     *              @OA\Property(property="phone", required=true, description="Votre téléphone", type="string"),
     *              @OA\Property(property="address", required=true, description="Votre address", type="string"),
     *          )
     *      )
     * )
     */
    #[Route('/api/users', name: 'addUser', methods: ['POST'])]
    public function addUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        try {
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');

            $user->setClient($this->getUser());
            $em->persist($user);
            $em->flush();

            $jsonUser = $serializer->serialize($user, 'json');

            return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['accept' => 'json'], true);
        } catch (\Exception $e) {
            // Handle exceptions, log errors, and return an appropriate error response
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Function to edit a user by id
     *
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         example={
     *              "firstName": "Toto",
     *              "lastName": "Tester",
     *              "email": "test@email.fr",
     *              "phone": "0623456789",
     *              "address": "123 rue de Test"
     *          },
     *          @OA\Schema (
     *              type="object",
     *              @OA\Property(property="firstName", required=true, description="Votre prénom", type="string"),
     *              @OA\Property(property="lastName", required=true, description="Votre nom", type="string"),
     *              @OA\Property(property="email", required=true, description="Votre email", type="string"),
     *              @OA\Property(property="phone", required=true, description="Votre téléphone", type="string"),
     *              @OA\Property(property="address", required=true, description="Votre address", type="string"),
     *          )
     *      )
     * )
     */
    #[Route('/api/users/{id}', name: 'editUser', methods: ['PUT'])]
    public function editUser(User $user, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        try {
            // check right to access
            $this->denyAccessUnlessGranted(UserVoter::ACCESS, $user);

            $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

            if ($newUser->getFirstName() !== null)
                $user->setFirstName($newUser->getFirstName());

            if ($newUser->getLastName() !== null)
                $user->setLastName($newUser->getLastName());

            if ($newUser->getEmail() !== null)
                $user->setEmail($newUser->getEmail());

            if ($newUser->getPhone() !== null)
                $user->setPhone($newUser->getPhone());

            if ($newUser->getAddress() !== null)
                $user->setAddress($newUser->getAddress());


            // $user->setClient($this->getUser());
            $em->persist($user);
            $em->flush();

            $jsonUser = $serializer->serialize($user, 'json');

            return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['accept' => 'json'], true);
        } catch (\Exception $e) {
            // Handle exceptions, log errors, and return an appropriate error response
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Function to delete a user by id
     */
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
