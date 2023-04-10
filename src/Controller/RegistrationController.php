<?php

// src/Controller/RegistrationController.php
namespace App\Controller;

// ...
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        // ... e.g. get the user data from a registration form
        $user = new User();
        $email = $request->get('email');
        $password = $request->get('password');
        $name = $request->get('name');
        $username = $request->get('username');

        $user->setEmail($email);
        $user->setIsActive(1);
        $user->setName($name);
        $user->setUsername($username);
        // hash the password (based on the security.yaml config for the $user class)
        $password = $passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($password);
        $entityManager->persist($user);
        $entityManager->flush();

        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'message' => 'User registered successfully!',
        ]);
        // ...
    }
}