<?php

namespace App\Command;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-database',
    description: 'Seeds the database with default categories',
)]
class SeedDatabaseCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repository = $this->entityManager->getRepository(Categorie::class);
        $count = $repository->count([]);

        if ($count > 0) {
            $io->note('Categories already exist. Skipping seed.');
            return Command::SUCCESS;
        }

        $categories = [
            'Support Technique' => 'Problèmes techniques et bugs',
            'Facturation' => 'Questions sur les paiements et factures',
            'Commercial' => 'Demandes de devis ou infos produits',
            'Autre' => 'Autres demandes'
        ];

        foreach ($categories as $nom => $description) {
            $categorie = new Categorie();
            $categorie->setNom($nom);
            $categorie->setDesription($description); // Note: explicit typo in Entity 'desription'
            // Generate a random ID for id_categorie if required by Entity, or let logic handle it.
            // Looking at Categorie.php, id_categorie is a string column.
            $categorie->setIdCategorie('CAT-' . strtoupper(substr($nom, 0, 3)));
            
            $this->entityManager->persist($categorie);
        }

        $this->entityManager->flush();

        $io->success('Database seeded with default categories!');

        return Command::SUCCESS;
    }
}
