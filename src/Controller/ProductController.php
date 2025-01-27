<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\AlzaParserService;
use App\Validator\ProductRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    private ProductRequestValidator $validator;

    private EntityManagerInterface $em;

    public function __construct(ProductRequestValidator $validator, EntityManagerInterface $em)
    {
        $this->validator = $validator;
        $this->em = $em;
    }

    #[Route('', name: 'api_products_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $data = array_map(function (Product $product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'photo' => $product->getPhoto(),
                'description' => $product->getDescription(),
            ];
        }, $products);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_products_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'photo' => $product->getPhoto(),
            'description' => $product->getDescription(),
        ]);
    }

    #[Route('', name: 'api_products_create', methods: ['POST'])]
    public function create(
        Request $request
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $errors = $this->validator->validate($data, 'create');

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setPhoto($data['photo']);
        $product->setDescription($data['description']);

        $this->em->persist($product);
        $this->em->flush();

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'photo' => $product->getPhoto(),
            'description' => $product->getDescription(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_products_update', methods: ['PUT'])]
    public function update(
        Request $request,
        Product $product,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $errors = $this->validator->validate($data, 'update');

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $product->setName($data['name'] ?? $product->getName());
        $product->setPrice($data['price'] ?? $product->getPrice());
        $product->setPhoto($data['photo'] ?? $product->getPhoto());
        $product->setDescription($data['description'] ?? $product->getDescription());

        $em->flush();

        return $this->json(['message' => 'Product updated']);
    }

    #[Route('/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return $this->json(['message' => 'Product deleted']);
    }

    #[Route('/import', name: 'api_products_import', methods: ['POST'])]
    public function importFromAlza(
        Request $request,
        AlzaParserService $parser,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['url'])) {
            return $this->json(['error' => 'URL is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $productData = $parser->parseProduct($data['url']);

            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);
            $product->setPhoto($productData['photo']);
            $product->setDescription($productData['description'] ?? '');

            $em->persist($product);
            $em->flush();

            return $this->json([
                'message' => 'Product imported successfully',
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'photo' => $product->getPhoto(),
                    'description' => $product->getDescription(),
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json(['error sd' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
