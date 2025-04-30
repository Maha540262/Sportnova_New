<?php

namespace App\Command;

use App\Entity\Equipement;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-equipement-slugs',
    description: 'Génère les slugs manquants pour les équipements',
)]
class GenerateEquipementSlugsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $equipements = $this->entityManager
            ->getRepository(Equipement::class)
            ->findBy(['slug' => null]);

        $existingSlugs = $this->fetchExistingSlugs();

        foreach ($equipements as $equipement) {
            $baseSlug = Urlizer::urlize($equipement->getNom());
            $slug = $baseSlug;
            $i = 1;

            // Empêche les doublons
            while (in_array($slug, $existingSlugs, true)) {
                $slug = $baseSlug . '-' . $i++;
            }

            $equipement->setSlug($slug);
            $existingSlugs[] = $slug; // Ajoute ce slug à la liste des existants

            $output->writeln("✅ Slug généré : <info>$slug</info> pour <comment>{$equipement->getNom()}</comment>");
        }

        $this->entityManager->flush();
        $output->writeln('<info>Tous les slugs manquants ont été générés avec gestion des doublons.</info>');

        return Command::SUCCESS;
    }

    private function fetchExistingSlugs(): array
    {
        $results = $this->entityManager->getRepository(Equipement::class)->createQueryBuilder('e')
            ->select('e.slug')
            ->getQuery()
            ->getScalarResult();

        return array_map(fn($row) => $row['slug'], $results);
    }
}
