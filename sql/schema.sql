-- PiVert faunes & flores — schéma de la base (refonte 2026)
-- D'après rendu_najet/creation_table .sql (SAÉ 203), avec :
--   CATEGORIE.slug / libelle / univers, PRODUIT.espece, table CARACTERISTIQUE,
--   CLIENT.role, mots de passe hachés. Le panier vit en session (pas de table PANIER).

SET NAMES utf8mb4;

CREATE TABLE CLIENT (
    id_client        INT PRIMARY KEY AUTO_INCREMENT,
    nom              VARCHAR(255) NOT NULL,
    prenom           VARCHAR(255) NOT NULL,
    email            VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe     VARCHAR(255) NOT NULL,
    adresse          VARCHAR(255) NOT NULL,
    role             ENUM('client', 'admin', 'demo') NOT NULL DEFAULT 'client',
    date_inscription DATE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE CATEGORIE (
    id_categorie  INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(255) NOT NULL,
    slug          VARCHAR(60)  NOT NULL UNIQUE,
    libelle       VARCHAR(100) NOT NULL,
    univers       ENUM('vegetal', 'animalier') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE MARQUE (
    id_marque  INT PRIMARY KEY AUTO_INCREMENT,
    nom_marque VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE PRODUIT (
    id_produit   INT PRIMARY KEY AUTO_INCREMENT,
    id_categorie INT NOT NULL,
    id_marque    INT NULL,
    photo_nom    VARCHAR(255) NULL,
    nom          VARCHAR(255) NOT NULL,
    description  VARCHAR(255) NOT NULL,
    prix         DECIMAL(10, 2) NOT NULL,
    quantite     INT NOT NULL,
    espece       ENUM('chiens', 'chats', 'rongeurs', 'oiseaux', 'poissons', 'autres') NULL,
    date_ajout   DATE NOT NULL,
    CONSTRAINT produit_quantite_positive CHECK (quantite >= 0),
    FOREIGN KEY (id_categorie) REFERENCES CATEGORIE (id_categorie),
    FOREIGN KEY (id_marque) REFERENCES MARQUE (id_marque)
) ENGINE=InnoDB;

CREATE TABLE CARACTERISTIQUE (
    id_caracteristique INT PRIMARY KEY AUTO_INCREMENT,
    id_produit         INT NOT NULL,
    libelle            VARCHAR(60) NOT NULL,
    valeur             VARCHAR(60) NOT NULL,
    icone              VARCHAR(30) NOT NULL,
    ordre              TINYINT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_produit) REFERENCES PRODUIT (id_produit) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE COMMANDE (
    id_commande   INT PRIMARY KEY AUTO_INCREMENT,
    id_client     INT NOT NULL,
    date_commande DATE NOT NULL,
    total         DECIMAL(10, 2) NOT NULL,
    statut        VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_client) REFERENCES CLIENT (id_client)
) ENGINE=InnoDB;

CREATE TABLE COMPORTER (
    id_produit    INT,
    id_commande   INT,
    quantite      INT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (id_produit, id_commande),
    FOREIGN KEY (id_produit) REFERENCES PRODUIT (id_produit),
    FOREIGN KEY (id_commande) REFERENCES COMMANDE (id_commande)
) ENGINE=InnoDB;
