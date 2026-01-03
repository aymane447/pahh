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

final class TickController extends AbstractController
{
    #[Route('/tick', name: 'app_tick')]
    public function index(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findAll();
        return $this->render('tick/index.html.twig', [
            'ticket' => $tickets,
        ]);
    }

     #[Route('/tick/ajou', name: 'app_tick_ajou')]
    public function ajoutt(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Ticket();
        $form = $this->createForm(TicketType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('tick/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     #[Route('/tick/sup/{id}', name: 'app_tick_sup')]
        public function sup(EntityManagerInterface $entityManager,$id): Response
        {
            $Tick=$entityManager->getRepository(Ticket::class)->find($id);
            $entityManager->remove($Tick);
            $entityManager->flush();
            return $this->redirectToRoute('app_tick');
        }
        #[Route('/tick/mod/{id}', name: 'app_tick_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $Tick=$entityManager->getRepository(Ticket::class)->find($id);
               $form = $this->createForm(TicketType::class, $Tick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Tick);
            $entityManager->flush();
        }
        return $this->render('tick/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
