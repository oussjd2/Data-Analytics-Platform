<?php

namespace App\Entity\Leads;

use App\Repository\LeadsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadsRepository::class)]
#[ORM\Table(name: 'leads')]
//#[ORM\EntityListeners(['App\EntityListener\LeadsListener'])]
class Leads
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $porteur = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $question = null;

    #[ORM\Column(type: "string", length: 10)]
    private ?string $title = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $country = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $ip = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $partner = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $raison = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $campaign = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $rtc = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $audio = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $subid = null;

    #[ORM\Column(type: "string", length: 10)]
    private ?string $form = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $cardlist = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $c1 = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $c2 = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $c3 = null;

    #[ORM\Column(type: "text")]
    private ?string $reponse_ai = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $exported = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPorteur(): ?string
    {
        return $this->porteur;
    }

    public function setPorteur(string $porteur): self
    {
        $this->porteur = $porteur;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPartner(): ?string
    {
        return $this->partner;
    }

    public function setPartner(string $partner): self
    {
        $this->partner = $partner;

        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): self
    {
        $this->raison = $raison;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    public function setCampaign(?string $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getRtc(): ?string
    {
        return $this->rtc;
    }

    public function setRtc(?string $rtc): self
    {
        $this->rtc = $rtc;

        return $this;
    }

    public function getAudio(): ?string
    {
        return $this->audio;
    }

    public function setAudio(?string $audio): self
    {
        $this->audio = $audio;

        return $this;
    }

    public function getSubid(): ?string
    {
        return $this->subid;
    }

    public function setSubid(?string $subid): self
    {
        $this->subid = $subid;

        return $this;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function setForm(string $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getCardlist(): ?string
    {
        return $this->cardlist;
    }

    public function setCardlist(string $cardlist): self
    {
        $this->cardlist = $cardlist;

        return $this;
    }

    public function getC1(): ?string
    {
        return $this->c1;
    }

    public function setC1(string $c1): self
    {
        $this->c1 = $c1;

        return $this;
    }

    public function getC2(): ?string
    {
        return $this->c2;
    }

    public function setC2(string $c2): self
    {
        $this->c2 = $c2;

        return $this;
    }

    public function getC3(): ?string
    {
        return $this->c3;
    }

    public function setC3(string $c3): self
    {
        $this->c3 = $c3;

        return $this;
    }

    public function getReponseAi(): ?string
    {
        return $this->reponse_ai;
    }

    public function setReponseAi(string $reponseAi): self
    {
        $this->reponse_ai = $reponseAi;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->created_at = $createdAt;

        return $this;
    }

    public function getExported(): ?bool
    {
        return $this->exported;
    }

    public function setExported(bool $exported): self
    {
        $this->exported = $exported;

        return $this;
    }
}
