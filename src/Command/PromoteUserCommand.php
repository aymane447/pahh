<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:promote-user',
    description: 'Promotes a user to Admin role',
)]
class PromoteUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('identifier', InputArgument::REQUIRED, 'The username (id_utilisateur) or email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $identifier = $input->getArgument('identifier');

        $repository = $this->entityManager->getRepository(Utilisateur::class);
        
        // Try finding by username first
        $user = $repository->findOneBy(['id_utilisateur' => $identifier]);
        
        // If not found, try by description/email (if email is unique field)
        // Check Entity: email is a column.
        if (!$user) {
            $user = $repository->findOneBy(['email' => $identifier]);
        }

        if (!$user) {
            $io->error(sprintf('User "%s" not found.', $identifier));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            $io->note(sprintf('User "%s" is already an Admin.', $identifier));
            return Command::SUCCESS;
        }

        $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_unique($roles));
        $this->entityManager->flush();

        $io->success(sprintf('User "%s" has been promoted to Admin!', $identifier));

        return Command::SUCCESS;
    }
}
