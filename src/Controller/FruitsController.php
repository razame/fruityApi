<?php

namespace App\Controller;

use App\Entity\Fruit;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class FruitsController extends AbstractController
{

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/api/fruits', name: 'fruit_list')]
    public function list(Request $request, EntityManagerInterface $entityManager)
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 10);
        $familyFilter = $request->query->getAlpha('family', '');
        $nameFilter = $request->query->getAlpha('name', '');

        $repository = $entityManager->getRepository(Fruit::class);

        // build the query for the doctrine paginator
        $query = $repository->createQueryBuilder('f');

        if ($familyFilter !== '') {
            $query = $query->andWhere('f.family = :familyFilter')->setParameter('familyFilter', $familyFilter);
        }

        if($nameFilter !== ''){
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

        return $this->json([
            'fruits'        => $this->serializer->normalize($fruits),
            'totalPages'    => $pagesCount
        ]);
    }


    #[Route('/save-favorite-fruit', name: 'save_favorite_fruit')]
    public function saveFavoriteFruit(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(User::class)->find(1);
        $fruit = $entityManager->getRepository(Fruit::class)->find(11);

        $favoriteFruits = $user->getFavoriteFruits();

        if(count($favoriteFruits) === 10){
            return $this->json([
                'message' => 'Your basket is full of your favorite fruits!',
            ]);
        }

        $user->getFavoriteFruits()->add($fruit);

        $entityManager->flush();

        return $this->json([
            'message' => $fruit->getName().' added to your basket of favorite fruits successfully!',
        ]);
    }

    #[Route('/remove-favorite-fruit', name: 'remove_favorite_fruit')]
    public function removeFavoriteFruit(Request $request, EntityManagerInterface $entityManager)
    {
        $fruitId = $request->query->getInt('fruit_id');
        $user = $entityManager->getRepository(User::class)->find(1);
        $fruit = $entityManager->getRepository(Fruit::class)->find($fruitId);

        $favoriteFruits = $user->getFavoriteFruits();

        if(count($favoriteFruits) === 0){
            return $this->json([
                'message' => 'Your basket of favorite fruits is already empty!',
            ]);
        }

        $user->getFavoriteFruits()->removeElement($fruit);

        $entityManager->flush();

        return $this->json([
            'message' => $fruit->getName().' removed from your basket of favorite fruits successfully!',
        ]);
    }

}
