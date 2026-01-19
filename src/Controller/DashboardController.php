<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        
        // Latest activities (top 5 updated tickets)
        $latestQb = $ticketRepository->createQueryBuilder('t');
        
        // Clients see only their own tickets + public tickets
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_AGENT')) {
            $latestQb->where('t.id_client = :user OR t.id_client IS NULL')
                     ->setParameter('user', $user);
        }
        // Admin and Agent see ALL tickets
        
        $latestTickets = $latestQb->orderBy('t.date_mise', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Stats
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_AGENT')) {
            $totalTickets = $ticketRepository->count([]);
            $openTickets = $ticketRepository->count(['statut' => 'Ouvert']);
            $inProgressTickets = $ticketRepository->count(['statut' => 'En cours']);
            $resolvedTickets = $ticketRepository->count(['statut' => 'Résolu']);
            $criticalTickets = $ticketRepository->count(['priorite' => 'Haute']);
        } else {
            // Helper for client stats (own + public)
            $clientQb = function($statut = null, $priorite = null) use ($ticketRepository, $user) {
                $qb = $ticketRepository->createQueryBuilder('t')
                    ->select('count(t.id)')
                    ->where('t.id_client = :user OR t.id_client IS NULL')
                    ->setParameter('user', $user);
                if ($statut) $qb->andWhere('t.statut = :statut')->setParameter('statut', $statut);
                if ($priorite) $qb->andWhere('t.priorite = :priorite')->setParameter('priorite', $priorite);
                return (int)$qb->getQuery()->getSingleScalarResult();
            };

            $totalTickets = $clientQb();
            $openTickets = $clientQb('Ouvert');
            $inProgressTickets = $clientQb('En cours');
            $resolvedTickets = $clientQb('Résolu');
            $criticalTickets = $clientQb(null, 'Haute');
        }

        return $this->render('dashboard/index.html.twig', [
            'total' => $totalTickets,
            'open' => $openTickets,
            'in_progress' => $inProgressTickets,
            'resolved' => $resolvedTickets,
            'critical' => $criticalTickets,
            'latest' => $latestTickets
        ]);
    }
}
