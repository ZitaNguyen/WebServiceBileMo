<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProductList(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        Request $request,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        $productList = $productRepository->findAll();
        $productList = $paginator->paginate(
            $productList, // Query data
            $request->query->getInt('page', 1), // Page parameter
            5 // Limit per page
        );
        $jsonProductList = $serializer->serialize($productList, 'json');

        if ($jsonProductList === "[]")
            return new JsonResponse(['message' => 'Pas résultats sur cette page.'], Response::HTTP_OK);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getSingleProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
   }
}
