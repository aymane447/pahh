<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $id_message = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_message = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $id_utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $id_ticket = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $isRead = false;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMessage(): ?string
    {
        return $this->id_message;
    }

    public function setIdMessage(string $id_message): static
    {
        $this->id_message = $id_message;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateMessage(): ?\DateTime
    {
        return $this->date_message;
    }

    public function setDateMessage(\DateTime $date_message): static
    {
        $this->date_message = $date_message;

        return $this;
    }

    public function getIdUtilisateur(): ?Utilisateur
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(?Utilisateur $id_utilisateur): static
    {
        $this->id_utilisateur = $id_utilisateur;

        return $this;
    }

    public function getIdTicket(): ?Ticket
    {
        return $this->id_ticket;
    }

    public function setIdTicket(?Ticket $id_ticket): static
    {
        $this->id_ticket = $id_ticket;

        return $this;
    }
    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }
}

