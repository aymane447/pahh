<?php
require 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$passwordHasher = $container->get('security.user_password_hasher');

// Create admin user
$admin = new App\Entity\Utilisateur();
$admin->setIdUtilisateur('admin');
$admin->setNom('Admin');
$admin->setPrenom('System');
$admin->setEmail('admin@ticketflow.com');
$admin->setRoles(['ROLE_ADMIN']);

// Hash password
$hashedPassword = $passwordHasher->hashPassword($admin, 'admin123');
$admin->setPassword($hashedPassword);

$em->persist($admin);
$em->flush();

echo "✅ Compte admin créé avec succès !\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Identifiant: admin\n";
echo "Mot de passe: admin123\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
