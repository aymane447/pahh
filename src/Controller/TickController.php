<?php

namespace App\Controller;
use App\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Entity\Categorie;

final class TickController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/home', name: 'app_home_alias')]
    public function home(\App\Repository\EventRepository $eventRepository): Response
    {
        return $this->render('home/pg1.html.twig', [
            'events' => $eventRepository->findAll()
        ]);
    }

    #[Route('/tick', name: 'app_tick')]
    public function index(TicketRepository $ticketRepository, Request $request, \App\Repository\CategorieRepository $categorieRepository): Response
    {
        $qb = $ticketRepository->createQueryBuilder('t');

        // Security: Restrict non-staff to their own tickets OR tickets with no client (created by agents)
        if (!$this->isGranted('ROLE_AGENT') && !$this->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('t.id_client = :user OR t.id_client IS NULL')
               ->setParameter('user', $this->getUser());
        }

        $filters = [
            'statut' => $request->query->get('statut'),
            'priorite' => $request->query->get('priorite'),
            'category' => $request->query->get('category'),
            'q' => $request->query->get('q')
        ];
        if ($filters['statut']) {
            $qb->andWhere('t.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        if ($filters['priorite']) {
            $qb->andWhere('t.priorite = :priorite')
               ->setParameter('priorite', $filters['priorite']);
        }

        if ($filters['category']) {
            $qb->andWhere('t.id_categorie = :category')
               ->setParameter('category', $filters['category']);
        }

        if ($filters['q']) {
            $qb->andWhere('t.titre LIKE :q OR t.description LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }

        $qb->orderBy('t.date_mise', 'DESC');

        return $this->render('tick/index.html.twig', [
            'tickets' => $qb->getQuery()->getResult(),
            'filters' => $filters,
            'categories' => $categorieRepository->findAll()
        ]);
    }

    #[Route('/tick/ajou', name: 'app_tick_ajou')]
    public function ajoutt(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Permission check: Only Agents and Admins can create tickets
        if (!$this->isGranted('ROLE_AGENT') && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seuls les agents peuvent créer des tickets.');
            return $this->redirectToRoute('app_tick');
        }

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        
        // Remove admin/auto fields from the form for agents
        if (!$this->isGranted('ROLE_ADMIN')) {
            $form->remove('id_client');
            $form->remove('id_agent');
            $form->remove('date_creation');
            $form->remove('date_mise');
            $form->remove('statut');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure defaults are set for EVERYONE (including Admins)
            if (!$ticket->getIdTicket()) {
                $ticket->setIdTicket('TKT-' . strtoupper(bin2hex(random_bytes(3))));
            }
            if (!$ticket->getDateCreation()) {
                $ticket->setDateCreation(new \DateTime());
            }
            if (!$ticket->getDateMise()) {
                $ticket->setDateMise(new \DateTime());
            }
            if (!$ticket->getStatut()) {
                $ticket->setStatut('Ouvert');
            }

            // Assign creator if not set
            if (!$ticket->getCreateur()) {
                $ticket->setCreateur($this->getUser());
            }

            // Logic specific to Agents (assigning themselves if they create it)
            if (!$this->isGranted('ROLE_ADMIN') && !$ticket->getIdAgent()) {
                $ticket->setIdAgent($this->getUser());
            }

            
            // Set creator for admin creations too if needed, but primarily for agents
            if (!$ticket->getCreateur()) {
                $ticket->setCreateur($this->getUser());
            }
            
            $entityManager->persist($ticket);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le ticket a été créé avec succès.');
            return $this->redirectToRoute('app_tick');
        }

        return $this->render('tick/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/tick/sup/{id}', name: 'app_tick_sup')]
    public function sup(EntityManagerInterface $entityManager, Ticket $ticket): Response
    {
        // Security check: Only Admin or the Creator (if Agent) can delete
        $isCreator = $ticket->getCreateur() === $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if (!$isAdmin && !$isCreator) {
            $this->addFlash('error', 'Vous n\'avez pas les permissions pour supprimer ce ticket (vous n\'êtes pas le créateur).');
            return $this->redirectToRoute('app_tick');
        }

        $entityManager->remove($ticket);
        $entityManager->flush();
        $this->addFlash('success', 'Ticket supprimé avec succès.');
        return $this->redirectToRoute('app_tick');
    }
    #[Route('/api/notifications/unread', name: 'app_notifications_unread')]
    public function getUnreadCount(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['count' => 0]);
        }

        // Count messages that belong to tickets where user is client or agent,
        // were not sent by the user, and are not read.
        $unreadCount = $entityManager->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->select('count(m.id)')
            ->join('m.id_ticket', 't')
            ->where('m.id_utilisateur != :user')
            ->andWhere('m.isRead = false')
            ->andWhere('t.id_client = :user OR t.id_agent = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json(['count' => (int)$unreadCount]);
    }

    #[Route('/tick/show/{id}', name: 'app_tick_show')]
    public function show(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        // Mark messages as read when viewing the ticket
        foreach ($ticket->getMessages() as $message) {
            // Compare IDs to be safe (Proxy vs Entity)
            if ($message->getIdUtilisateur()->getId() !== $this->getUser()->getId() && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $entityManager->flush();

        return $this->render('tick/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/tick/message/{id}', name: 'app_tick_message', methods: ['POST'])]
    public function addMessage(Ticket $ticket, Request $request, EntityManagerInterface $entityManager): Response
    {
        $contenu = $request->request->get('contenu');
        if ($contenu) {
            $message = new Message();
            $message->setContenu($contenu);
            $message->setDateMessage(new \DateTime());
            $message->setIdUtilisateur($this->getUser());
            $message->setIdTicket($ticket);
            $message->setIdMessage('MSG-' . strtoupper(bin2hex(random_bytes(3))));

            $entityManager->persist($message);
            
            // Update ticket update date
            $ticket->setDateMise(new \DateTime());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tick_show', ['id' => $ticket->getId()]);
    }

    #[Route('/tick/claim/{id}', name: 'app_tick_claim')]
    public function claim(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_AGENT') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $ticket->setIdAgent($this->getUser());
        $ticket->setStatut('En cours');
        $ticket->setDateMise(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez pris en charge ce ticket.');
        return $this->redirectToRoute('app_tick_show', ['id' => $ticket->getId()]);
    }

    #[Route('/tick/close/{id}', name: 'app_tick_close')]
    public function close(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_AGENT') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $ticket->setStatut('Résolu');
        $ticket->setDateMise(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été marqué comme résolu.');
        return $this->redirectToRoute('app_tick_show', ['id' => $ticket->getId()]);
    }

    #[Route('/tick/mod/{id}', name: 'app_tick_mod')]
    public function mod(EntityManagerInterface $entityManager, Request $request, Ticket $ticket): Response
    {
        // Security check: Only Admin or the Creator (if Agent) can edit
        $isCreator = $ticket->getCreateur() === $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if (!$isAdmin && !$isCreator) {
            $this->addFlash('error', 'Vous n\'avez pas les permissions pour modifier ce ticket (vous n\'êtes pas le créateur).');
            return $this->redirectToRoute('app_tick');
        }

        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setDateMise(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', 'Ticket mis à jour.');
            return $this->redirectToRoute('app_tick');
        }

        return $this->render('tick/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tick/{id}/prendre', name: 'app_tick_prendre')]
    public function prendre(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_AGENT') || $this->isGranted('ROLE_ADMIN')) {
            $ticket->setIdAgent($this->getUser());
            $ticket->setStatut('En cours');
            $ticket->setDateMise(new \DateTime());
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_tick');
    }

    #[Route('/tick/{id}/resoudre', name: 'app_tick_resoudre')]
    public function resoudre(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_AGENT') || $this->isGranted('ROLE_ADMIN')) {
            $ticket->setStatut('Résolu');
            $ticket->setDateMise(new \DateTime());
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_tick');
    }

    #[Route('/tick/{id}/acheter', name: 'app_tick_acheter')]
    public function acheter(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        // Only for Clients and only if the ticket has no owner
        if (!$this->isGranted('ROLE_CLIENT') || $ticket->getIdClient() !== null) {
            $this->addFlash('error', 'Vous ne pouvez pas acheter ce ticket.');
            return $this->redirectToRoute('app_tick');
        }

        $ticket->setIdClient($this->getUser());
        $ticket->setStatut('En cours');
        $ticket->setDateMise(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Félicitations ! Vous avez acheté ce ticket.');
        return $this->redirectToRoute('app_tick_show', ['id' => $ticket->getId()]);
    }
}
