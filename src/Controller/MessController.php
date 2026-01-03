<?php

namespace App\Controller;

use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;

final class MessController extends AbstractController
{
    #[Route('/mess', name: 'app_mess')]
    public function index(MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findAll();
        return $this->render('mess/index.html.twig', [
            'message' => $messages, 
        ]);
    }
    #[Route('/mess/ajou', name: 'app_mess_ajou')]
    public function ajoute(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Message();
        $form = $this->createForm(MessageType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('mess/mesg.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mess/sup/{id}', name: 'app_mess_sup')]
        public function sup(EntityManagerInterface $entityManager,$id): Response
        {
            $Mess=$entityManager->getRepository(Message::class)->find($id);
            $entityManager->remove($Mess);
            $entityManager->flush();
            return $this->redirectToRoute('app_mess');
        }
        #[Route('/mess/mod/{id}', name: 'app_mess_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $Mess=$entityManager->getRepository(Message::class)->find($id);
               $form = $this->createForm(MessageType::class, $Mess);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Mess);
            $entityManager->flush();
        }
        return $this->render('mess/mesg.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
