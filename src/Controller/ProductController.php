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
use Hateoas\HateoasBuilder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class ProductController extends AbstractController
{
    /**
     * Function to get a list of products
     */
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProductList(
        ProductRepository $productRepository,
        Request $request,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $productList = $cache->get('product_list_cache_key', function (ItemInterface $item) use ($productRepository) {
            $item->expiresAfter(3600); // Cache expires after 1 hour
            return $productRepository->findAll();
        });

        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 5;// Limit per page
        $productList = $paginator->paginate(
            $productList, // Query data
            $request->query->getInt('page', 1), // Page parameter
            $limit
        );
        $hateoas = HateoasBuilder::create()->build();
        $jsonProductList = $hateoas->serialize($productList, 'json');

        if ($jsonProductList === '[]')
            return new JsonResponse(['message' => 'Pas résultats sur cette page.'], Response::HTTP_OK);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    /**
     * Function to get a product by id
     */
    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getSingleProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
   }
}
