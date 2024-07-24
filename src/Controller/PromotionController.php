<?php

namespace App\Controller;

use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PromotionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/promotions/custom', name: 'custom_promotions', methods: ['GET'])]
    public function customAction(): JsonResponse
    {
        $repository = $this->entityManager->getRepository(Promotion::class);
        $promotions = $repository->findBy(['discount' => 20]);

        return $this->json($promotions);
    }

    #[Route('/api/promotions', name: 'create_promotion', methods: ['POST'])]
    public function createPromotion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $promotion = new Promotion();
        $promotion->setName($data['name']);
        $promotion->setDescription($data['description']);
        $promotion->setDiscount($data['discount']);
        $promotion->setStartDate(new \DateTime($data['startDate']));
        $promotion->setEndDate(new \DateTime($data['endDate']));

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();

        return $this->json($promotion, JsonResponse::HTTP_CREATED);
    }
}
