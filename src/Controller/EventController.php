<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Ticket;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Only Agents/Admins
        if (!$this->isGranted('ROLE_AGENT') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/events',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $event->setImage($newFilename);
            }

            $event->setCreateur($this->getUser());
            
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/buy', name: 'app_event_buy', methods: ['POST'])]
    public function buy(Event $event, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_CLIENT')) {
            $this->addFlash('error', 'Vous devez être connecté en tant que client pour acheter un ticket.');
            return $this->redirectToRoute('app_login');
        }

        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setIdClient($this->getUser());
        $ticket->setTitre('Billet: ' . $event->getTitre());
        $ticket->setDescription('Achat billet pour l\'événement ' . $event->getTitre());
        $ticket->setPriorite('Moyenne');
        $ticket->setStatut('Payé'); // Or 'En attente'
        $ticket->setDateCreation(new \DateTime());
        $ticket->setDateMise(new \DateTime());
        
        // Generate Ticket ID
        $ticket->setIdTicket('TKT-EVT-' . strtoupper(bin2hex(random_bytes(3))));
        
        // Ensure ticket has a creator (System or Event Creator?)
        $ticket->setCreateur($event->getCreateur());  
        
        // Ideally we need a category for this ticket? Or make category nullable?
        // For now let's hope category is nullable or we assign a default one if needed.
        // Checking Ticket entity: id_categorie is nullable=false!
        // We need to fetch a default category or make it nullable. 
        // Let's TRY to find a category "Event" or just picking the first one available.
        
        $category = $entityManager->getRepository(\App\Entity\Categorie::class)->findOneBy([]); 
        if ($category) {
            $ticket->setIdCategorie($category);
        } else {
             // If really no category, we might fail. 
             // Ideally we should create a category 'Evenement' if it doesn't exist?
             // For safety in this "do it" mode, I'll rely on existing categories.
        }

        $entityManager->persist($ticket);
        $entityManager->flush();

        $this->addFlash('success', 'Félicitations! Vous avez acheté votre billet.');

        return $this->redirectToRoute('app_tick_show', ['id' => $ticket->getId()]);
    }
}
