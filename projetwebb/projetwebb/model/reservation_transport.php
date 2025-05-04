<?php
class ReservationTransport 
{
    private $id;
    private $id_moyen;
    private $nom;
    private $prenom;
    private $depart;
    private $destination;
    private $paiement;
    private $cin;
    private $email;
    private $datedebut;
    private $datefin;

    public function __construct($id = null, $id_moyen, $nom, $prenom, $depart, $destination, $paiement, $cin, $email, $datedebut, $datefin) 
    {
        $this->id = $id;
        $this->id_moyen = $id_moyen;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->depart = $depart;
        $this->destination = $destination;
        $this->paiement = $paiement;
        $this->cin = $cin;
        $this->email = $email;
        $this->datedebut = $datedebut;
        $this->datefin = $datefin;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of id_moyen
     */ 
    public function getIdMoyen()
    {
        return $this->id_moyen;
    }

    /**
     * Set the value of id_moyen
     *
     * @return  self
     */ 
    public function setIdMoyen($id_moyen)
    {
        $this->id_moyen = $id_moyen;
        return $this;
    }

    /**
     * Get the value of nom
     */ 
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @return  self
     */ 
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Get the value of prenom
     */ 
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set the value of prenom
     *
     * @return  self
     */ 
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * Get the value of depart
     */ 
    public function getDepart()
    {
        return $this->depart;
    }

    /**
     * Set the value of depart
     *
     * @return  self
     */ 
    public function setDepart($depart)
    {
        $this->depart = $depart;
        return $this;
    }

    /**
     * Get the value of destination
     */ 
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set the value of destination
     *
     * @return  self
     */ 
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Get the value of paiement
     */ 
    public function getPaiement()
    {
        return $this->paiement;
    }

    /**
     * Set the value of paiement
     *
     * @return  self
     */ 
    public function setPaiement($paiement)
    {
        $this->paiement = $paiement;
        return $this;
    }

    /**
     * Get the value of cin
     */ 
    public function getCin()
    {
        return $this->cin;
    }

    /**
     * Set the value of cin
     *
     * @return  self
     */ 
    public function setCin($cin)
    {
        $this->cin = $cin;
        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the value of datedebut
     */ 
    public function getDatedebut()
    {
        return $this->datedebut;
    }

    /**
     * Set the value of datedebut
     *
     * @return  self
     */ 
    public function setDatedebut($datedebut)
    {
        $this->datedebut = $datedebut;
        return $this;
    }

    /**
     * Get the value of datefin
     */ 
    public function getDatefin()
    {
        return $this->datefin;
    }

    /**
     * Set the value of datefin
     *
     * @return  self
     */ 
    public function setDatefin($datefin)
    {
        $this->datefin = $datefin;
        return $this;
    }
}