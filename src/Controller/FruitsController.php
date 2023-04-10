<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Fruit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FruitsController extends AbstractController
{

    private $serializer, $security;

    public function __construct(SerializerInterface $serializer, Security $security)
    {
        $this->serializer = $serializer;
        $this->security = $security;
    }

    #[Route('/api/fruits', name: 'fruit_list')]
    public function listOfFruits(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 10);
        $familyFilter = $request->get('family', '');
        $nameFilter = $request->get('name', '');

        $repository = $entityManager->getRepository(Fruit::class);

        // build the query for the doctrine paginator
        $query = $repository->createQueryBuilder('f');

        if ($familyFilter !== '') {
            $query = $query->andWhere('f.family = :familyFilter')->setParameter('familyFilter', $familyFilter);
        }

        if ($nameFilter !== '') {
            $query = $query->andWhere('f.name = :nameFilter')->setParameter('nameFilter', $nameFilter);
        }

        $query = $query->orderBy('f.id', 'ASC')
            ->getQuery();

        // load doctrine Paginator
        $paginator = new Paginator($query);

        // you can get total items
        $totalItems = count($paginator);

        // get total pages
        $pagesCount = ceil($totalItems / $pageSize);

        // now get one page's items:
        $fruits = $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page-1)) // set the offset
            ->setMaxResults($pageSize) // set the limit
            ->getResult();

        foreach ($fruits as $fruit){
            $user = $entityManager->getRepository(User::class)->find($this->security->getUser()->getId());
            $fruit->isFavorite = $user->getFavoriteFruits()->contains($fruit);
        }

        return $this->json([
            'fruits'        => $this->serializer->normalize($fruits),
            'totalPages'    => $pagesCount
        ]);
    }


    #[Route('/api/save-favorite-fruit', name: 'save_favorite_fruit')]
    public function saveFavoriteFruit(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $fruitId = $request->get('fruit_id');

        if (!$fruitId) {
            return $this->json([
                'message' => 'Please provide fruit_id so we can add that to your basket of favorites!',
            ], 400);
        }


        $fruit = $entityManager->getRepository(Fruit::class)->find($fruitId);

        if (!$fruit) {
            return $this->json([
                'message' => 'Requested fruit not found!',
            ], 404);
        }

        $user = $this->security->getUser();

        if (empty($user)) {
            return $this->json([
                'message' => 'You are not authenticated!',
            ], 401);
        }

        $user = $entityManager->getRepository(User::class)->find($user->getId());

        $favoriteFruits = $user->getFavoriteFruits();

        if(count($favoriteFruits) === 10){
            return $this->json([
                'message' => 'Your basket is full of your favorite fruits!',
            ], 400);
        }

        if( $favoriteFruits->contains($fruit) ){
            return $this->json([
                'message' => $fruit->getName().' is already in your basket of favorites!',
            ], 400);
        }

        $user->getFavoriteFruits()->add($fruit);

        $entityManager->flush();

        return $this->json([
            'message' => $fruit->getName().' added to your basket of favorite fruits successfully!',
        ]);
    }

    #[Route('/api/remove-favorite-fruit', name: 'remove_favorite_fruit')]
    public function removeFavoriteFruit(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $fruitId = $request->get('fruit_id');

        if(!$fruitId) {
            return $this->json([
                'message' => 'Please provide fruit_id so we can remove that from your basket of favorites!',
            ], 400);
        }


        $fruit = $entityManager->getRepository(Fruit::class)->find($fruitId);

        if (!$fruit) {
            return $this->json([
                'message' => 'Requested fruit not found!',
            ], 404);
        }

        $user = $this->security->getUser();

        if (empty($user)) {
            return $this->json([
                'message' => 'You are not authenticated!',
            ], 401);
        }

        $user = $entityManager->getRepository(User::class)->find($user->getId());


        $favoriteFruits = $user->getFavoriteFruits();

        if (!$favoriteFruits->contains($fruit)) {
            return $this->json([
                'message' => $fruit->getName().' is already NOT in your basket of favorites!',
            ], 400);
        }

        if (count($favoriteFruits) === 0) {
            return $this->json([
                'message' => 'Your basket of favorite fruits is already empty!',
            ], 400);
        }

        $user->getFavoriteFruits()->removeElement($fruit);

        $entityManager->flush();

        return $this->json([
            'message' => $fruit->getName().' removed from your basket of favorite fruits successfully!',
        ]);
    }

}
