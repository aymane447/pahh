<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Request;

final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(ClientRepository $clientRepository): Response
    {
         $client=$clientRepository->findAll();
        return $this->render('client/index.html.twig', [
            'client' => $client,

        ]);
    }
     #[Route('/client/ajou', name: 'app_client_ajou')]
    public function ajoutee(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Client();
        $form = $this->createForm(ClientType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('client/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     #[Route('/client/sup/{id}', name: 'app_client_delete')]
        public function sup(EntityManagerInterface $entityManager,$id): Response
        {
            $Mess=$entityManager->getRepository(Client::class)->find($id);
            $entityManager->remove($Mess);
            $entityManager->flush();
            return $this->redirectToRoute('app_client');
        }
        #[Route('/client/mod/{id}', name: 'app_client_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $Mess=$entityManager->getRepository(Client::class)->find($id);
               $form = $this->createForm(ClientType::class, $Mess);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Mess);
            $entityManager->flush();
        }
        return $this->render('client/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
