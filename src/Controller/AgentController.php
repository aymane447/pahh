<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Agent;
use App\Form\AgentType;
use App\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\Request;

final class AgentController extends AbstractController
{
    #[Route('/agent', name: 'app_agent')]
    public function index(AgentRepository $agentRepository): Response
    {
        return $this->render('agent/index.html.twig', [
            'controller_name' => $agentRepository->findAll(),
        ]);
    }
    #[Route('/home', name: 'app_home')]
    public function indexx(): Response
    {
       return $this->render('home/pg1.html.twig', [
            'controller_name' => 'AnnonceController',
            ]);
    }

    #[Route('/agent/ajou', name: 'app_agent_ajou')]
    public function ajoutee(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Agent();
        $form = $this->createForm(AgentType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('agent/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     #[Route('/agent/sup/{id}', name: 'app_agent_sup')]
        public function sup(EntityManagerInterface $entityManager,$id): Response
        {
            $Mess=$entityManager->getRepository(Agent::class)->find($id);
            $entityManager->remove($Mess);
            $entityManager->flush();
            return $this->redirectToRoute('app_agent');
        }
        #[Route('/agent/mod/{id}', name: 'app_agent_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $Mess=$entityManager->getRepository(Agent::class)->find($id);
               $form = $this->createForm(AgentType::class, $Mess);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Mess);
            $entityManager->flush();
        }
        return $this->render('agent/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
