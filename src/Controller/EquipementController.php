<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Form\EquipementType;
use App\Repository\EquipementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class EquipementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/e', name: 'app_home')]
    public function accueil(): Response
    {
        return $this->render('equipement/home.html.twig'); 
    }



    #[Route('/equipement', name: 'app_equipement_index')]
    public function index(EquipementRepository $equipementRepository): Response
    {
        return $this->render('equipement/index.html.twig', [
            'equipements' => $equipementRepository->findAll(),
        ]);
    }

    #[Route('/equipement/new', name: 'app_equipement_new')]
    public function new(Request $request): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the new equipment
            $this->entityManager->persist($equipement);
            $this->entityManager->flush();

            // Redirect to the index page after saving
            return $this->redirectToRoute('app_equipement_index');
        }

        return $this->render('equipement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/equipement/{id}', name: 'app_equipement_show')]
    public function show(Equipement $equipement): Response
    {
        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }

    #[Route('/equipement/{id}/edit', name: 'app_equipement_edit')]
    public function edit(Request $request, Equipement $equipement): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the equipment
            $this->entityManager->flush();

            // Redirect to the index page after saving
            return $this->redirectToRoute('app_equipement_index');
        }

        return $this->render('equipement/edit.html.twig', [
            'form' => $form->createView(),
            'equipement' => $equipement,
        ]);
    }

    #[Route('/equipement/{id}/delete', name: 'app_equipement_delete')]
    public function delete(Request $request, Equipement $equipement): Response
    {
        // Check the CSRF token for security
        if ($this->isCsrfTokenValid('delete' . $equipement->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($equipement);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_equipement_index');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // This is a no-op action. Symfony will handle the actual logout process.
        // This will prevent errors when the logout route is accessed.
        return $this->redirectToRoute('app_equipement_index');  // Optionally redirect to the equipement index
    }

}













