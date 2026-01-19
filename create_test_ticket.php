<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$cat = $em->getRepository(App\Entity\Categorie::class)->findOneBy([]);
$ticket = new App\Entity\Ticket();
$ticket->setTitre('Offre Spéciale Agent (TEST)');
$ticket->setDescription('Ceci est une offre créée par un agent, elle devrait être visible par TOUS les clients.');
$ticket->setStatut('Ouvert');
$ticket->setPriorite('Basse');
$ticket->setDateCreation(new \DateTime());
$ticket->setDateMise(new \DateTime());
$ticket->setIdTicket('TKT-PUBLIC-TEST');
if ($cat) $ticket->setIdCategorie($cat);
// id_client is intentionally left NULL

$em->persist($ticket);
$em->flush();
echo "SUCCESS: Ticket created with ID " . $ticket->getId() . "\n";
