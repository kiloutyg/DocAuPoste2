<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Event\SecurityEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SecurityController extends BaseController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('success', 'You have been logged in');
            return $this->redirectToRoute('front_index');
        }

        $error        = $authenticationUtils->getLastAuthenticationError(); // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'You have been logged out');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/create_account', name: 'app_create_account')]
    public function create_account(UserPasswordHasherInterface $passwordHasher, AuthenticationUtils $authenticationUtils, Request $request, UserRepository $userRepository, EntityManagerInterface $manager, SessionRepository $sessionRepository): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // catch the form data with POST method
        if ($request->getMethod() == 'POST') {
            $name     = $request->request->get('name');
            $password = $request->request->get('password');

            // check if the email is already in use
            $user = $userRepository->findOneBy(['name' => $name]);
            if ($user) {
                $error = 'The email is already in use';
            } else {
                // create the user
                $user     = new User();
                $password = $passwordHasher->hashPassword($user, $password);
                $user->setName($name);
                $user->setHashedPassword($password);
                $manager->persist($user);
                $manager->flush();

                $this->authenticateUser($user);
                $this->addFlash('success', 'Your account has been created');

                return $this->redirectToRoute('front_index');
            }
        }

        return $this->render('security/create_account.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token       = new UsernamePasswordToken($user, $providerKey, $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
    }
}