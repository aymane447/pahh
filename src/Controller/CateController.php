<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;

final class CateController extends AbstractController
{
    #[Route('/cate', name: 'app_cate')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('cate/index.html.twig', [
            'categorie' => $categories, 
        ]);
    }
    #[Route('/cate/ajou', name: 'app_cate_ajou')]
    public function ajout(EntityManagerInterface $entityManager,Request $request): Response
    {
        $tent1 = new Categorie();
        $form = $this->createForm(CategorieType::class, $tent1);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tent1);
            $entityManager->flush();
        }
        return $this->render('cate/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cate/sup/{id}', name: 'app_cate_sup')]
public function supprimerAnnonce(
    int $id,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $categories = $entityManager->getRepository(Categorie::class)->find($id);

    if (!$categories) {
        throw $this->createNotFoundException("Catégorie introuvable");
    }

    // Récupérer les tickets liés
    $tickets = $categories->getTickets();

    // Vérifier si l'utilisateur a confirmé
    if ($request->query->get('confirm') !== 'true') {
        return $this->render('cate/index.html.twig', [
           'categorie' => $categories,
           'tickets' => $tickets,
        ]);
    }

    // Si confirmé → supprimer tickets puis catégorie
    foreach ($tickets as $ticket) {
        $entityManager->remove($ticket);
    }

    $entityManager->remove($categories);
    $entityManager->flush();

    return $this->redirectToRoute('app_cate');
}
        #[Route('/cate/mod/{id}', name: 'app_cate_mod')]
        public function mod(EntityManagerInterface $entityManager,Request $request,$id): Response
        {
            $Cate=$entityManager->getRepository(Categorie::class)->find($id);
               $form = $this->createForm(CategorieType::class, $Cate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
        }
        return $this->render('cate/ajou.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
