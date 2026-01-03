<?php

namespace App\Controller;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UtilisateurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UtilisateurRepository;
final class UtiliController extends AbstractController 
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    #[Route('/utili', name: 'app_utili')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
$utili=$utilisateurRepository->findAll();
        return $this->render('utili/index.html.twig', [
            'utilis' => $utili,
        ]);
    }

     #[Route('/utili/ajou', name: 'app_utili_ajou')]
    public function ajouet(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $this->passwordHasher->hashPassword($tent1, $tent1->getpassword());
            $tent1->setpassword($hashedPassword);
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('utili/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }

        #[Route('/utili/sup/{id}', name: 'app_utili_sup')]
        public function sup(EntityManagerInterface $entityManager,$id): Response
        {
            $utili=$entityManager->getRepository(Utilisateur::class)->find($id);
            $entityManager->remove($utili);
            $entityManager->flush();
            return $this->redirectToRoute('app_utili');
        }

        #[Route('/utili/mod/{id}', name: 'app_utili_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $utili=$entityManager->getRepository(Utilisateur::class)->find($id);
             $form = $this->createForm(UtilisateurType::class, $utili);
        $form->handleRequest($request);//eviter poste ou get 
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $this->passwordHasher->hashPassword($utili, $utili->getpassword());
            $utili->setpassword($hashedPassword);
            $entityManager->persist($utili);
            $entityManager->flush();
        }
        return $this->render('utili/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}    