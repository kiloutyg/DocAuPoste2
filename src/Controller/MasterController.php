<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

use App\Controller\AdminController;
use App\Controller\SecurityController;
use App\Service\AccountService;
use App\Repository\ZoneRepository;
use App\Entity\Zone;

class MasterController extends BaseController
{

    #[Route('/master', name: 'app_master')]

    public function index(AuthenticationUtils $authenticationUtils,): Response
    {
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('master/master_index.html.twig', [
            'controller_name' => 'MasterController',
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/master/create_admin', name: 'app_master_create_admin')]
    public function createAdmin(AccountService $accountService, Request $request): Response
    {

        // Use createAccount() function from AccountService

        $user = $accountService->createAccount($request, $error);

        if ($user) {
            // Handle the created user, for example, by redirecting to a specific route
            // return $this->redirectToRoute('some_route');

            $this->addFlash('success', 'account has been created');
            return $this->redirectToRoute('app_master');
        }
        return $this->redirectToRoute('app_master');
    }

    #[Route('/master/create_zone', name: 'app_master_create_zone')]
    public function createZone(Request $request)
    {
        // Create a zone
        if ($request->getMethod() == 'POST') {
            $zonename = $request->request->get('zonename');


            $zone = $this->zoneRepository->findOneBy(['name' => $zonename]);
            if ($zone) {
                $this->addFlash('danger', 'Zone already exists');
                return $this->redirectToRoute('app_master');
            } else {
                $zone = new Zone();
                $zone->setName($zonename);
                $this->em->persist($zone);
                $this->em->flush();
                $this->addFlash('success', 'Zone has been created');
                return $this->redirectToRoute('app_master');
            }
        }
    }
}