# Refonte PiVert faunes & flores — plan de réalisation

> **Pour les agents :** SOUS-SKILL REQUIS : utiliser superpowers:subagent-driven-development (recommandé) ou superpowers:executing-plans pour réaliser ce plan tâche par tâche. Les étapes utilisent des cases à cocher (`- [ ]`).

**Objectif :** reconstruire PiVert (site client + admin de stock) dans un nouveau projet `pivert/`, en PHP + Twig, avec la direction visuelle « Serre » v2 (une couleur d'accent par univers), sécurisé et démontrable à partir de scripts SQL.

**Architecture :** PHP 8 procédural. Les fichiers de `public/` sont de minces contrôleurs : `bootstrap` → lecture/validation des entrées → fonctions de `src/` → `rendu()` d'un template Twig. Toute la logique (base, panier, commande, auth, validation) vit dans des fonctions de `src/`, testées avec PHPUnit sur une base `pivert_test`. CSS maison à variables, thème piloté par `data-univers` sur `<body>`. JS natif en modules ES, fonctions pures testées avec `node --test`.

**Stack :** PHP 8.2 (XAMPP `C:\Xampp2\xampp`), MariaDB 10.4 (`mysqli`), Twig 3, PHPUnit 11, Node 24 (tests JS seulement), Git.

**Spec :** `site_a_ameliorer/docs/2026-11-17-refonte-pivert-design.md` — à lire en entier avant toute tâche. Maquette de référence : `site_a_ameliorer/.superpowers/brainstorm/967-1794903192/content/serre-v2.html` (à ouvrir dans un navigateur).

**Chemins :** `SITE = C:\Users\mathi\OneDrive - Université de Tours\Bureau\cours blois\2eme année\Perso\site_a_ameliorer`. Le projet est `SITE\pivert\`. Toutes les commandes se lancent depuis `SITE\pivert\` (Git Bash). Ne **jamais** modifier `rendu_jaqueline`, `rendu-Deniau`, `rendu_najet` (lecture seule). Le dossier est sur OneDrive : éviter les `grep -r` sur tout `SITE`.

## Contraintes globales

- PHP procédural + `mysqli` en **requêtes préparées uniquement** pour toute valeur venant de l'utilisateur. Pas de framework, pas de Tailwind, pas de React.
- Aucun HTML dans les fichiers PHP de `public/` : tout passe par Twig, autoescape activé, jamais `|raw` sur des données.
- Tokens exacts : `--vert: #12351F`, `--fond: #EEF2E6`, `--blanc: #FFFFFF`, `--citron: #C6F05A`, `--corail: #FF8A66`. `body[data-univers="vegetal"]` → `--accent: var(--citron)`, `body[data-univers="animalier"]` → `--accent: var(--corail)`.
- Citron et corail **seulement en fond**, avec du texte `--vert` dessus ; texte citron/corail permis uniquement sur fond `--vert`.
- Police : Bricolage Grotesque (Google Fonts) 400/500/600/800 ; titres en 800, `letter-spacing: -0.03em`. Cartes arrondies 22–28 px, boutons/étiquettes en pilule. Icônes SVG en ligne, pas d'emojis.
- Textes d'interface en français, tutoiement côté client, vouvoiement nulle part (ton de la maquette). `lang="fr"` partout.
- Toute action qui modifie se fait en `POST` avec jeton CSRF. Les rôles : `client`, `admin`, `demo` ; `demo` voit l'admin mais toute écriture admin est refusée côté serveur avec le message exact : « Mode démo : les modifications sont désactivées ».
- Mobile d'abord : chaque page doit être utilisable à 360 px de large sans défilement horizontal (vérifié à chaque tâche de page).
- Base de l'appli : `pivert` ; base des tests : `pivert_test`. Identifiants dans `config.php` (non versionné).
- Crédit obligatoire (footer + README) : « Projet d'origine : SAÉ 203 (BUT MMI 1, 2025-2026), en binôme avec Korantin Mercuzot. Refonte complète du front et de l'admin : Mathis Beaussier, 2026. »

## Points de vigilance (Review Focus)

1. **Panier avec un produit supprimé** depuis (id en session qui n'existe plus) → la ligne disparaît du panier sans erreur. Test : Tâche 8, `test_detail_ignore_produit_inexistant`.
2. **Quantité trafiquée** (`qte=-3`, `0`, `abc`, `99999`) envoyée en POST → bornée entre 1 et le stock (0 ou moins = retrait), jamais d'erreur SQL. Test : Tâche 8, `test_ajouter_borne_quantite`.
3. **Recherche avec accents/casse/apostrophes** (« erable » trouve « Érable », « cochon d'inde » trouve « cochon d inde ») → correspondance. Test : Tâche 5, `recherche.test.js`.
4. **Paramètres d'URL inconnus** (`univers.php?u=xyz`, `&filtre=licornes`) → `u` invalide redirige vers l'accueil, `filtre` invalide est ignoré (affiche « Tout »). Test : Tâche 5, `test_filtre_invalide_ignore`.
5. **Compte démo qui envoie un POST admin à la main** (sans passer par l'interface) → refusé côté serveur, rien n'est modifié. Test : Tâche 10, `test_demo_ne_peut_pas_ecrire` + vérif curl Tâche 11.

---

## Structure des fichiers

```
pivert/
  composer.json  package.json  phpunit.xml  .gitignore  config.exemple.php  README.md
  docs/                         copie de la spec et du plan
  outils/installer-base.php     (re)crée une base depuis sql/ — usage : php outils/installer-base.php [--test]
  outils/creer-admin.php        crée/maj un compte admin — usage : php outils/creer-admin.php email motdepasse prenom nom
  sql/schema.sql  sql/donnees.sql
  src/
    bootstrap.php     charge config, session, inclut tous les fichiers src
    config.php        config(): array
    db.php            db(), db_tous(), db_un(), db_executer()
    installation.php  installer_base()
    http.php          rediriger(), est_post(), entier_post(), url_retour_sure()
    flash.php         flash(), flashs_lire()
    csrf.php          csrf_jeton(), csrf_valide(), exiger_csrf()
    vue.php           twig(), rendu_chaine(), rendu()
    catalogue.php     constantes UNIVERS/ESPECES + lecture produits, catégories, caractéristiques, stats
    conseils.php      conseils(), conseil_du_jour()
    panier.php        fonctions pures de panier + panier_detail()
    commande.php      commande_creer(), commande_par_id()
    auth.php          inscription, connexion, rôles, gardes
    admin_produits.php  valider_produit(), valider_photo(), produit_enregistrer(), caracteristiques_remplacer(), produit_supprimer()
  public/
    index.php univers.php conseils.php panier.php panier-action.php compte.php deconnexion.php
    commande.php commande-confirmee.php
    admin/index.php admin/produit.php admin/produit-supprimer.php admin/commande.php
    assets/css/tokens.css base.css composants.css pages.css admin.css
    assets/js/recherche.js modale-produit.js modale-conseil.js nav.js admin-produit.js admin-tableau.js
    images/produits/  images/site/
  templates/
    base.twig
    partials/nav.twig footer.twig toasts.twig icones.twig carte-produit.twig modale-produit.twig carte-conseil.twig modale-conseil.twig
    pages/accueil.twig univers.twig conseils.twig panier.twig compte.twig commande.twig commande-confirmee.twig
    admin/layout.twig tableau-de-bord.twig produit-form.twig commande-form.twig commande-recap.twig
  tests/
    bootstrap.php  BaseTestCase.php  (tests PHPUnit *Test.php)
    js/*.test.js
```

**Commandes de test** (depuis `pivert/`) :
- PHP : `php vendor/bin/phpunit` (une classe : `php vendor/bin/phpunit --filter PanierTest`).
- JS : `node --test tests/js/`.
- MySQL doit tourner : `/c/Xampp2/xampp/mysql_start.bat` (lancer en arrière-plan) ou via `xampp-control.exe`.
- Vérif manuelle d'une page : `php -S 127.0.0.1:8080 -t public` (en arrière-plan), puis `curl`.

---

### Task 1: Socle du projet (dépôt, Composer, config, base de données d'accès, tests)

**Fichiers :**
- Créer : `pivert/composer.json`, `package.json`, `phpunit.xml`, `.gitignore`, `config.exemple.php`, `config.php`, `src/bootstrap.php`, `src/config.php`, `src/db.php`, `src/installation.php`, `outils/installer-base.php`, `sql/schema.sql` (provisoire : une table `ping (id INT PRIMARY KEY)` remplacée en Tâche 2), `sql/donnees.sql` (provisoire : vide), `tests/bootstrap.php`, `tests/BaseTestCase.php`, `docs/` (copie de la spec et du plan)
- Test : `tests/DbTest.php`

**Interfaces :**
- Produit :
  - `config(): array` — lit `config.php` à la racine (`return ['db' => [...], 'db_test' => [...]]`, chaque entrée `host`, `utilisateur`, `mot_de_passe`, `base`) ; renvoie la config avec `db` remplacé par `db_test` si `getenv('PIVERT_ENV') === 'test'`.
  - `db(): mysqli` — connexion unique (static), `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)`, charset `utf8mb4`, aucun `echo`.
  - `db_tous(string $sql, string $types = '', array $params = []): array` (lignes assoc), `db_un(...): ?array`, `db_executer(...): int` (lignes affectées). Toujours `prepare` + `bind_param` quand `$params` n'est pas vide.
  - `installer_base(mysqli $cnx, string $nomBase, string $dossierSql): void` — `DROP DATABASE IF EXISTS`, `CREATE DATABASE … CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`, `USE`, exécute `schema.sql` puis `donnees.sql` avec `multi_query` en vidant tous les résultats.
  - `BaseTestCase::reinstallerBase(): void` (statique) — ouvre sa **propre** connexion `mysqli` sans base, appelle `installer_base` sur `pivert_test`, puis `db()->select_db('pivert_test')` pour que la connexion partagée retrouve la base recréée ; `tests/bootstrap.php` l'appelle une fois au démarrage (avant tout appel à `db()`) et vide `$_SESSION`. Les classes de test qui écrivent en base l'appellent dans `setUp()`.
  - `bootstrap.php` : `require vendor/autoload.php`, `session_start()` seulement si `PHP_SAPI !== 'cli'`, `require_once` de tous les fichiers `src/*.php`.

- [ ] **Étape 1 : initialiser le dépôt** — `mkdir pivert && cd pivert && git init -b main`. `.gitignore` : `/vendor/`, `/node_modules/`, `/config.php`, `.phpunit.cache/`, `public/images/produits/upload-*`. `composer.json` : `require` `twig/twig ^3.14`, `require-dev` `phpunit/phpunit ^11`. `package.json` : `{"private": true, "type": "module", "scripts": {"test": "node --test tests/js/"}}`. `phpunit.xml` : bootstrap `tests/bootstrap.php`, dossier `tests`, `<env name="PIVERT_ENV" value="test" force="true"/>`. `config.exemple.php` et `config.php` : `localhost`, `root`, `''`, bases `pivert` / `pivert_test`. Lancer `composer install`. Copier la spec et ce plan dans `docs/`.
- [ ] **Étape 2 : écrire le test qui échoue** — `tests/DbTest.php` :

```php
public function test_connexion_sur_base_de_test(): void {
    $this->assertSame('pivert_test', db_un('SELECT DATABASE() AS b')['b']);
}
public function test_requete_preparee_avec_parametre(): void {
    db_executer('INSERT INTO ping (id) VALUES (?)', 'i', [42]);
    $this->assertSame(42, (int) db_un('SELECT id FROM ping WHERE id = ?', 'i', [42])['id']);
    $this->assertSame([], db_tous('SELECT id FROM ping WHERE id = ?', 'i', [7]));
}
```

- [ ] **Étape 3 : lancer** `php vendor/bin/phpunit --filter DbTest` → ÉCHEC (`db_un` non défini).
- [ ] **Étape 4 : implémenter** `src/config.php`, `src/db.php`, `src/installation.php`, `src/bootstrap.php`, `tests/bootstrap.php`, `tests/BaseTestCase.php`, `outils/installer-base.php` (option `--test` → base `db_test`).
- [ ] **Étape 5 : lancer** `php vendor/bin/phpunit --filter DbTest` → OK (2 tests).
- [ ] **Étape 6 : commit** — `git add -A && git commit -m "chore: socle du projet (composer, config, accès base, tests)"`

---

### Task 2: Schéma et données de démonstration

**Fichiers :**
- Modifier : `sql/schema.sql`, `sql/donnees.sql`
- Créer : `outils/creer-admin.php`, `public/images/produits/*` (photos copiées), `public/images/produits/defaut.svg`, `public/images/site/` (`monstera.jpg`, `chat.jpg`, `photo-koko.png`, `photo-mathis.png`)
- Test : `tests/DonneesTest.php`

**Interfaces :**
- Consomme : `installer_base()`, `db_tous()`, `db_un()` (Tâche 1).
- Produit — schéma (noms de tables en MAJUSCULES comme l'original, colonnes en minuscules) :
  - `CATEGORIE (id_categorie, nom_categorie, slug UNIQUE, libelle, univers ENUM('vegetal','animalier') NOT NULL)`.
  - `MARQUE` inchangée (`id_marque`, `nomMarque` → renommer `nom_marque`).
  - `PRODUIT` : colonnes d'origine + `espece ENUM('chiens','chats','rongeurs','oiseaux','poissons','autres') NULL`, `photo_nom VARCHAR(255) NULL`, `date_ajout DATE NOT NULL`, `CHECK (quantite >= 0)`.
  - `CARACTERISTIQUE (id_caracteristique PK AI, id_produit FK ON DELETE CASCADE, libelle VARCHAR(60), valeur VARCHAR(60), icone VARCHAR(30), ordre TINYINT)`.
  - `CLIENT` : colonnes d'origine + `role ENUM('client','admin','demo') NOT NULL DEFAULT 'client'`, `date_inscription DATE NOT NULL`.
  - `COMMANDE`, `COMPORTER` : comme l'original (`statuss` → renommer `statut`). Les tables `PANIER` / `CONTENIR` ne sont **pas** reprises (le panier vit en session).
  - Toutes les tables en InnoDB.
- Produit — données : les 10 catégories (`slug` / `libelle` / `univers`) :

| id | slug | libelle | univers |
|---|---|---|---|
| 1 | plantes | Plantes | vegetal |
| 2 | fleurs | Fleurs | vegetal |
| 3 | arbres | Arbres | vegetal |
| 4 | outils-jardinage | Outils de jardinage | vegetal |
| 5 | decoration-jardin | Décoration de jardin | vegetal |
| 6 | animaux | Animaux | animalier |
| 7 | alimentation | Alimentation | animalier |
| 8 | sante | Santé et bien-être | animalier |
| 9 | accessoires | Accessoires et loisirs | animalier |
| 10 | habitat | Habitat | animalier |

  - Les 51 produits de `rendu_najet/insertion_Profil.sql` (supprimer la valeur `''` en trop des lignes animalières ; noms avec majuscule initiale, accents et apostrophes corrects : « Cochon d'Inde », « Fontaine à eau pour animaux », « Croquettes pour chat », « Laisse pour chien » — les tests de la Tâche 5 utilisent ces noms exacts), `date_ajout` étalées entre `2026-01-05` et `2026-11-10`.
  - `espece` des produits animaliers : chiens = chien, croquettes pour chien, tapis rafraîchissant, laisse, jouet pour chien ; chats = chat, croquettes pour chat, jouet pour chat ; rongeurs = lapin, cochon d'Inde, foin, coupe-griffe, boule sensorielle, tunnel, cage à rongeurs ; poissons = poisson combattant, nourriture pour poissons, pompe à aquarium, aquarium ; oiseaux = graines, cage à oiseaux ; autres = poulailler, terrarium ; `NULL` = fontaine à eau, brosse de toilettage.
  - Photos : copier depuis `rendu_jaqueline/imagesBaseDonnées/brutes/` vers `public/images/produits/`, renommées en minuscules ASCII avec tirets (`erable.webp`, `souffleur-de-feuilles.webp`) ; une seule version par produit ; produit sans photo → `photo_nom = NULL` (le template affichera `defaut.svg`).
  - 2 à 4 caractéristiques plausibles par produit. Icônes autorisées (noms de la macro de la Tâche 3) : `soleil`, `goutte`, `regle`, `thermometre`, `sablier`, `assiette`, `maison`, `patte`, `feuille`, `outil`.
  - Comptes : `client@pivert.demo` / `demo1234` (role `client`, prénom « Camille ») et `recruteur@pivert.demo` / `recruteur2027` (role `demo`, prénom « Recruteur »). Hachés avec `php -r "echo password_hash('…', PASSWORD_DEFAULT);"` et collés dans le SQL. **Pas de compte `admin` dans `donnees.sql`** : il se crée avec `outils/creer-admin.php` (son mot de passe n'est jamais versionné).
  - `creer_admin` : `php outils/creer-admin.php email motdepasse prenom nom` → insère ou met à jour (role `admin`, mot de passe haché), adresse « — ».

- [ ] **Étape 1 : écrire les tests qui échouent** — `tests/DonneesTest.php` :

```php
public function test_51_produits_et_10_categories_reparties(): void {
    $this->assertSame(51, (int) db_un('SELECT COUNT(*) n FROM PRODUIT')['n']);
    $parUnivers = array_column(db_tous('SELECT univers, COUNT(*) n FROM CATEGORIE GROUP BY univers'), 'n', 'univers');
    $this->assertEquals(['animalier' => 5, 'vegetal' => 5], $parUnivers);
}
public function test_chaque_photo_existe_sur_le_disque(): void {
    foreach (db_tous('SELECT photo_nom FROM PRODUIT WHERE photo_nom IS NOT NULL') as $p) {
        $this->assertFileExists(__DIR__ . '/../public/images/produits/' . $p['photo_nom']);
    }
}
public function test_espece_seulement_sur_produits_animaliers(): void {
    $this->assertSame(0, (int) db_un("SELECT COUNT(*) n FROM PRODUIT p JOIN CATEGORIE c USING(id_categorie) WHERE c.univers='vegetal' AND p.espece IS NOT NULL")['n']);
    $this->assertSame(2, (int) db_un("SELECT COUNT(*) n FROM PRODUIT p JOIN CATEGORIE c USING(id_categorie) WHERE c.univers='animalier' AND p.espece IS NULL")['n']);
}
public function test_deux_a_quatre_caracteristiques_par_produit(): void {
    $hors = db_un('SELECT COUNT(*) n FROM (SELECT p.id_produit, COUNT(c.id_caracteristique) k FROM PRODUIT p LEFT JOIN CARACTERISTIQUE c USING(id_produit) GROUP BY p.id_produit HAVING k < 2 OR k > 4) t');
    $this->assertSame(0, (int) $hors['n']);
}
public function test_comptes_de_demo_hachés_et_aucun_admin(): void {
    $demo = db_un("SELECT * FROM CLIENT WHERE email = 'recruteur@pivert.demo'");
    $this->assertSame('demo', $demo['role']);
    $this->assertTrue(password_verify('recruteur2027', $demo['mot_de_passe']));
    $this->assertSame(0, (int) db_un("SELECT COUNT(*) n FROM CLIENT WHERE role = 'admin'")['n']);
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter DonneesTest` → ÉCHEC.
- [ ] **Étape 3 : écrire** `sql/schema.sql`, `sql/donnees.sql`, copier les photos, créer `defaut.svg` (silhouette de feuille vert foncé sur fond `--fond`), `outils/creer-admin.php` ; supprimer la table `ping` et adapter `DbTest` pour lire `CATEGORIE` au lieu de `ping`.
- [ ] **Étape 4 : lancer** `php vendor/bin/phpunit` → OK. Puis `php outils/installer-base.php` → base `pivert` créée sans erreur.
- [ ] **Étape 5 : commit** — `git add -A && git commit -m "feat: schéma et données de démo (catégories par univers, espèces, caractéristiques)"`

---

### Task 3: Système visuel, gabarit Twig, flash et CSRF

**Fichiers :**
- Créer : `src/http.php`, `src/flash.php`, `src/csrf.php`, `src/vue.php`, `templates/base.twig`, `templates/partials/{nav,footer,toasts,icones}.twig`, `public/assets/css/{tokens,base,composants}.css`, `public/assets/js/nav.js`, `public/index.php` (provisoire : page vide qui étend `base.twig`)
- Test : `tests/VueTest.php`, `tests/CsrfTest.php`, `tests/js/contraste.test.js`

**Interfaces :**
- Produit :
  - `rediriger(string $url): never`, `est_post(): bool`, `entier_post(string $cle, int $defaut = 0): int` (`filter_var` FILTER_VALIDATE_INT, sinon `$defaut`), `url_retour_sure(?string $url, string $defaut = 'index.php'): string` (n'accepte qu'un chemin relatif sans `//`, sans schéma, sans `\`).
  - `flash(string $type, string $message): void` (`$type` ∈ `succes|erreur|info`), `flashs_lire(): array` (vide après lecture).
  - `csrf_jeton(): string` (64 hex, stocké en session, stable pendant la session), `csrf_valide(?string $jeton): bool` (`hash_equals`), `exiger_csrf(): void` (si invalide : flash erreur « Session expirée, réessaie. » + redirection vers `HTTP_REFERER` sûr ou accueil).
  - `twig(): \Twig\Environment` (templates `templates/`, autoescape html, `strict_variables` false, cache désactivé), filtre `prix` (`24.9` → `24,90 €` avec espace insécable `\u{202F}` avant €), fonction `image_produit(?string $photo): string` (→ `images/produits/{photo}` ou `images/produits/defaut.svg`).
  - `rendu_chaine(string $template, array $vars = []): string` — ajoute les variables globales `client` (`$_SESSION['client'] ?? null`), `panier_nombre` (somme des quantités de `$_SESSION['panier']`), `flashs` (`flashs_lire()`), `csrf` (`csrf_jeton()`), `univers` (`$vars['univers'] ?? null`), `base_url` (`''` côté client, `'../'` dans l'admin — passé par `$vars`). `rendu(...)` : `echo rendu_chaine(...)`.
  - `base.twig` : blocs `titre`, `contenu`, `scripts` ; `<body data-univers="{{ univers }}">` si `univers` défini ; liens CSS `tokens`, `base`, `composants`, `pages` ; Google Fonts Bricolage Grotesque ; inclut nav, toasts, footer.
  - `icones.twig` : macro `icone(nom, classe = '')` → `<svg aria-hidden="true">` trait 1.75, 24×24, pour : `panier compte recherche plus moins fermer menu fleche soleil goutte regle thermometre sablier assiette maison patte feuille outil poubelle crayon alerte` (tracés Lucide, licence ISC citée dans le README).
  - Tous les liens et fichiers (CSS, JS, images) de `base.twig`, `nav.twig`, `footer.twig` sont préfixés par `{{ base_url }}` pour fonctionner depuis `/admin`.
  - `nav.twig` : pilule blanche (logo « **Pi**Vert » où « Pi » a le fond `--accent`, ou citron hors univers), liens Végétal / Animalier / Conseils (`conseils.php`) / Contact (`index.php#contact`), lien actif en fond `--accent` (selon `univers`), compte (prénom + bouton POST « Se déconnecter » si connecté, sinon « Connexion »), bouton panier vert foncé avec `panier_nombre` en citron dans `<span class="nav-panier-nombre">`. Sous 760 px : bouton menu (`nav.js` bascule `aria-expanded`).
  - `toasts.twig` : une pilule par flash, `role="status"`, disparaît après 4 s (CSS).
  - `footer.twig` : contact court, liens, ligne de crédit exacte des Contraintes globales.

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
// VueTest
public function test_body_porte_le_theme_d_univers(): void {
    $html = rendu_chaine('base.twig', ['univers' => 'animalier']);
    $this->assertStringContainsString('data-univers="animalier"', $html);
}
public function test_badge_panier_somme_des_quantites(): void {
    $_SESSION['panier'] = [3 => 2, 8 => 1];
    $this->assertMatchesRegularExpression('/class="nav-panier-nombre"[^>]*>3</', rendu_chaine('base.twig'));
}
public function test_flash_affiche_puis_consomme(): void {
    flash('succes', 'Ajouté au panier');
    $this->assertStringContainsString('Ajouté au panier', rendu_chaine('base.twig'));
    $this->assertSame([], flashs_lire());
}
public function test_filtre_prix(): void {
    $this->assertSame("24,90\u{202F}€", twig()->createTemplate('{{ 24.9|prix }}')->render());
}
public function test_url_retour_sure_refuse_les_urls_externes(): void {
    $this->assertSame('index.php', url_retour_sure('https://evil.test'));
    $this->assertSame('index.php', url_retour_sure('//evil.test'));
    $this->assertSame('panier.php', url_retour_sure('panier.php'));
}
// CsrfTest
public function test_jeton_stable_et_verifie(): void {
    $_SESSION = [];
    $j = csrf_jeton();
    $this->assertSame($j, csrf_jeton());
    $this->assertTrue(csrf_valide($j));
    $this->assertFalse(csrf_valide('faux'));
    $this->assertFalse(csrf_valide(null));
}
```

```js
// tests/js/contraste.test.js — ratio WCAG entre paires de tokens (couleurs copiées de tokens.css)
test('vert sur citron ≥ 7', () => assert.ok(ratio('#12351F', '#C6F05A') >= 7));
test('vert sur corail ≥ 4.5', () => assert.ok(ratio('#12351F', '#FF8A66') >= 4.5));
test('vert sur fond ≥ 7', () => assert.ok(ratio('#12351F', '#EEF2E6') >= 7));
test('citron sur blanc < 3 (interdit en texte)', () => assert.ok(ratio('#C6F05A', '#FFFFFF') < 3));
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter "VueTest|CsrfTest"` et `node --test tests/js/` → ÉCHEC.
- [ ] **Étape 3 : implémenter** les fichiers `src/`, templates, CSS (tokens + reset + typographie + composants `.pilule`, `.bouton` (`--accent` en fond), `.carte`, `.etiquette`, `.toast`, focus visible `outline: 3px solid var(--vert)` décalé de 2 px) et `nav.js` ; la fonction `ratio(hex1, hex2)` vit dans le fichier de test.
- [ ] **Étape 4 : lancer** les deux commandes → OK. Vérif : `php -S 127.0.0.1:8080 -t public` puis ouvrir `http://127.0.0.1:8080/` à 360 px et 1280 px : nav et footer conformes à la maquette, aucun défilement horizontal.
- [ ] **Étape 5 : commit** — `"feat: système visuel, gabarit Twig, flash et CSRF"`

---

### Task 4: Comptes (inscription, connexion, déconnexion, rôles)

**Fichiers :**
- Créer : `src/auth.php`, `public/compte.php`, `public/deconnexion.php`, `templates/pages/compte.twig`
- Test : `tests/AuthTest.php`

**Interfaces :**
- Consomme : `db_*`, `exiger_csrf()`, `flash()`, `rediriger()`, `url_retour_sure()`, `rendu()`.
- Produit :
  - `valider_inscription(array $post): array{donnees: array, erreurs: array<string,string>}` — champs `nom`, `prenom`, `email` (FILTER_VALIDATE_EMAIL), `mot_de_passe` (≥ 8 caractères), `adresse` ; tout `trim` ; erreurs indexées par champ.
  - `inscrire(array $donnees): int|string` — id du client, ou `'email_pris'` si l'email existe ; mot de passe `password_hash`, `date_inscription = CURDATE()`, role `client`.
  - `connecter(string $email, string $motDePasse): ?array` — `password_verify` ; en cas de succès : `session_regenerate_id(true)` si session active, `$_SESSION['client'] = ['id_client','prenom','nom','email','adresse','role']` (jamais le hash).
  - `deconnecter(): void` — vide la session client, garde le panier.
  - `client_connecte(): ?array`, `peut_voir_admin(?array $c): bool` (`admin` ou `demo`), `peut_modifier_admin(?array $c): bool` (`admin` seulement).
  - `compte.php` : GET → onglets Connexion / Inscription (onglet actif via `?onglet=inscription`), garde `?retour=` ; POST `action=connexion|inscription`, précédé de `exiger_csrf()` ; succès → `rediriger(url_retour_sure($_POST['retour']))` + flash « Bienvenue, {prénom} ! » ; échec connexion → message « E-mail ou mot de passe incorrect. » ; inscription : erreurs sous les champs, valeurs saisies conservées (sauf mot de passe). Encadré « Visiter l'admin en mode démo » avec les identifiants `recruteur@pivert.demo` / `recruteur2027` et un bouton qui pré-remplit le formulaire de connexion.
  - `deconnexion.php` : POST + CSRF seulement, puis accueil.

- [ ] **Étape 1 : écrire les tests qui échouent** (`AuthTest extends BaseTestCase`, `setUp` réinstalle la base et vide `$_SESSION`) :

```php
public function test_inscription_puis_connexion(): void {
    $id = inscrire(['nom' => 'Dupont', 'prenom' => 'Léa', 'email' => 'lea@test.fr', 'mot_de_passe' => 'motdepasse1', 'adresse' => '1 rue A']);
    $this->assertIsInt($id);
    $this->assertNotSame('motdepasse1', db_un('SELECT mot_de_passe m FROM CLIENT WHERE id_client = ?', 'i', [$id])['m']);
    $this->assertSame('Léa', connecter('lea@test.fr', 'motdepasse1')['prenom']);
    $this->assertArrayNotHasKey('mot_de_passe', $_SESSION['client']);
}
public function test_email_deja_pris(): void {
    $this->assertSame('email_pris', inscrire(['nom' => 'X', 'prenom' => 'Y', 'email' => 'client@pivert.demo', 'mot_de_passe' => 'motdepasse1', 'adresse' => 'Z']));
}
public function test_mauvais_mot_de_passe(): void {
    $this->assertNull(connecter('client@pivert.demo', 'faux'));
    $this->assertArrayNotHasKey('client', $_SESSION);
}
public function test_validation_inscription(): void {
    $r = valider_inscription(['nom' => '', 'prenom' => 'A', 'email' => 'pas-un-email', 'mot_de_passe' => 'court', 'adresse' => 'B']);
    $this->assertSame(['nom', 'email', 'mot_de_passe'], array_keys($r['erreurs']));
}
public function test_roles(): void {
    $this->assertTrue(peut_voir_admin(['role' => 'demo']));
    $this->assertFalse(peut_modifier_admin(['role' => 'demo']));
    $this->assertTrue(peut_modifier_admin(['role' => 'admin']));
    $this->assertFalse(peut_voir_admin(['role' => 'client']));
    $this->assertFalse(peut_voir_admin(null));
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter AuthTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter** `src/auth.php`, `compte.php`, `deconnexion.php`, `compte.twig` (carte blanche centrée, onglets en pilules, champs arrondis, bouton `--vert` plein).
- [ ] **Étape 4 : lancer** → OK. Vérif navigateur : connexion avec `client@pivert.demo` → la nav affiche « Camille » ; mauvais mot de passe → message ; 360 px OK.
- [ ] **Étape 5 : commit** — `"feat: comptes clients (inscription, connexion, rôles)"`

---

### Task 5: Catalogue et page univers (filtres, recherche, conseils)

**Fichiers :**
- Créer : `src/catalogue.php`, `src/conseils.php`, `public/univers.php`, `public/conseils.php`, `templates/pages/univers.twig`, `templates/pages/conseils.twig`, `templates/partials/{carte-produit,carte-conseil,modale-conseil}.twig`, `public/assets/css/pages.css`, `public/assets/js/{recherche,modale-conseil}.js`
- Test : `tests/CatalogueTest.php`, `tests/js/recherche.test.js`

**Interfaces :**
- Consomme : `db_*`, `rendu()`, `rediriger()`, macro `icone`.
- Produit :
  - `const UNIVERS = ['vegetal' => 'Végétal', 'animalier' => 'Animalier'];`
  - `const ESPECES = ['chiens' => 'Chiens', 'chats' => 'Chats', 'rongeurs' => 'Rongeurs', 'oiseaux' => 'Oiseaux', 'poissons' => 'Poissons', 'autres' => 'Autres animaux'];`
  - `filtres_univers(string $univers): array<string,string>` — slug ⇒ libellé : catégories du végétal (depuis `CATEGORIE`, ordre `id_categorie`) ou `ESPECES` pour l'animalier.
  - `filtre_valide(string $univers, ?string $filtre): ?string` — le slug s'il fait partie de `filtres_univers`, sinon `null`.
  - `produits_univers(string $univers, ?string $filtre): array` — lignes avec `id_produit, nom, description, prix, quantite, photo_nom, espece, slug_categorie, libelle_categorie, univers`, triées par nom ; végétal : filtre sur `c.slug` ; animalier : `p.espece = ? OR p.espece IS NULL`.
  - `caracteristiques_de(array $idsProduits): array<int, list<array{libelle,valeur,icone}>>` — une seule requête, triées par `ordre`.
  - `conseils(string $univers): list<array{icone,titre,texte}>` — données statiques reprises mot pour mot de `rendu_jaqueline/univers-vegetal/univers-vegetal.php` (lignes 267-344, 9 conseils) et `univers-animalier/univers-animalier.php` (section `#formations`), passées au tutoiement ; `conseil_du_jour(DateTimeInterface $d): array` — `conseils` des deux univers fusionnés, index `(int) $d->format('z') % count`.
  - `univers.php` : `u` invalide → `rediriger('index.php')` ; rend `univers.twig` avec `univers`, `filtres`, `filtre_actif`, `produits` (chaque produit enrichi de `caracteristiques`), `conseils`.
  - `carte-produit.twig` : `<article class="carte-produit" data-produit="{{ produit|json_encode|e('html_attr') }}" data-recherche="{{ nom }} {{ libelle_categorie }} {{ espece libellé }}">` ; photo (`image_produit`), étiquette d'univers (sur l'accueil seulement : paramètre `avec_etiquette`), nom, prix, libellé de catégorie, bouton « + » = formulaire POST `panier-action.php` (`action=ajouter`, `id`, `qte=1`, `csrf`, `retour`). Bouton désactivé « Rupture » si `quantite == 0`. Le clic sur la photo ou le nom ouvre la modale produit (Tâche 6).
  - `univers.twig` : bandeau `--accent` (petit « Univers » + grand titre + champ de recherche `type="search"`), puces de filtre (liens `?u=…&filtre=…`, active en `--vert` avec texte `--accent`), grille 4 colonnes → 2 (≤ 760 px), message `#aucun-resultat` « Aucun produit ne correspond à ta recherche. » (masqué par défaut), section `#conseils` en cartes ouvrant la modale conseil.
  - `recherche.js` (module) : `export function normaliser(texte: string): string` (minuscules, sans accents via `normalize('NFD')`, apostrophes et tirets → espace, espaces multiples réduits, `trim`) ; `export function correspond(texteCarte: string, terme: string): boolean` (chaque mot du terme normalisé est inclus dans le texte normalisé ; terme vide → `true`) ; au chargement, branche l'`input` de recherche sur les `.carte-produit[data-recherche]` (attribut `hidden`) et affiche `#aucun-resultat` si tout est masqué.
  - `conseils.php` : les deux listes, en deux sections titrées Végétal / Animalier (pas de `data-univers` sur `body`, chaque section porte son accent via une classe `theme-vegetal` / `theme-animalier` qui redéfinit `--accent`).

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
public function test_filtre_chats_inclut_les_produits_tous_animaux(): void {
    $noms = array_column(produits_univers('animalier', 'chats'), 'nom');
    $this->assertContains('Croquettes pour chat', $noms);
    $this->assertContains('Fontaine à eau pour animaux', $noms);
    $this->assertNotContains('Laisse pour chien', $noms);
}
public function test_filtre_categorie_vegetal(): void {
    $this->assertSame(['fleurs'], array_unique(array_column(produits_univers('vegetal', 'fleurs'), 'slug_categorie')));
}
public function test_filtre_invalide_ignore(): void {
    $this->assertNull(filtre_valide('animalier', 'licornes'));
    $this->assertNull(filtre_valide('vegetal', 'chats'));
    $this->assertSame('chats', filtre_valide('animalier', 'chats'));
    $this->assertCount(25, produits_univers('animalier', null));
}
public function test_caracteristiques_groupees_par_produit(): void {
    $c = caracteristiques_de([1, 2]);
    $this->assertSame([1, 2], array_keys($c));
    $this->assertArrayHasKey('icone', $c[1][0]);
}
public function test_conseil_du_jour_stable(): void {
    $d = new DateTimeImmutable('2026-11-17');
    $this->assertSame(conseil_du_jour($d), conseil_du_jour($d));
}
```

```js
// tests/js/recherche.test.js
import { normaliser, correspond } from '../../public/assets/js/recherche.js';
test('accents et casse', () => assert.ok(correspond('Érable du Japon Arbres', 'erable')));
test('apostrophe', () => assert.ok(correspond("Cochon d'Inde Animaux Rongeurs", 'cochon d inde')));
test('tous les mots requis', () => assert.equal(correspond('Monstera Plantes', 'monstera chat'), false));
test('terme vide', () => assert.ok(correspond('Ficus', '   ')));
test('normaliser', () => assert.equal(normaliser("  Pépinière—d'Été "), 'pepiniere d ete'));
```

  (`recherche.js` ne doit toucher au DOM que si `typeof document !== 'undefined'`, pour être importable par Node.)
- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter CatalogueTest` et `node --test tests/js/` → ÉCHEC.
- [ ] **Étape 3 : implémenter** les fichiers listés, d'après l'écran 2 de la maquette.
- [ ] **Étape 4 : lancer** → OK. Vérif : `curl -s "http://127.0.0.1:8080/univers.php?u=animalier" | grep -c 'data-univers="animalier"'` → `1` ; `curl -s -o /dev/null -w "%{http_code} %{redirect_url}" "http://127.0.0.1:8080/univers.php?u=xyz"` → `302 …index.php`. Navigateur : recherche « erable » en végétal ne garde que l'érable ; filtre Chats ; 360 px OK ; page `conseils.php` OK.
- [ ] **Étape 5 : commit** — `"feat: page univers (filtres, recherche, conseils)"`

---

### Task 6: Pop-up produit

**Fichiers :**
- Créer : `templates/partials/modale-produit.twig`, `public/assets/js/modale-produit.js`
- Modifier : `templates/pages/univers.twig` (inclure la modale + script), `public/assets/css/composants.css`
- Test : `tests/js/modale-produit.test.js`, `tests/VueTest.php` (ajout)

**Interfaces :**
- Consomme : attribut `data-produit` (JSON) de `carte-produit.twig`, macro `icone`, filtre `prix`.
- Produit :
  - `modale-produit.twig` : un seul `<dialog id="modale-produit" aria-labelledby="modale-produit-nom">` : photo, bouton ✕ (`aria-label="Fermer"`), nom, prix (pilule `--accent`), libellé de catégorie (+ animal le cas échéant), description, grille `#modale-produit-caracteristiques`, formulaire POST `panier-action.php` (`action=ajouter`, `id` caché, sélecteur de quantité −/+ borné à `[1, quantite]`, `csrf`, `retour`), bouton « Ajouter au panier » (`--accent`). Un `<template id="modele-caracteristique">` contient le HTML d'une tuile ; les icônes sont pré-rendues dans des `<template data-icone="…">` (un par nom d'icône autorisé) pour que le JS les clone.
  - `modale-produit.js` (module) : `export function vueProduit(p: object): {nom, prix, categorie, description, image, rupture: boolean, qteMax: number, caracteristiques: array}` — mise en forme pure (prix `"24,90 €"` avec `\u202F`, image `images/produits/defaut.svg` si `photo_nom` vide, `rupture = quantite <= 0`, `qteMax = max(quantite, 0)`) ; partie DOM (si `document` existe) : clic sur `.carte-produit [data-ouvrir-modale]` → remplit le dialog avec `textContent` (jamais `innerHTML` avec des données), `showModal()` ; fermeture par ✕, Échap (natif) et clic sur le fond (`event.target === dialog`) ; rupture → bouton désactivé avec le texte « Rupture de stock ».

- [ ] **Étape 1 : écrire les tests qui échouent** :

```js
import { vueProduit } from '../../public/assets/js/modale-produit.js';
const base = { id_produit: 7, nom: 'Lapin nain', prix: '45.00', quantite: 3, photo_nom: 'lapin.jpg', libelle_categorie: 'Animaux', description: 'Calme', caracteristiques: [{ libelle: 'Vie', valeur: '8–10 ans', icone: 'sablier' }] };
test('prix formaté', () => assert.equal(vueProduit(base).prix, '45,00\u202F€'));
test('image par défaut', () => assert.equal(vueProduit({ ...base, photo_nom: null }).image, 'images/produits/defaut.svg'));
test('rupture', () => { const v = vueProduit({ ...base, quantite: 0 }); assert.ok(v.rupture); assert.equal(v.qteMax, 0); });
```

```php
// VueTest — la carte échappe les données injectées dans data-produit
public function test_carte_produit_echappe_le_json(): void {
    $p = ['id_produit' => 1, 'nom' => '"><script>x</script>', 'prix' => 1, 'quantite' => 1, 'photo_nom' => null, 'libelle_categorie' => 'A', 'slug_categorie' => 'a', 'espece' => null, 'description' => '', 'caracteristiques' => []];
    $html = rendu_chaine('partials/carte-produit.twig', ['produit' => $p]);
    $this->assertStringNotContainsString('<script>x', $html);
}
```

- [ ] **Étape 2 : lancer** `node --test tests/js/` et `php vendor/bin/phpunit --filter VueTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter**, d'après l'écran 3 de la maquette (même pop-up, accent selon l'univers de la page).
- [ ] **Étape 4 : lancer** → OK. Vérif navigateur : clic sur un produit → pop-up remplie, caractéristiques avec icônes, Échap ferme, focus revient sur la carte, Tab reste dans la pop-up ; produit en rupture (mettre `quantite = 0` à la main dans `pivert`) → bouton désactivé ; 360 px : pop-up plein écran.
- [ ] **Étape 5 : commit** — `"feat: pop-up produit générique avec caractéristiques"`

---

### Task 7: Accueil

**Fichiers :**
- Modifier : `public/index.php`, `src/catalogue.php`
- Créer : `templates/pages/accueil.twig`
- Test : `tests/CatalogueTest.php` (ajout)

**Interfaces :**
- Consomme : `produits_univers` (lignes), `caracteristiques_de`, `conseil_du_jour`, `filtres_univers`, partials carte/modale produit.
- Produit : `produits_mis_en_avant(int $n = 4): array` — `$n/2` produits végétaux puis `$n/2` animaliers, en stock, les plus récents (`date_ajout DESC`), alternés végétal/animalier, même forme de ligne que `produits_univers`.
- `accueil.twig` (écran 1 de la maquette) : grille bento — tuile vert foncé (« Fais pousser <span citron>ta jungle.</span> Adopte <span corail>ta bande.</span> » + sous-titre + 2 pilules Végétal/Animalier), tuile photo `images/site/monstera.jpg` (étiquette citron « Univers Végétal », pastille « {n} rayons » = `count(filtres_univers('vegetal'))`), grande tuile photo `images/site/chat.jpg` (étiquette corail « Univers Animalier », « {n} rayons »), tuile blanche « Le conseil du jour » (titre du conseil + lien « Tous les conseils » → `conseils.php`) ; rangée des 4 produits mis en avant (cartes avec étiquette d'univers, ouvrent la pop-up) ; section `#qui-sommes-nous` (texte existant de `rendu_jaqueline/acceuil/Acceuil.php` l. 60-71 passé au tutoiement + photos `photo-koko.png` « Korantin » et `photo-mathis.png` « Mathis ») ; section `#contact` (téléphone 00 67 00 67 00, adresse 5 rue de la nature, e-mail `contact@pivert.fr`, horaires Lun–Sam 9h–18h, carte Google Maps avec l'URL `src` existante de `Acceuil.php` l. 191, `loading="lazy"`, `title="Carte : IUT de Blois"`). Bento : 3 colonnes → 2 (≤ 760 px, tuile principale sur toute la largeur) → 1 (≤ 420 px).

- [ ] **Étape 1 : écrire le test qui échoue** :

```php
public function test_mis_en_avant_alterne_les_univers(): void {
    $p = produits_mis_en_avant(4);
    $this->assertSame(['vegetal', 'animalier', 'vegetal', 'animalier'], array_column($p, 'univers'));
    $this->assertGreaterThan(0, min(array_column($p, 'quantite')));
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter CatalogueTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter.**
- [ ] **Étape 4 : lancer** → OK. Vérif navigateur à 1280 / 760 / 360 px : comparer à l'écran 1 de la maquette ; liens des tuiles et pilules ; pop-up depuis l'accueil.
- [ ] **Étape 5 : commit** — `"feat: page d'accueil en grille bento"`

---

### Task 8: Panier

**Fichiers :**
- Créer : `src/panier.php`, `public/panier.php`, `public/panier-action.php`, `templates/pages/panier.twig`
- Test : `tests/PanierTest.php`

**Interfaces :**
- Consomme : `db_tous`, `exiger_csrf`, `flash`, `rediriger`, `url_retour_sure`, `entier_post`.
- Produit (panier = `array<int idProduit, int quantite>` dans `$_SESSION['panier']`) :
  - `panier_ajouter(array $panier, int $id, int $qte, int $stock): array` — ajoute `$qte` à l'existant ; total borné à `$stock` ; `$qte <= 0` ou `$stock <= 0` → panier inchangé.
  - `panier_modifier(array $panier, int $id, int $qte, int $stock): array` — fixe la quantité, bornée à `$stock` ; `$qte <= 0` → retire la ligne.
  - `panier_retirer(array $panier, int $id): array`.
  - `panier_nombre(array $panier): int` (somme des quantités ; `rendu_chaine` de la Tâche 3 doit l'utiliser).
  - `panier_detail(array $panier): array{lignes: list<array{id_produit, nom, photo_nom, prix, quantite, stock, sous_total, univers}>, total: float}` — une requête `WHERE id_produit IN (?, ?, …)` préparée ; les ids absents de la base sont ignorés ; si la quantité dépasse le stock actuel, elle est ramenée au stock (la ligne à stock 0 reste affichée avec la mention « Plus en stock » et un sous-total de 0).
  - `panier-action.php` : POST + CSRF ; `action ∈ ajouter|modifier|retirer` ; lit le stock en base ; produit inexistant → flash erreur « Ce produit n'existe plus. » ; flashs de succès : « Ajouté au panier », « Panier mis à jour », « Produit retiré » ; redirection vers `url_retour_sure($_POST['retour'], 'panier.php')`.
  - `panier.twig` : lignes en cartes (photo, nom, étiquette d'univers, prix unitaire, formulaire −/+ (POST `modifier`), sous-total, bouton retirer icône poubelle avec `aria-label`), récap sticky (total, « Valider ma commande » → `commande.php`, « Continuer mes achats »). Panier vide : illustration + 2 pilules vers les univers.

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
public function test_ajouter_borne_quantite(): void {
    $this->assertSame([5 => 3], panier_ajouter([5 => 2], 5, 4, 3));
    $this->assertSame([], panier_ajouter([], 5, -3, 10));
    $this->assertSame([], panier_ajouter([], 5, 1, 0));
}
public function test_modifier_a_zero_retire(): void {
    $this->assertSame([2 => 1], panier_modifier([5 => 2, 2 => 1], 5, 0, 10));
    $this->assertSame([5 => 10], panier_modifier([5 => 2], 5, 99999, 10));
}
public function test_nombre(): void {
    $this->assertSame(3, panier_nombre([3 => 2, 8 => 1]));
}
public function test_detail_ignore_produit_inexistant(): void {
    $d = panier_detail([1 => 2, 999999 => 1]);
    $this->assertCount(1, $d['lignes']);
    $prix1 = (float) db_un('SELECT prix FROM PRODUIT WHERE id_produit = 1')['prix'];
    $this->assertEqualsWithDelta(2 * $prix1, $d['total'], 0.001);
}
public function test_detail_ramene_au_stock(): void {
    db_executer('UPDATE PRODUIT SET quantite = 1 WHERE id_produit = 1');
    $this->assertSame(1, panier_detail([1 => 5])['lignes'][0]['quantite']);
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter PanierTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter.**
- [ ] **Étape 4 : lancer** `php vendor/bin/phpunit` → tout OK. Vérif navigateur : ajouter depuis la pop-up (toast « Ajouté au panier », badge mis à jour), −/+, retirer, panier vide ; envoyer un POST sans jeton avec `curl -s -o /dev/null -w "%{http_code}" -X POST -d "action=ajouter&id=1&qte=1" http://127.0.0.1:8080/panier-action.php` → `302` et panier inchangé.
- [ ] **Étape 5 : commit** — `"feat: panier en session borné au stock"`

---

### Task 9: Validation de commande

**Fichiers :**
- Créer : `src/commande.php`, `public/commande.php`, `public/commande-confirmee.php`, `templates/pages/{commande,commande-confirmee}.twig`
- Test : `tests/CommandeTest.php`

**Interfaces :**
- Consomme : `panier_detail`, `client_connecte`, `exiger_csrf`, `flash`, `rediriger`.
- Produit :
  - `class StockInsuffisant extends RuntimeException` (message : « Stock insuffisant pour « {nom} ». »).
  - `commande_creer(int $idClient, array $panier): int` — transaction (`db()->begin_transaction()`) : pour chaque ligne, `SELECT … FOR UPDATE`, `UPDATE PRODUIT SET quantite = quantite - ? WHERE id_produit = ? AND quantite >= ?` (0 ligne affectée → `StockInsuffisant`), insertion `COMMANDE` (`statut = 'validée'`, `date_commande = CURDATE()`, total calculé avec les prix **de la base**) puis `COMPORTER` (`prix_unitaire` de la base) ; `commit` ; toute exception → `rollback` puis relance. Panier vide → `InvalidArgumentException`.
  - `commande_par_id(int $idCommande): ?array` — `['commande' => …, 'lignes' => […], 'client' => ['prenom','nom','adresse','email']]`.
  - `commande.php` : non connecté → flash info « Connecte-toi pour valider ta commande. » + `rediriger('compte.php?retour=commande.php')` ; panier vide → `panier.php` ; GET → récap (lignes, total, adresse) ; POST + CSRF → `commande_creer`, vide `$_SESSION['panier']`, `rediriger('commande-confirmee.php?id=…')` ; `StockInsuffisant` → flash erreur avec le message + retour `panier.php`.
  - `commande-confirmee.php` : affiche « Merci, {prenom} ! » + numéro + récap ; uniquement si la commande appartient au client connecté (ou rôle admin/demo), sinon accueil.

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
public function test_commande_decremente_le_stock(): void {
    $avant = (int) db_un('SELECT quantite q FROM PRODUIT WHERE id_produit = 1')['q'];
    $id = commande_creer($this->idClientDemo(), [1 => 2]);
    $this->assertSame($avant - 2, (int) db_un('SELECT quantite q FROM PRODUIT WHERE id_produit = 1')['q']);
    $this->assertSame('validée', commande_par_id($id)['commande']['statut']);
}
public function test_stock_insuffisant_annule_tout(): void {
    db_executer('UPDATE PRODUIT SET quantite = 1 WHERE id_produit = 2');
    $avant1 = (int) db_un('SELECT quantite q FROM PRODUIT WHERE id_produit = 1')['q'];
    try { commande_creer($this->idClientDemo(), [1 => 1, 2 => 5]); $this->fail(); } catch (StockInsuffisant) {}
    $this->assertSame(0, (int) db_un('SELECT COUNT(*) n FROM COMMANDE')['n']);
    $this->assertSame($avant1, (int) db_un('SELECT quantite q FROM PRODUIT WHERE id_produit = 1')['q']);
}
public function test_prix_pris_en_base(): void {
    $id = commande_creer($this->idClientDemo(), [1 => 1]);
    $prix = (float) db_un('SELECT prix FROM PRODUIT WHERE id_produit = 1')['prix'];
    $this->assertEqualsWithDelta($prix, (float) commande_par_id($id)['commande']['total'], 0.001);
}
```

  (`idClientDemo()` : helper de `BaseTestCase` qui renvoie l'id de `client@pivert.demo`.)
- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter CommandeTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter.**
- [ ] **Étape 4 : lancer** `php vendor/bin/phpunit` → tout OK. Vérif navigateur : parcours complet non connecté → connexion → retour au récap → confirmer → page « Merci, Camille ! » ; double clic sur « Confirmer » → une seule commande (le second envoi trouve un panier vide et redirige).
- [ ] **Étape 5 : commit** — `"feat: validation de commande transactionnelle"`

---

### Task 10: Admin — gabarit, gardes, mode démo, tableau de bord

**Fichiers :**
- Modifier : `src/auth.php`, `src/catalogue.php`
- Créer : `templates/admin/{layout,tableau-de-bord}.twig`, `public/admin/index.php`, `public/assets/css/admin.css`, `public/assets/js/admin-tableau.js`
- Test : `tests/AdminTest.php`, `tests/VueTest.php` (ajout)

**Interfaces :**
- Consomme : `peut_voir_admin`, `peut_modifier_admin`, `client_connecte`, `rendu`, `normaliser` / `correspond` (import depuis `recherche.js`).
- Produit :
  - `exiger_admin(): array` — renvoie le client ; sinon flash « Accès réservé à l'administration. » + `rediriger('../compte.php?retour=admin/index.php')`.
  - `exiger_ecriture_admin(): void` — si `!peut_modifier_admin(client_connecte())` : flash erreur « Mode démo : les modifications sont désactivées » + `rediriger(url_retour_sure($_SERVER['HTTP_REFERER'] chemin relatif, 'index.php'))`. **À appeler en premier dans toute branche POST de `/admin`.**
  - `ecriture_admin_autorisee(?array $client): bool` — fonction pure utilisée par `exiger_ecriture_admin` (testable).
  - `stats_stock(): array{total: int, par_univers: array{vegetal: int, animalier: int}, stock_faible: int, rupture: int}` — une seule requête groupée ; stock faible = `1 ≤ quantite ≤ 5`, rupture = `quantite = 0`.
  - `produits_admin(): array` — tous les produits avec catégorie, univers, espèce, marque, triés par nom.
  - `layout.twig` (étend `base.twig`, `base_url = '../'`) : nav admin (Tableau de bord, Produits = tableau, Nouvelle commande, « Retour au site »), et si rôle `demo` un bandeau permanent `role="note"` « Mode démo — lecture seule ».
  - `tableau-de-bord.twig` : tuiles de chiffres (Total, Végétal sur fond citron, Animalier sur fond corail, Stock faible, Rupture), puis tableau (photo, nom, catégorie avec étiquette de couleur d'univers, animal, marque, prix, stock ; lignes stock faible/rupture marquées par une pastille + texte, pas seulement la couleur), champ de recherche et liste déroulante de catégorie (`admin-tableau.js`), boutons Modifier (lien) et Supprimer (formulaire POST, Tâche 11). Tableau défilant horizontalement dans son conteneur sous 760 px.

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
public function test_demo_ne_peut_pas_ecrire(): void {
    $this->assertFalse(ecriture_admin_autorisee(['role' => 'demo']));
    $this->assertFalse(ecriture_admin_autorisee(null));
    $this->assertTrue(ecriture_admin_autorisee(['role' => 'admin']));
}
public function test_stats_stock(): void {
    db_executer('UPDATE PRODUIT SET quantite = 0 WHERE id_produit = 1');
    db_executer('UPDATE PRODUIT SET quantite = 3 WHERE id_produit = 2');
    $s = stats_stock();
    $this->assertSame(51, $s['total']);
    $this->assertSame(['vegetal' => 26, 'animalier' => 25], $s['par_univers']);
    $this->assertGreaterThanOrEqual(1, $s['rupture']);
    $this->assertGreaterThanOrEqual(1, $s['stock_faible']);
}
// VueTest
public function test_bandeau_demo(): void {
    $_SESSION['client'] = ['prenom' => 'Recruteur', 'role' => 'demo'];
    $this->assertStringContainsString('Mode démo — lecture seule', rendu_chaine('admin/tableau-de-bord.twig', ['stats' => ['total' => 0, 'par_univers' => ['vegetal' => 0, 'animalier' => 0], 'stock_faible' => 0, 'rupture' => 0], 'produits' => [], 'base_url' => '../']));
}
```

  (Vérifier le total 26/25 sur les données réelles de la Tâche 2 ; si les chiffres diffèrent, corriger le test d'après `SELECT univers, COUNT(*)` — pas les données.)
- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter "AdminTest|VueTest"` → ÉCHEC.
- [ ] **Étape 3 : implémenter.**
- [ ] **Étape 4 : lancer** → OK. Vérif : `http://127.0.0.1:8080/admin/` non connecté → redirigé vers la connexion ; connecté `recruteur@pivert.demo` → tableau de bord + bandeau ; connecté `client@pivert.demo` → refusé.
- [ ] **Étape 5 : commit** — `"feat: admin (gabarit, gardes, mode démo, tableau de bord)"`

---

### Task 11: Admin — créer, modifier, supprimer un produit

**Fichiers :**
- Créer : `src/admin_produits.php`, `public/admin/produit.php`, `public/admin/produit-supprimer.php`, `templates/admin/produit-form.twig`, `public/assets/js/admin-produit.js`
- Test : `tests/AdminProduitsTest.php`

**Interfaces :**
- Consomme : `exiger_admin`, `exiger_ecriture_admin`, `exiger_csrf`, `caracteristiques_de`, `ESPECES`, catégories.
- Produit :
  - `valider_produit(array $post): array{donnees: array, erreurs: array<string,string>}` — `nom` (1–255), `description` (1–255), `prix` (décimal > 0, virgule acceptée), `quantite` (entier ≥ 0), `id_categorie` (existe), `id_marque` (existe ou vide → `null`), `espece` (clé de `ESPECES`, `''` → `null` ; forcé à `null` si la catégorie est végétale), `caracteristiques` (tableau de `{libelle, valeur, icone}` ; lignes entièrement vides ignorées ; 0 à 6 lignes ; icône dans la liste autorisée de la Tâche 2).
  - `valider_photo(array $fichier): array{ok: bool, erreur: ?string, extension: ?string}` — pas de fichier (`UPLOAD_ERR_NO_FILE`) → `ok` true, extension `null` ; type réel par `finfo` ∈ `image/jpeg` (jpg), `image/png` (png), `image/webp` (webp) ; taille ≤ 3 Mo ; messages « Format accepté : JPG, PNG ou WebP. » / « Image trop lourde (3 Mo maximum). ».
  - `produit_enregistrer(array $donnees, ?int $id, ?string $photoNom): int` — INSERT (`date_ajout = CURDATE()`) ou UPDATE ; ne change `photo_nom` que si `$photoNom !== null` ; appelle `caracteristiques_remplacer` ; le tout en transaction.
  - `caracteristiques_remplacer(int $idProduit, array $lignes): void` — DELETE puis INSERT avec `ordre` = position.
  - `produit_supprimer(int $id): bool` — `false` (rien supprimé) si le produit figure dans `COMPORTER`, sinon supprime (caractéristiques en cascade) et efface le fichier photo s'il commence par `upload-`. Message côté page : « Ce produit apparaît dans des commandes : passe son stock à 0 plutôt que de le supprimer. » (comportement à documenter dans le README).
  - `produit.php` : GET `?id=` (absent → création) ; POST : `exiger_ecriture_admin()` puis `exiger_csrf()`, validation, upload déplacé vers `public/images/produits/upload-{bin2hex(random_bytes(8))}.{ext}`, enregistrement, flash « Produit enregistré » → `index.php`. Erreurs : formulaire ré-affiché avec messages sous chaque champ et valeurs conservées.
  - `produit-supprimer.php` : POST seulement ; `exiger_ecriture_admin()`, `exiger_csrf()` ; confirmation côté client par `confirm("Supprimer « {nom} » ?")` dans `admin-tableau.js`.
  - `produit-form.twig` : 2 colonnes (≥ 760 px) — champs à gauche, aperçu photo + caractéristiques à droite ; liste de lignes de caractéristiques (libellé, valeur, `<select>` d'icône) avec boutons « Ajouter une caractéristique » / retirer (`admin-produit.js`, clone d'un `<template>`) ; champ « Animal » (options `ESPECES` + « Tous animaux » = valeur vide) visible seulement si la catégorie choisie est animalière (`data-univers` sur chaque `<option>` de catégorie).

- [ ] **Étape 1 : écrire les tests qui échouent** :

```php
public function test_validation_produit(): void {
    $r = valider_produit(['nom' => '', 'description' => 'x', 'prix' => '-2', 'quantite' => '1.5', 'id_categorie' => '99', 'id_marque' => '', 'espece' => '', 'caracteristiques' => []]);
    $this->assertSame(['nom', 'prix', 'quantite', 'id_categorie'], array_keys($r['erreurs']));
}
public function test_espece_forcee_a_null_en_vegetal(): void {
    $r = valider_produit(['nom' => 'Rose', 'description' => 'x', 'prix' => '4,50', 'quantite' => '3', 'id_categorie' => '2', 'id_marque' => '', 'espece' => 'chats', 'caracteristiques' => []]);
    $this->assertSame([], $r['erreurs']);
    $this->assertNull($r['donnees']['espece']);
    $this->assertSame(4.5, $r['donnees']['prix']);
}
public function test_creation_avec_caracteristiques_puis_modification(): void {
    $d = valider_produit(['nom' => 'Hamster', 'description' => 'Petit', 'prix' => '12', 'quantite' => '4', 'id_categorie' => '6', 'id_marque' => '', 'espece' => 'rongeurs', 'caracteristiques' => [['libelle' => 'Vie', 'valeur' => '2 ans', 'icone' => 'sablier'], ['libelle' => '', 'valeur' => '', 'icone' => '']]])['donnees'];
    $id = produit_enregistrer($d, null, null);
    $this->assertCount(1, caracteristiques_de([$id])[$id]);
    produit_enregistrer(['caracteristiques' => []] + $d, $id, null);
    $this->assertArrayNotHasKey($id, caracteristiques_de([$id]));
}
public function test_suppression_refusee_si_commande(): void {
    commande_creer($this->idClientDemo(), [1 => 1]);
    $this->assertFalse(produit_supprimer(1));
    $this->assertTrue(produit_supprimer(2));
    $this->assertNull(db_un('SELECT id_produit FROM PRODUIT WHERE id_produit = 2'));
}
public function test_photo_refuse_un_faux_jpg(): void {
    $tmp = tempnam(sys_get_temp_dir(), 'pv');
    file_put_contents($tmp, '<?php echo 1;');
    $r = valider_photo(['name' => 'x.jpg', 'tmp_name' => $tmp, 'size' => 13, 'error' => UPLOAD_ERR_OK]);
    $this->assertFalse($r['ok']);
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter AdminProduitsTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter.** Créer un compte admin local : `php outils/creer-admin.php admin@pivert.local <mot-de-passe-local> Mathis Beaussier`.
- [ ] **Étape 4 : lancer** `php vendor/bin/phpunit` → tout OK. Vérif : en `admin@pivert.local`, créer un produit avec photo et 2 caractéristiques → visible dans l'univers et sa pop-up ; modifier ; supprimer. En `recruteur@pivert.demo` : soumettre le formulaire → toast « Mode démo : les modifications sont désactivées » et rien ne change ; avec les cookies de session du compte démo, `curl -b cookies.txt -X POST -d "id=2&csrf=<jeton>" http://127.0.0.1:8080/admin/produit-supprimer.php` → produit 2 toujours présent.
- [ ] **Étape 5 : commit** — `"feat: admin, gestion des produits (photo, caractéristiques, suppression sûre)"`

---

### Task 12: Admin — nouvelle commande pour un client

**Fichiers :**
- Créer : `public/admin/commande.php`, `templates/admin/{commande-form,commande-recap}.twig`
- Test : `tests/AdminTest.php` (ajout)

**Interfaces :**
- Consomme : `commande_creer`, `commande_par_id`, `StockInsuffisant`, `exiger_admin`, `exiger_ecriture_admin`, `exiger_csrf`.
- Produit :
  - `clients_pour_commande(): array` (dans `src/auth.php`) — `id_client, prenom, nom, email` des comptes `role = 'client'`, triés par nom.
  - `panier_depuis_formulaire(array $quantites): array` (dans `src/panier.php`) — garde les entrées dont la clé et la valeur sont des entiers > 0 ; renvoie `[id => qte]`.
  - `admin/commande.php` : GET sans `id` → formulaire (sélecteur client, liste des produits en stock groupés par catégorie avec un champ quantité `min=0 max=stock` chacun) ; POST → `exiger_ecriture_admin()`, `exiger_csrf()`, `commande_creer(idClient, panier_depuis_formulaire($_POST['quantites'] ?? []))` → `?id=` ; GET `?id=` → `commande-recap.twig` (client, lignes, total, statut). `StockInsuffisant` / panier vide → formulaire ré-affiché avec le message.

- [ ] **Étape 1 : écrire le test qui échoue** :

```php
public function test_panier_depuis_formulaire(): void {
    $this->assertSame([3 => 2, 9 => 1], panier_depuis_formulaire(['3' => '2', '5' => '0', '7' => '-1', '9' => '1', 'x' => '4', '11' => 'abc']));
}
public function test_clients_pour_commande_exclut_demo(): void {
    $this->assertSame(['client@pivert.demo'], array_column(clients_pour_commande(), 'email'));
}
```

- [ ] **Étape 2 : lancer** `php vendor/bin/phpunit --filter AdminTest` → ÉCHEC.
- [ ] **Étape 3 : implémenter.**
- [ ] **Étape 4 : lancer** `php vendor/bin/phpunit` → tout OK. Vérif : en admin, commande de 2 produits pour Camille → récap, stock décrémenté dans le tableau de bord ; en démo → refusé.
- [ ] **Étape 5 : commit** — `"feat: admin, commande pour un client"`

---

### Task 13: Finitions, README, vérification complète

**Fichiers :**
- Créer : `README.md`, `docs/captures/` (avant/après)
- Modifier : selon les défauts trouvés

- [ ] **Étape 1 : vérifier la syntaxe** — `for f in $(git ls-files '*.php'); do php -l "$f" || exit 1; done` → aucune erreur.
- [ ] **Étape 2 : lancer toutes les suites** — `php vendor/bin/phpunit` et `node --test tests/js/` → tout OK (sortie à coller dans le compte rendu).
- [ ] **Étape 3 : parcours manuel complet**, à 1280 px et 360 px, consigné sous forme de liste cochée dans le README : accueil → univers végétal (filtre, recherche) → pop-up → ajout → univers animalier (filtre Chats) → panier (−/+, retrait) → validation non connecté → inscription → confirmation → déconnexion ; admin : démo (lecture seule, écritures refusées) puis admin (créer/modifier/supprimer, commande client). Navigation au clavier seul sur la pop-up et le menu mobile. Aucune erreur dans la console du navigateur.
- [ ] **Étape 4 : captures** — « après » : accueil, univers animalier, pop-up, panier, tableau de bord admin (bureau + mobile). « avant » : mêmes pages de l'ancien site (`rendu_jaqueline` servi avec la base `sae_203` si elle existe encore dans MySQL ; sinon, noter dans le README que les captures « avant » sont à ajouter par Mathis).
- [ ] **Étape 5 : écrire `README.md`** — présentation (1 paragraphe), captures avant/après, stack, fonctionnalités, installation locale (XAMPP, `composer install`, copier `config.exemple.php` → `config.php`, `php outils/installer-base.php`, `php outils/creer-admin.php …`, `php -S 127.0.0.1:8080 -t public`), comptes de démo (`client@pivert.demo` / `demo1234`, `recruteur@pivert.demo` / `recruteur2027`), choix techniques (Twig, `data-univers`, pop-up générique, transactions, mode démo), comportement de la suppression d'un produit commandé, tests (commandes), crédits (ligne exacte des Contraintes globales) et licence des icônes Lucide (ISC).
- [ ] **Étape 6 : commit** — `"docs: README, captures et parcours de vérification"`
