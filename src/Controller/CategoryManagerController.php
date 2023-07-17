<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\AccountService;
use App\Service\UploadsService;
use App\Service\IncidentsService;


use App\Entity\Button;

# This controller manage the logic of the category's admin interface

class CategoryManagerController extends BaseController
{

    #[Route('/category_manager/{category}', name: 'app_category_manager')]

    # This function is responsible for rendering the category's admin interface
    public function index(IncidentsService $incidentsService, UploadsService $uploadsService, string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'category',
            $category->getId()
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'category',
            $category->getId()
        );
        $groupedUploads = $uploadsService->groupUploads($uploads);
        $groupIncidents = $incidentsService->groupIncidents($incidents);





        return $this->render('category_manager/category_manager_index.html.twig', [
            'groupedUploads'    => $groupedUploads,
            'groupincidents'    => $groupIncidents,
            'zone'              => $zone,
            'productLine'       => $productLine,
            'category'          => $category,
            'buttons'           => $this->buttonRepository->findAll(),
            'uploads'           => $this->uploadRepository->findAll(),
            'users'             => $this->userRepository->findAll(),
            'incidents'         => $incidents,
            'incidentCategories' => $this->incidentCategoryRepository->findAll(),

        ]);
    }



    #[Route('/category_manager/create_user/{category}', name: 'app_category_manager_create_user')]

    # This function is responsible for creating a new user, it's access is restricted on the frontend
    public function createUser(string $category = null, AccountService $accountService, Request $request): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);

        $error = null;
        $result = $accountService->createAccount(
            $request,
            $error,
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_category_manager', [
            'category'    => $category->getName(),
        ]);
    }


    #[Route('/category_manager/create_button/{category}', name: 'app_category_manager_create_button')]

    # This function is used to create a new button to which is attached the uploads.
    public function createButton(Request $request, string $category = null)
    {
        $categoryentity    = $this->categoryRepository->findoneBy(['name' => $category]);
        // Check if button name does not contain the disallowed characters
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {
            // Handle the case when button name contains disallowed characters
            $this->addFlash('danger', 'Nom de bouton invalide');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {

            // Handle the case when button name does not contain disallowed characters
            // Create a button

            $buttonname = $request->request->get('buttonname') . '.' . $categoryentity->getName();

            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);

            if ($button) {
                $this->addFlash('danger', 'Le bouton existe déjà');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
            } else {
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($categoryentity);
                $this->em->persist($button);
                $this->em->flush();
                $this->folderCreationService->folderStructure($buttonname);

                $this->addFlash('success', 'Le bouton a été créé');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
            }
        }
    }

    #[Route('/category_manager/delete_button/{button}', name: 'app_category_manager_delete_button')]

    # This function is used to delete a button and all the uploads attached to it.
    public function deleteEntity(string $button): Response
    {
        $entityType = 'button';
        $entityid = $this->buttonRepository->findOneBy(['name' => $button]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());


        $category = $entityid->getCategory()->getName();

        if ($entity == true) {

            $this->addFlash('success', 'Le bouton ' . $entityType . ' a été supprimé');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {
            $this->addFlash('danger', 'Le bouton ' . $entityType . ' n\'existe pas.');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        }
    }
}