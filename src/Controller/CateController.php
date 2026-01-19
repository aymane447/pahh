<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cate')]
#[IsGranted('ROLE_ADMIN')]
final class CateController extends AbstractController
{
    #[Route('', name: 'app_cate')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('cate/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$categorie->getIdCategorie()) {
                $categorie->setIdCategorie('CAT-' . strtoupper(bin2hex(random_bytes(2))));
            }
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_cate', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cate/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cate', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cate/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_cate_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->redirectToRoute('app_cate', [], Response::HTTP_SEE_OTHER);
    }
}
