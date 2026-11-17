-- PiVert faunes & flores — données de démonstration
-- Les 51 produits viennent de rendu_najet/insertion_Profil.sql (SAÉ 203), corrigés :
-- noms et accents, photos, espèce, date d'ajout, caractéristiques.
-- Comptes : client@pivert.demo / demo1234 (client) et recruteur@pivert.demo / recruteur2027 (démo admin en lecture seule).
-- Le compte admin complet n'est pas ici : php outils/creer-admin.php email motdepasse prenom nom

SET NAMES utf8mb4;

INSERT INTO CATEGORIE (id_categorie, nom_categorie, slug, libelle, univers) VALUES
    (1,  'Plantes',                 'plantes',           'Plantes',                'vegetal'),
    (2,  'Fleurs',                  'fleurs',            'Fleurs',                 'vegetal'),
    (3,  'Arbres',                  'arbres',            'Arbres',                 'vegetal'),
    (4,  'Outils_de_jardinage',     'outils-jardinage',  'Outils de jardinage',    'vegetal'),
    (5,  'Decoration_jardin',       'decoration-jardin', 'Décoration de jardin',   'vegetal'),
    (6,  'Animaux',                 'animaux',           'Animaux',                'animalier'),
    (7,  'Alimentation_nutritions', 'alimentation',      'Alimentation',           'animalier'),
    (8,  'Sante_et_bien-etre',      'sante',             'Santé et bien-être',     'animalier'),
    (9,  'Accesoires_et_loisirs',   'accessoires',       'Accessoires et loisirs', 'animalier'),
    (10, 'Habitat_animal',          'habitat',           'Habitat',                'animalier');

INSERT INTO MARQUE (id_marque, nom_marque) VALUES
    (1, 'Stihl'), (2, 'Bosch'), (3, 'Black & Decker'), (4, 'Gardena'), (5, 'Wolf'),
    (6, 'Chalet & Jardin'), (7, 'Maison de Vacances'), (8, 'Gifi'), (9, 'Tribu'), (10, 'Leroy Merlin'),
    (11, 'Purina'), (12, 'Royal Canin'), (13, 'Hamiform'), (14, 'Tetra'), (15, 'Friskies'),
    (16, 'Toonioos'), (17, 'Tyrol'), (18, 'JBL'), (19, 'Freedog'), (20, 'Hobby Pet'),
    (21, 'Heoupet'), (22, 'Zolux'), (23, 'KONG'), (24, 'Julius-K9'), (25, 'Trixie'),
    (26, 'Aquatlantis'), (27, 'Ferplast'), (28, 'Omlet'), (29, 'Exo Terra'), (30, 'Zoo Med');

INSERT INTO PRODUIT (id_produit, id_categorie, id_marque, photo_nom, nom, description, prix, quantite, espece, date_ajout) VALUES
    -- Plantes
    (1,  1, NULL, 'monstera.jpg',   'Monstera',   'Plante d''intérieur aux grandes feuilles découpées.', 29.99, 20, NULL, '2026-11-10'),
    (2,  1, NULL, 'ficus.jpg',      'Ficus',      'Plante d''intérieur aux feuilles brillantes, facile à vivre.', 19.99, 30, NULL, '2026-03-14'),
    (3,  1, NULL, 'bonsai.jpg',     'Bonsaï',     'Arbre miniature pour décorer ton intérieur.', 49.99, 10, NULL, '2026-06-02'),
    (4,  1, NULL, 'cactus.jpg',     'Cactus',     'Plante succulente qui résiste à la sécheresse.', 9.99, 50, NULL, '2026-02-20'),
    (5,  1, NULL, 'succulente.jpg', 'Succulente', 'Plante grasse aux feuilles épaisses.', 14.99, 25, NULL, '2026-04-08'),
    -- Fleurs
    (6,  2, NULL, 'tournesol.webp', 'Tournesol', 'Fleur jaune à grande tête, qui suit le soleil.', 9.99, 30, NULL, '2026-05-19'),
    (7,  2, NULL, 'jonquille.webp', 'Jonquille', 'Fleur de printemps au parfum doux.', 7.99, 25, NULL, '2026-01-27'),
    (8,  2, NULL, 'tulipe.webp',    'Tulipe',    'Fleur colorée au calice en forme de gobelet.', 8.99, 20, NULL, '2026-02-03'),
    (9,  2, NULL, 'rose.jpg',       'Rose',      'Fleur rouge au parfum intense.', 12.99, 15, NULL, '2026-07-11'),
    (10, 2, NULL, 'violette.webp',  'Violette',  'Petite fleur violette au parfum sucré.', 6.99, 30, NULL, '2026-01-12'),
    (11, 2, NULL, 'pivoine.jpg',    'Pivoine',   'Fleur rose aux pétales ronds et généreux.', 10.99, 10, NULL, '2026-11-03'),
    -- Arbres
    (12, 3, NULL, 'chene.webp',   'Chêne',   'Jeune chêne de 5 ans.', 49.99, 10, NULL, '2026-09-22'),
    (13, 3, NULL, 'pin.webp',     'Pin',     'Jeune pin de 3 ans.', 39.99, 15, NULL, '2026-10-05'),
    (14, 3, NULL, 'sapin.jpg',    'Sapin',   'Sapin de 2 ans, parfait pour les fêtes.', 29.99, 20, NULL, '2026-10-20'),
    (15, 3, NULL, 'olivier.jpg',  'Olivier', 'Olivier de 4 ans, pour un air de Méditerranée.', 59.99, 5, NULL, '2026-08-17'),
    (16, 3, NULL, 'erable.webp',  'Érable',  'Érable de 6 ans aux couleurs d''automne.', 69.99, 8, NULL, '2026-09-30'),
    -- Outils de jardinage
    (17, 4, 1, 'tondeuse.webp',              'Tondeuse',              'Tondeuse à gazon électrique.', 199.99, 5, NULL, '2026-04-15'),
    (18, 4, 1, 'taille-haie.webp',           'Taille-haie',           'Taille-haie sans fil.', 149.99, 10, NULL, '2026-05-06'),
    (19, 4, 2, 'debroussailleuse.webp',      'Débroussailleuse',      'Débroussailleuse thermique.', 249.99, 3, NULL, '2026-06-18'),
    (20, 4, 3, 'scarificateur.webp',         'Scarificateur',         'Scarificateur électrique pour aérer la pelouse.', 179.99, 7, NULL, '2026-03-28'),
    (21, 4, 5, 'souffleur-de-feuilles.webp', 'Souffleur de feuilles', 'Souffleur de feuilles à batterie.', 129.99, 12, NULL, '2026-10-12'),
    -- Décoration de jardin
    (22, 5, 6,  'banc-de-jardin.webp',     'Banc de jardin',          'Banc de jardin en bois.', 149.99, 5, NULL, '2026-05-25'),
    (23, 5, 7,  'fontaine-de-jardin.webp', 'Fontaine de jardin',      'Fontaine de jardin en pierre.', 199.99, 3, NULL, '2026-06-09'),
    (24, 5, 8,  'statue.webp',             'Statue de jardin',        'Statue de jardin en résine.', 89.99, 10, NULL, '2026-07-02'),
    (25, 5, 9,  'lanterne.webp',           'Lanterne solaire',        'Lanterne solaire pour éclairer le jardin.', 29.99, 20, NULL, '2026-08-04'),
    (26, 5, 10, NULL,                      'Pot de fleurs décoratif', 'Pot de fleurs décoratif en céramique.', 39.99, 15, NULL, '2026-07-21'),
    -- Animaux
    (27, 6, NULL, 'chien.jpg',              'Chien',              'Chien de race Labrador.', 299.99, 5, 'chiens', '2026-09-14'),
    (28, 6, NULL, 'chat.jpg',               'Chat',               'Chat de race Siamois.', 199.99, 10, 'chats', '2026-10-09'),
    (29, 6, NULL, 'lapin.jpg',              'Lapin',              'Lapin de race Angora.', 49.99, 15, 'rongeurs', '2026-11-08'),
    (30, 6, NULL, 'cochon-d-inde.jpg',      'Cochon d''Inde',     'Cochon d''Inde de race Rex.', 39.99, 20, 'rongeurs', '2026-11-01'),
    (31, 6, NULL, 'poisson-combattant.jpg', 'Poisson combattant', 'Poisson combattant de couleur rouge.', 9.99, 30, 'poissons', '2026-10-26'),
    -- Alimentation
    (32, 7, 11, 'croquettes-chien.jpg',    'Croquettes pour chien',    'Croquettes de haute qualité pour chien.', 29.99, 20, 'chiens', '2026-02-11'),
    (33, 7, 12, 'croquettes-chat.webp',    'Croquettes pour chat',     'Croquettes de haute qualité pour chat.', 19.99, 30, 'chats', '2026-03-03'),
    (34, 7, 13, 'foin.jpg',                'Foin pour rongeurs',       'Foin frais et nutritif pour rongeurs.', 9.99, 15, 'rongeurs', '2026-01-19'),
    (35, 7, 14, 'nourriture-poissons.jpeg', 'Nourriture pour poissons', 'Nourriture équilibrée pour poissons.', 14.99, 25, 'poissons', '2026-04-22'),
    (36, 7, 15, 'graines-oiseaux.jpg',     'Graines pour oiseaux',     'Mélange de graines variées et nutritives.', 12.99, 20, 'oiseaux', '2026-01-05'),
    -- Santé et bien-être
    (37, 8, 16, 'tapis-rafraichissant.webp', 'Tapis rafraîchissant pour chien', 'Tapis rafraîchissant pour les fortes chaleurs.', 29.99, 20, 'chiens', '2026-06-25'),
    (38, 8, 17, NULL,                        'Fontaine à eau pour animaux',     'Fontaine à eau avec filtration.', 39.99, 15, NULL, '2026-05-13'),
    (39, 8, 18, 'brosse-toilettage.png',     'Brosse de toilettage',            'Brosse de toilettage aux poils doux.', 14.99, 25, NULL, '2026-03-21'),
    (40, 8, 19, 'pompe-aquarium.jpg',        'Pompe à aquarium',                'Pompe à aquarium silencieuse et efficace.', 24.99, 10, 'poissons', '2026-08-28'),
    (41, 8, 20, 'coupe-griffe.webp',         'Coupe-griffe pour rongeurs',      'Coupe-griffe avec butée de sécurité.', 9.99, 30, 'rongeurs', '2026-02-26'),
    -- Accessoires et loisirs
    (42, 9, 21, 'laisse-chien.jpg',   'Laisse pour chien',              'Laisse pour chien de haute qualité.', 19.99, 20, 'chiens', '2026-07-15'),
    (43, 9, 22, 'jouet-chien.webp',   'Jouet pour chien',               'Jouet interactif pour chien.', 14.99, 30, 'chiens', '2026-08-11'),
    (44, 9, 23, 'jouet-chat.webp',    'Jouet pour chat',                'Jouet à plumes pour chat.', 9.99, 25, 'chats', '2026-09-03'),
    (45, 9, 24, 'boule-rongeurs.jpg', 'Boule sensorielle pour rongeurs', 'Boule sensorielle avec friandises.', 12.99, 15, 'rongeurs', '2026-04-29'),
    (46, 9, 25, 'tunnel-jeu.jpg',     'Tunnel de jeu pour rongeurs',    'Tunnel de jeu en tissu.', 24.99, 10, 'rongeurs', '2026-06-30'),
    -- Habitat
    (47, 10, 26, 'aquarium.jpg',      'Aquarium',          'Aquarium pour poissons.', 49.99, 10, 'poissons', '2026-09-08'),
    (48, 10, 27, 'poulailler.jpg',    'Poulailler',        'Poulailler pour poules.', 199.99, 5, 'autres', '2026-05-31'),
    (49, 10, 28, 'terrarium.jpg',     'Terrarium',         'Terrarium pour reptiles.', 89.99, 8, 'autres', '2026-07-08'),
    (50, 10, 29, 'cage-oiseaux.webp', 'Cage à oiseaux',    'Cage pour oiseaux.', 59.99, 12, 'oiseaux', '2026-03-09'),
    (51, 10, 30, 'cage-rongeurs.webp', 'Cage à rongeurs',   'Cage pour rongeurs.', 39.99, 15, 'rongeurs', '2026-08-21');

INSERT INTO CARACTERISTIQUE (id_produit, libelle, valeur, icone, ordre) VALUES
    (1, 'Lumière', 'Indirecte vive', 'soleil', 0), (1, 'Arrosage', '1× par semaine', 'goutte', 1), (1, 'Hauteur', '80 cm', 'regle', 2),
    (2, 'Lumière', 'Vive, sans soleil direct', 'soleil', 0), (2, 'Arrosage', '1× par semaine', 'goutte', 1), (2, 'Hauteur', '60 cm', 'regle', 2),
    (3, 'Lumière', 'Vive', 'soleil', 0), (3, 'Arrosage', '2× par semaine', 'goutte', 1), (3, 'Hauteur', '30 cm', 'regle', 2), (3, 'Âge', '5 ans', 'sablier', 3),
    (4, 'Lumière', 'Plein soleil', 'soleil', 0), (4, 'Arrosage', '1× par mois', 'goutte', 1), (4, 'Hauteur', '15 cm', 'regle', 2),
    (5, 'Lumière', 'Plein soleil', 'soleil', 0), (5, 'Arrosage', 'Tous les 15 jours', 'goutte', 1), (5, 'Hauteur', '10 cm', 'regle', 2),
    (6, 'Lumière', 'Plein soleil', 'soleil', 0), (6, 'Arrosage', 'Régulier', 'goutte', 1), (6, 'Floraison', 'Juillet – septembre', 'feuille', 2), (6, 'Hauteur', '1,5 m', 'regle', 3),
    (7, 'Lumière', 'Soleil ou mi-ombre', 'soleil', 0), (7, 'Floraison', 'Mars – avril', 'feuille', 1), (7, 'Hauteur', '35 cm', 'regle', 2),
    (8, 'Lumière', 'Plein soleil', 'soleil', 0), (8, 'Floraison', 'Avril – mai', 'feuille', 1), (8, 'Hauteur', '40 cm', 'regle', 2),
    (9, 'Lumière', 'Plein soleil', 'soleil', 0), (9, 'Arrosage', '2× par semaine en été', 'goutte', 1), (9, 'Floraison', 'Mai – octobre', 'feuille', 2),
    (10, 'Lumière', 'Mi-ombre', 'soleil', 0), (10, 'Floraison', 'Février – mai', 'feuille', 1), (10, 'Hauteur', '15 cm', 'regle', 2),
    (11, 'Lumière', 'Plein soleil', 'soleil', 0), (11, 'Floraison', 'Mai – juin', 'feuille', 1), (11, 'Hauteur', '80 cm', 'regle', 2),
    (12, 'Âge', '5 ans', 'sablier', 0), (12, 'Hauteur adulte', '25 m', 'regle', 1), (12, 'Rusticité', 'Jusqu''à −30 °C', 'thermometre', 2),
    (13, 'Âge', '3 ans', 'sablier', 0), (13, 'Hauteur adulte', '20 m', 'regle', 1), (13, 'Exposition', 'Plein soleil', 'soleil', 2),
    (14, 'Âge', '2 ans', 'sablier', 0), (14, 'Hauteur adulte', '15 m', 'regle', 1), (14, 'Rusticité', 'Jusqu''à −25 °C', 'thermometre', 2),
    (15, 'Âge', '4 ans', 'sablier', 0), (15, 'Exposition', 'Plein soleil', 'soleil', 1), (15, 'Rusticité', 'Jusqu''à −10 °C', 'thermometre', 2),
    (16, 'Âge', '6 ans', 'sablier', 0), (16, 'Hauteur adulte', '10 m', 'regle', 1), (16, 'Exposition', 'Mi-ombre', 'soleil', 2),
    (17, 'Moteur', 'Électrique 1 600 W', 'outil', 0), (17, 'Largeur de coupe', '38 cm', 'regle', 1), (17, 'Surface', 'Jusqu''à 500 m²', 'maison', 2),
    (18, 'Moteur', 'Batterie 18 V', 'outil', 0), (18, 'Lame', '50 cm', 'regle', 1), (18, 'Autonomie', '45 min', 'sablier', 2),
    (19, 'Moteur', 'Thermique 27 cm³', 'outil', 0), (19, 'Largeur de coupe', '42 cm', 'regle', 1),
    (20, 'Moteur', 'Électrique 1 500 W', 'outil', 0), (20, 'Largeur de travail', '32 cm', 'regle', 1),
    (21, 'Moteur', 'Batterie 36 V', 'outil', 0), (21, 'Autonomie', '30 min', 'sablier', 1),
    (22, 'Matière', 'Bois d''acacia', 'feuille', 0), (22, 'Dimensions', '120 × 60 cm', 'regle', 1), (22, 'Extérieur', 'Résiste au gel', 'thermometre', 2),
    (23, 'Matière', 'Pierre reconstituée', 'feuille', 0), (23, 'Hauteur', '90 cm', 'regle', 1), (23, 'Pompe', 'Incluse', 'outil', 2),
    (24, 'Matière', 'Résine', 'feuille', 0), (24, 'Hauteur', '45 cm', 'regle', 1),
    (25, 'Recharge', 'Solaire', 'soleil', 0), (25, 'Éclairage', '8 h', 'sablier', 1),
    (26, 'Matière', 'Céramique', 'feuille', 0), (26, 'Diamètre', '30 cm', 'regle', 1),
    (27, 'Espérance de vie', '10 – 12 ans', 'sablier', 0), (27, 'Repas', 'Croquettes', 'assiette', 1), (27, 'Espace', 'Jardin conseillé', 'maison', 2), (27, 'Caractère', 'Joueur', 'patte', 3),
    (28, 'Espérance de vie', '15 ans', 'sablier', 0), (28, 'Repas', 'Croquettes, pâtée', 'assiette', 1), (28, 'Caractère', 'Bavard', 'patte', 2),
    (29, 'Espérance de vie', '8 – 10 ans', 'sablier', 0), (29, 'Repas', 'Foin, légumes', 'assiette', 1), (29, 'Espace', 'Grand enclos', 'maison', 2),
    (30, 'Espérance de vie', '5 – 7 ans', 'sablier', 0), (30, 'Repas', 'Foin, vitamine C', 'assiette', 1), (30, 'Espace', 'Cage de 120 cm', 'maison', 2),
    (31, 'Espérance de vie', '3 ans', 'sablier', 0), (31, 'Eau', '24 – 28 °C', 'thermometre', 1), (31, 'Espace', 'Aquarium de 20 L', 'maison', 2),
    (32, 'Contenu', 'Sac de 12 kg', 'regle', 0), (32, 'Pour', 'Chien adulte', 'patte', 1),
    (33, 'Contenu', 'Sac de 4 kg', 'regle', 0), (33, 'Pour', 'Chat adulte', 'patte', 1),
    (34, 'Contenu', '1 kg', 'regle', 0), (34, 'Pour', 'Lapins, cochons d''Inde', 'patte', 1),
    (35, 'Contenu', '250 ml', 'regle', 0), (35, 'Pour', 'Poissons tropicaux', 'patte', 1),
    (36, 'Contenu', '1 kg', 'regle', 0), (36, 'Pour', 'Oiseaux de cage et du jardin', 'patte', 1),
    (37, 'Dimensions', '90 × 50 cm', 'regle', 0), (37, 'Fonctionnement', 'Sans électricité', 'thermometre', 1),
    (38, 'Contenance', '2 L', 'goutte', 0), (38, 'Filtre', 'Charbon actif', 'outil', 1),
    (39, 'Pour', 'Chiens et chats', 'patte', 0), (39, 'Poils', 'Doux', 'feuille', 1),
    (40, 'Débit', '300 L/h', 'goutte', 0), (40, 'Bruit', 'Silencieuse', 'outil', 1),
    (41, 'Pour', 'Rongeurs', 'patte', 0), (41, 'Sécurité', 'Butée de coupe', 'outil', 1),
    (42, 'Longueur', '2 m', 'regle', 0), (42, 'Pour', 'Chiens moyens', 'patte', 1),
    (43, 'Usage', 'Mastication', 'patte', 0), (43, 'Matière', 'Caoutchouc', 'outil', 1),
    (44, 'Pour', 'Chats', 'patte', 0), (44, 'Matière', 'Plumes naturelles', 'feuille', 1),
    (45, 'Pour', 'Rongeurs', 'patte', 0), (45, 'Bonus', 'Friandises incluses', 'assiette', 1),
    (46, 'Longueur', '1 m', 'regle', 0), (46, 'Matière', 'Tissu', 'feuille', 1),
    (47, 'Volume', '60 L', 'goutte', 0), (47, 'Dimensions', '61 × 31 × 36 cm', 'regle', 1),
    (48, 'Capacité', '2 à 4 poules', 'maison', 0), (48, 'Dimensions', '150 × 80 cm', 'regle', 1),
    (49, 'Chauffage', 'Compatible', 'thermometre', 0), (49, 'Dimensions', '60 × 45 × 45 cm', 'regle', 1),
    (50, 'Dimensions', '50 × 30 × 60 cm', 'regle', 0), (50, 'Capacité', '1 à 2 oiseaux', 'patte', 1),
    (51, 'Dimensions', '100 × 50 cm', 'regle', 0), (51, 'Étages', '2', 'maison', 1);

INSERT INTO CLIENT (nom, prenom, email, mot_de_passe, adresse, role, date_inscription) VALUES
    ('Martin', 'Camille',   'client@pivert.demo',    '$2y$10$0/n1fdwLNhsuyVUxf/A46OoeYf0/6ETykI1IXFDlkpJlnp71bNtZu', '12 rue des Lilas, 41000 Blois', 'client', '2026-09-01'),
    ('Démo',   'Recruteur', 'recruteur@pivert.demo', '$2y$10$87RHdpBUuNVhyz1bSk.SfOboTmX5iTfUiNUS5zd/1RN.dcT5R7AGy', '—',                            'demo',   '2026-11-17');
