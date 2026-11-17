# Refonte du front de PiVert faunes & flores — document de conception

*17/11/2026 — Mathis Beaussier (avec Claude)*

## 1. Pourquoi

PiVert faunes & flores est le site e-commerce de plantes et d'animaux réalisé en binôme (Mathis + Korantin Mercuzot) pour la SAÉ 203 de 1re année de BUT MMI. Mathis veut en faire **une pièce de son portfolio** pour trouver son stage de printemps 2027 (profil développeur web).

Aujourd'hui le site ne peut pas être montré :
- chaque page a son propre header, sa propre nav et son propre CSS (4 polices différentes) ; le panier n'a même pas de nav ;
- `connexion.php` affiche « connexion reussi youpi » en haut de chaque page ;
- la loupe de recherche ouvre un champ qui ne filtre rien, les « Caractéristiques » de la pop-up produit sont vides ;
- l'espace admin (gestion de stock, `rendu-Deniau`) est une appli séparée, sans style commun ni protection ;
- failles visibles dans le code : mots de passe en clair, injections SQL, sorties non échappées.

Mathis veut **refaire tout le front**, pour un rendu **plus professionnel mais original**. Un recruteur doit voir : un site soigné et cohérent, qui marche (mobile, recherche, panier, commande, admin), et un code propre quand il ouvre le dépôt GitHub.

## 2. Objectifs, non-objectifs, anti-objectifs

**Objectifs**
- Une seule identité visuelle (direction « Serre » v2, §5) sur **toutes** les pages, admin comprise.
- Un seul site : la partie client et l'admin partagent le même gabarit, la même connexion à la base et le même CSS.
- Toutes les fonctions existantes marchent et sont finies : parcours des 2 univers, pop-up produit, recherche, panier, inscription/connexion, validation de commande, gestion du stock, passage de commande côté admin.
- Responsive (mobile d'abord) et accessible (contrastes, focus visible, navigation clavier).
- Code lisible par un recruteur : logique PHP séparée de l'affichage (Twig), requêtes préparées, pas de code mort.

**Non-objectifs** (on ne le fait pas dans cette refonte)
- Paiement en ligne, envoi d'e-mails, vrai formulaire de contact.
- Espace « Mon compte » / historique des commandes côté client.
- Un CMS pour les conseils (ils restent du contenu statique).
- Changer de langage ou de framework (pas de Laravel, pas de React).
- Mise en ligne : elle se fera après, sur AlwaysData (le code doit juste y être prêt, §8).

**Anti-objectifs** (ce qui ferait rater le projet même si « ça marche »)
- Un rendu « template Bootstrap » ou générique : l'originalité doit se voir (couleur par univers, typo, grille bento).
- Une refonte qui casse des fonctions qui marchaient (panier, commande, décrément du stock).
- Du texte citron ou corail sur fond blanc (illisible).
- Présenter le projet comme un travail solo : le projet d'origine reste crédité au binôme.

## 3. Contraintes

- **Stack** : PHP 8 procédural + MySQL via `mysqli`, Twig 3 (via Composer), HTML/CSS/JS natif. C'est la stack réelle de Mathis ; Laravel/React/Tailwind sont encore en apprentissage chez lui.
- **Environnement local** : XAMPP (MySQL `root` sans mot de passe en local) ; base `sae_203`.
- **Hébergement cible** : AlwaysData (PHP + MySQL), donc pas de dépendance serveur exotique.
- **Les anciens dossiers ne sont pas modifiés** (`rendu_jaqueline`, `rendu-Deniau`, `rendu_najet`) : ils servent de « avant » pour le portfolio. La refonte vit dans un nouveau dossier.
- **Données** : le schéma est dans `rendu_najet/creation_table .sql`, les données dans `rendu_najet/insertion_Profil.sql` (10 catégories, marques, **51 produits**). Ce fichier ne s'exécute pas tel quel : les lignes animalières ont 7 valeurs pour 6 colonnes, `date_ajout` (NOT NULL) n'est jamais rempli, et les `INSERT` de `CLIENT`, `PANIER`, `COMMANDE`… sont vides (aucun client de test). La base locale de Mathis diffère donc un peu de ce schéma (ex. `validerPanier.php` lit `prenom_client`, absent du schéma). Le lien produit → photo (`photo_nom`) n'existe que dans la base locale de Mathis (fichiers hachés dans `rendu_jaqueline/imagesBaseDonnées/hasher/`) ; les photos lisibles sont dans `rendu_jaqueline/imagesBaseDonnées/brutes/` (`monstera.jpg`, `lapin.jpg`…).

## 4. Approches envisagées

| Approche | Principe | Pour | Contre |
|---|---|---|---|
| 1. PHP actuel, vues refaites | Garder les fichiers PHP, ajouter `include header/footer`, un CSS commun | Le moins de travail | HTML toujours mélangé au PHP ; l'admin Twig reste à part |
| **2. Twig partout (retenue)** | Toutes les pages, client et admin, rendues par Twig avec un gabarit commun ; le PHP prépare seulement les données | Code propre, logique séparée de l'affichage, un seul gabarit pour site + admin ; Twig est déjà sur le CV et déjà utilisé par l'admin | Un peu plus de travail que la 1 (chaque page est réécrite) |
| 3. Laravel + Blade | Réécriture complète dans Laravel | Le plus impressionnant | Réécriture du back aussi, framework encore en apprentissage, beaucoup plus long |

**Choix de Mathis : approche 2.** Pour le CSS : CSS maison avec variables (proposition de Claude, pas d'objection) — la couleur d'univers change avec un attribut `data-univers`, ce qui met en valeur de vraies compétences CSS ; Tailwind n'apporterait rien ici.

## 5. Direction visuelle (validée sur maquettes)

Direction **B « Serre » v2**, validée par Mathis sur maquettes. Les maquettes sont dans `site_a_ameliorer/.superpowers/brainstorm/967-1794903192/content/serre-v2.html` (référence à suivre ; ouvrir dans un navigateur, les images sont dans le même dossier).

**Principe** : une base neutre commune (vert foncé, fond vert très clair, typo grasse) + **une couleur d'accent par univers** qui colore tout ce qui appartient à cet univers.

**Tokens** (valeurs de la maquette, ajustables si un contraste échoue) :

| Token | Valeur | Usage |
|---|---|---|
| `--vert` | `#12351F` | texte principal, blocs sombres, bouton panier |
| `--fond` | `#EEF2E6` | fond de page, fonds secondaires (spécifications, quantité) |
| `--blanc` | `#FFFFFF` | cartes, nav, pop-up |
| `--citron` | `#C6F05A` | accent **Univers Végétal** |
| `--corail` | `#FF8A66` | accent **Univers Animalier** |
| `--accent` | = citron ou corail selon `data-univers` | ce que les composants utilisent réellement |

- `body[data-univers="vegetal"]` → `--accent: var(--citron)` ; `body[data-univers="animalier"]` → `--accent: var(--corail)`. Les pages neutres (accueil, panier, compte) utilisent les deux accents là où le contenu appartient à un univers (étiquettes produit, tuiles).
- **Règle de contraste** : citron et corail ne servent **que de fonds**, toujours avec du texte `--vert` dessus. Jamais de texte citron/corail sur blanc. Le texte citron/corail est permis seulement sur fond `--vert` (ex. titre de l'accueil).
- **Typo** : Bricolage Grotesque (Google Fonts), 400/500/600/800. Titres en 800, interlettrage serré (`-0.03em`).
- **Formes** : coins très arrondis (cartes 22–28 px, boutons et étiquettes en pilule), grille « bento » sur l'accueil, pas d'ombres lourdes.
- **Icônes** : SVG en ligne (style trait simple) plutôt que des emojis, pour un rendu pro. Les emojis de la maquette sont des espaces réservés.

## 6. Pages et comportements

### Partie client
Toutes les pages partagent : nav en pilule (logo, Végétal, Animalier, Conseils, Contact, compte, panier avec nombre d'articles), footer (contact, crédits), toast de confirmation (« Ajouté au panier »).

1. **Accueil** (maquette écran 1) : grande tuile vert foncé (accroche « Fais pousser *ta jungle*. Adopte *ta bande*. » + 2 boutons Végétal/Animalier), tuile photo Univers Végétal (étiquette citron), tuile photo Univers Animalier (étiquette corail), tuile « conseil du jour », rangée de produits mis en avant avec étiquette d'univers. Puis section « Qui sommes-nous » (texte + crédit du binôme) et section Contact (infos + carte Google Maps existante). Le carrousel actuel disparaît, remplacé par la grille bento.
2. **Page univers** — une seule page, deux thèmes (maquette écran 2) : `univers.php?u=vegetal|animalier[&filtre=<slug>]`. Bandeau à la couleur de l'univers avec titre et champ de recherche, puces de filtre, grille de cartes produit (photo, nom, prix, catégorie, bouton « + »). Les accordéons `<details>` actuels disparaissent.
   - **Puces de filtre — décision de Mathis** :
     - *Végétal* : par **catégorie** (Tout, Plantes, Fleurs, Arbres, Outils de jardinage, Décoration de jardin).
     - *Animalier* : par **animal** (Tout, Chiens, Chats, Rongeurs, Oiseaux, Poissons, Autres animaux), comme sur la maquette. Le type de produit (Animaux, Alimentation, Santé, Accessoires, Habitat) reste affiché sur la carte et dans la pop-up, et sert à l'admin, mais n'est pas un filtre côté client. Un produit pour « tous animaux » (fontaine à eau, brosse de toilettage) apparaît dans tous les filtres d'animal.
   - **Filtre** : côté serveur via `?filtre=` (marche sans JS, permet de faire des liens depuis l'accueil, ex. tuile « Chats »).
   - **Recherche** : filtrage en direct côté client (JS) sur le nom et la catégorie des cartes affichées ; message « Aucun produit » si rien ne correspond.
   - En bas de page : la section conseils de l'univers (Coin conseils / Conseils et formations), en cartes, qui ouvrent une pop-up conseil.
3. **Pop-up produit** (maquette écran 3) : une seule pop-up générique remplie en JS à partir des `data-*` de la carte (c'est le « défi » à raconter dans le portfolio — on le garde et on le soigne). Photo, nom, prix (pilule accent), description, **caractéristiques** (2 à 4 tuiles icône + libellé + valeur), sélecteur de quantité, bouton « Ajouter au panier » accent. Réalisée avec `<dialog>` (fermeture par Échap, clic à l'extérieur, bouton ✕ ; focus piégé). Si le produit est en rupture (`quantite = 0`) : bouton désactivé « Rupture de stock ».
4. **Panier** : liste des lignes (photo, nom, prix unitaire, quantité −/+, sous-total, supprimer), récapitulatif total, boutons « Continuer mes achats » / « Valider ma commande ». État vide illustré avec liens vers les 2 univers. On ne peut pas dépasser le stock disponible.
5. **Connexion / Inscription** : une page, deux onglets (comme aujourd'hui), messages d'erreur clairs (identifiants incorrects, e-mail déjà utilisé). Après connexion : retour à la page d'où l'on venait (ou accueil) ; la nav affiche le prénom et « Se déconnecter ».
6. **Validation de commande** : si non connecté → redirection vers la connexion puis retour au panier. Récapitulatif (articles, total, adresse du client), bouton « Confirmer », puis page de confirmation (n° de commande). Garder la logique existante de `validerPanier.php` : création de `COMMANDE`, lignes `COMPORTER`, décrément du stock, échec propre si stock insuffisant — mais dans une **transaction** pour ne pas laisser de commande à moitié créée.

### Admin (`/admin`, réservé aux comptes `role = 'admin'` ou `role = 'demo'`)
Même tokens et même typo, nav admin propre (Tableau de bord, Produits, Nouvelle commande, retour au site). Couleur neutre (vert foncé) ; les étiquettes de catégorie reprennent la couleur de leur univers.

**Mode démo en lecture seule — décision de Mathis.** Les recruteurs doivent pouvoir visiter l'admin du site en ligne sans rien casser :
- Un compte `role = 'demo'` (identifiants publiés dans le README et sur la page de connexion, ex. bouton « Visiter l'admin en mode démo ») voit **toutes** les pages admin, formulaires compris.
- Toute action qui modifie (créer/modifier/supprimer un produit, upload de photo, passer une commande pour un client) est **refusée côté serveur** pour ce rôle, avec un toast « Mode démo : les modifications sont désactivées ». Les boutons restent visibles (pour montrer l'interface) ; un bandeau permanent « Mode démo — lecture seule » est affiché en haut de l'admin.
- Le vrai compte `admin` (droits complets) existe aussi ; son mot de passe n'est pas publié.

7. **Tableau de bord stock** : tuiles de chiffres (total produits, par univers, nombre de produits en stock faible ≤ 5 et en rupture), puis tableau des produits (photo, nom, catégorie, animal, marque, prix, stock) avec recherche, filtre par catégorie et mise en évidence du stock faible/rupture. Remplace les 10 requêtes `COUNT` répétées par une seule requête groupée.
8. **Créer / modifier un produit** : un seul formulaire (nom, description, prix, stock, catégorie, marque, **animal** — affiché seulement si la catégorie est de l'univers animalier, avec l'option « Tous animaux » —, **photo** — l'upload de `insererPhoto.php` est intégré ici —, **caractéristiques** : liste de lignes libellé/valeur/icône à ajouter ou retirer). Validation côté serveur, messages d'erreur sous les champs.
9. **Supprimer un produit** : confirmation avant suppression ; si le produit figure dans des commandes, le refuser proprement (message) plutôt que de casser les clés étrangères — ou le passer en stock 0 (au choix du constructeur, à documenter).
10. **Nouvelle commande pour un client** (reprend `FormulaireCommande.php` / `passer-commande.php`) : choix du client, choix des produits en stock et des quantités, puis récapitulatif. Même logique transactionnelle que la validation côté client.

## 7. Données et sécurité

**Nouvelle base, pas de migration** (décision de Claude) : la refonte utilise une **nouvelle base `pivert`**, construite entièrement par `sql/schema.sql` + `sql/donnees.sql`. La base locale `sae_203` n'est ni migrée ni modifiée : elle continue de faire tourner l'ancien site pour les captures « avant ». Aucune donnée réelle à conserver (ce sont des données de démo), donc pas de conversion de mots de passe existants.

**Changements de schéma par rapport à `creation_table .sql`** (directement dans `sql/schema.sql`) :
- `CATEGORIE.univers ENUM('vegetal','animalier') NOT NULL` — catégories 1 à 5 = végétal, 6 à 10 = animalier (d'après `insertion_Profil.sql`). Remplace les noms de catégories écrits en dur dans les requêtes.
- `CATEGORIE.slug` (ex. `fleurs`) et `CATEGORIE.libelle` lisible (« Outils de jardinage » au lieu de `Outils_de_jardinage`) pour l'affichage.
- `PRODUIT.espece ENUM('chiens','chats','rongeurs','oiseaux','poissons','autres') NULL` : renseigné pour les produits animaliers (`NULL` = tous animaux ; `NULL` aussi pour les produits végétaux). D'après les données : lapin, cochon d'Inde → rongeurs ; poulailler (poules), terrarium (reptiles) → autres ; fontaine à eau, brosse de toilettage → `NULL`.
- Nouvelle table `CARACTERISTIQUE (id_caracteristique PK, id_produit FK → PRODUIT ON DELETE CASCADE, libelle, valeur, icone, ordre)`.
- `CLIENT.role ENUM('client','admin','demo') NOT NULL DEFAULT 'client'` + un compte `admin` et un compte `demo` dans les données (voir mode démo, §6).
- `CLIENT.mot_de_passe` stocke un hash `password_hash()` (le `VARCHAR(255)` actuel suffit).

**Données de démonstration** (`sql/donnees.sql`) : reprendre les 51 produits de `insertion_Profil.sql` (sans en inventer d'autres) en corrigeant les lignes cassées, et renseigner : `photo_nom` avec les photos lisibles de `brutes/` (copiées dans le nouveau projet, renommées en minuscules sans accents ni espaces ; un produit sans photo correspondante reçoit une image par défaut), `date_ajout`, 2 à 4 caractéristiques plausibles par produit. Ajouter un client de démo et un admin de démo, avec des mots de passe hachés (hash généré une fois par un petit script PHP et collé dans le SQL). Le site doit être entièrement démontrable à partir de ces deux scripts, sans la base locale de Mathis. La page de confirmation de commande utilise `prenom` (le `prenom_client` actuel est un bug).

**Sécurité** (proposition de Claude, Mathis ne s'y est pas opposé — à confirmer à la relecture) :
- Toutes les requêtes avec des données venant de l'utilisateur en requêtes préparées `mysqli`.
- `password_hash` / `password_verify` ; `session_regenerate_id()` à la connexion.
- Échappement de toutes les sorties (autoescape Twig activé, jamais `|raw` sur des données).
- Jeton CSRF sur tous les formulaires `POST` (panier, connexion, inscription, commande, admin).
- Actions qui modifient (ajout/modif/suppression panier, admin) en `POST`, pas en lien `GET`.
- Upload photo : vérification du type réel (MIME), taille max, nom généré.
- Garde d'accès admin sur toutes les pages `/admin` (rôles `admin` et `demo`), et garde d'écriture (rôle `admin` seulement) sur toutes les actions qui modifient.
- Identifiants de base dans `config.php` non versionné (fourni : `config.exemple.php`).

## 8. Organisation du code (décidée)

Nouveau dossier `site_a_ameliorer/pivert/`, futur dépôt GitHub :

```
pivert/
  public/              ← racine web (AlwaysData pointera ici)
    index.php  univers.php  panier.php  commande.php  compte.php  ...
    admin/index.php  admin/produit.php  admin/commande.php  ...
    assets/css/        tokens, base, composants, pages
    assets/js/         modale-produit, recherche, panier, admin
    images/produits/
  src/                 bootstrap (session, base, Twig), fonctions (auth, panier, csrf, requêtes)
  templates/           base.twig, partials/ (nav, footer, carte-produit, modale-produit, toast),
                       pages/, admin/ (layout admin qui étend base)
  sql/                 schema.sql, donnees.sql
  config.exemple.php   composer.json   README.md
```

- Un `src/bootstrap.php` unique remplace les 3 `connexion.php` (connexion `mysqli` en `utf8mb4`, `mysqli_report` strict, aucun `echo`).
- Les fichiers de `public/` font : bootstrap → lecture/validation des entrées → requêtes → `render()` du template. Aucun HTML dans le PHP.
- **README** : présentation du projet, captures avant/après, stack, installation locale (XAMPP), compte de démo, et crédits : « Projet d'origine : SAÉ 203 (BUT MMI 1, 2025-2026), en binôme avec Korantin Mercuzot. Refonte complète du front et de l'admin : Mathis Beaussier, 2026. »

## 9. Laissé au constructeur

- Le découpage exact des fichiers CSS/JS et des templates Twig, les noms de fonctions.
- Les textes d'interface (en français, ton direct et chaleureux comme la maquette : tutoiement côté client).
- Le choix des icônes SVG et des caractéristiques plausibles par produit.
- Le comportement de la suppression d'un produit déjà commandé (§6.9), à documenter dans le README.
- Les points de rupture responsive (la maquette donne la version bureau ; l'adaptation mobile est à concevoir : nav en menu burger, bento en 2 colonnes puis 1).
- Le niveau de tests : au minimum `php -l` sur tous les fichiers et un parcours manuel complet (parcours client + admin) en local, consigné dans le README.

## 10. Décisions prises par Claude (à vérifier par Mathis)

- CSS maison avec variables plutôt que Tailwind.
- Les corrections de sécurité du §7 font partie de la refonte.
- Nouvelle table `CARACTERISTIQUE`, colonnes `univers` / `slug` / `libelle` sur `CATEGORIE`, `role` sur `CLIENT`.
- Une seule page `univers.php` pour les deux univers (au lieu de deux pages séparées).
- Nouveau dossier `pivert/`, anciens dossiers intacts pour le « avant ».
- Carrousel de l'accueil remplacé par la grille bento ; accordéons remplacés par des puces de catégories.
- Pas d'espace « Mon compte » dans cette refonte.
- Nouvelle base `pivert` reconstruite par scripts ; `sae_203` intacte ; pas de migration ni de produits inventés.
- Animalier : catégorie « Autres animaux » pour poules et reptiles ; produits « tous animaux » visibles dans chaque filtre.
- Mode démo : boutons visibles mais actions refusées côté serveur, bandeau « lecture seule ».
