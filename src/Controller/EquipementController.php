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
use App\Service\PexelsImageFetcher;
use Knp\Component\Pager\PaginatorInterface;


class EquipementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function accueil(): Response
    {
        return $this->render('equipement/home.html.twig'); 
    }



    #[Route('/equipement', name: 'app_equipement_index')]
    public function index(Request $request, EquipementRepository $equipementRepository, PexelsImageFetcher $imageFetcher, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');
        $etat = $request->query->get('etat');
        $disponible = $request->query->get('disponible');

        // Première requête : récupération brute pour compléter les images
        $initialQueryBuilder = $equipementRepository->createQueryBuilder('e');
        if ($search) {
            $initialQueryBuilder->andWhere('e.nom LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($etat) {
            $initialQueryBuilder->andWhere('e.etat = :etat')
                ->setParameter('etat', $etat);
        }
        if ($disponible !== null && $disponible !== '') {
            $initialQueryBuilder->andWhere('e.disponible = :dispo')
                ->setParameter('dispo', (bool) $disponible);
        }

        $equipementsToUpdate = $initialQueryBuilder->getQuery()->getResult();

        foreach ($equipementsToUpdate as $equipement) {
            if (!$equipement->getImageUrl()) {
                $image = $imageFetcher->fetchImage($equipement->getNom());
                if ($image) {
                    $equipement->setImageUrl($image);
                    $this->entityManager->flush();
                }
            }
        }

        // Deuxième requête pour la pagination propre
        $paginationQueryBuilder = $equipementRepository->createQueryBuilder('e');
        if ($search) {
            $paginationQueryBuilder->andWhere('e.nom LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        if ($etat) {
            $paginationQueryBuilder->andWhere('e.etat = :etat')
                ->setParameter('etat', $etat);
        }
        if ($disponible !== null && $disponible !== '') {
            $paginationQueryBuilder->andWhere('e.disponible = :dispo')
                ->setParameter('dispo', (bool) $disponible);
        }

        $pagination = $paginator->paginate(
            $paginationQueryBuilder,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('equipement/index.html.twig', [
            'equipements' => $pagination,
            'search' => $search,
            'etat' => $etat,
            'disponible' => $disponible,
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

    #[Route('/equipement/{slug}', name: 'app_equipement_show')]
    public function show(string $slug, EquipementRepository $repo): Response
    {
        $equipement = $repo->findOneBySlug($slug);

        if (!$equipement) {
            throw $this->createNotFoundException('Équipement introuvable');
        }

        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }


    #[Route('/equipement/{slug}/edit', name: 'app_equipement_edit')]
    public function edit(Request $request, string $slug, EquipementRepository $repo): Response
    {
        $equipement = $repo->findOneBySlug($slug);

        if (!$equipement) {
            throw $this->createNotFoundException('Équipement introuvable');
        }

        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_equipement_index');
        }

        return $this->render('equipement/edit.html.twig', [
            'form' => $form->createView(),
            'equipement' => $equipement,
        ]);
    }

    #[Route('/equipement/{slug}/delete', name: 'app_equipement_delete')]
    public function delete(Request $request, string $slug, EquipementRepository $repo): Response
    {
        $equipement = $repo->findOneBySlug($slug);

        if (!$equipement) {
            throw $this->createNotFoundException('Équipement introuvable');
        }

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













