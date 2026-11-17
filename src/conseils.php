<?php

declare(strict_types=1);

/**
 * Conseils de chaque univers (contenu statique repris du site d'origine, passé au tutoiement).
 * @return list<array{icone: string, titre: string, texte: string}>
 */
function conseils(string $univers): array
{
    $conseils = [
        'vegetal' => [
            ['icone' => 'goutte', 'titre' => 'Arroser sans gaspiller', 'texte' => 'Arrose le matin de préférence : l\'eau s\'évapore moins et va directement aux racines.'],
            ['icone' => 'feuille', 'titre' => 'Prolonger la floraison', 'texte' => 'Retire régulièrement les fleurs fanées pour encourager de nouvelles floraisons tout au long de la saison.'],
            ['icone' => 'outil', 'titre' => 'Le bon substrat', 'texte' => 'Choisis un substrat adapté à ta plante pour lui garantir une bonne croissance et une santé solide.'],
            ['icone' => 'sablier', 'titre' => 'Planter un arbre', 'texte' => 'Choisis la bonne saison : l\'automne est idéal pour planter les arbres à racines nues.'],
            ['icone' => 'soleil', 'titre' => 'Entretenir ses plantes d\'intérieur', 'texte' => 'Dépoussière régulièrement les feuilles : elles captent mieux la lumière et restent éclatantes.'],
            ['icone' => 'feuille', 'titre' => 'Choisir ses fleurs selon la saison', 'texte' => 'Plante des tulipes en automne, des géraniums au printemps et des dahlias en été pour un jardin toujours fleuri.'],
            ['icone' => 'outil', 'titre' => 'Entretenir ses outils', 'texte' => 'Nettoie et huile tes outils après chaque utilisation pour prolonger leur durée de vie.'],
            ['icone' => 'feuille', 'titre' => 'Le compost maison', 'texte' => 'Recycle tes déchets organiques en compost pour nourrir naturellement tes plantes et ton potager.'],
            ['icone' => 'maison', 'titre' => 'Décorer avec des pierres naturelles', 'texte' => 'Délimite tes massifs avec des galets ou des bordures en pierre pour un jardin soigné et naturel.'],
        ],
        'animalier' => [
            ['icone' => 'maison', 'titre' => 'Bien choisir sa litière', 'texte' => 'Minérale, végétale, silicone… tout ce qu\'il faut savoir pour choisir la litière adaptée à ton chat.'],
            ['icone' => 'assiette', 'titre' => 'L\'alimentation du chien', 'texte' => 'Croquettes, pâtée ou BARF ? On t\'aide à nourrir ton chien sainement selon son âge et sa taille.'],
            ['icone' => 'thermometre', 'titre' => 'Vermifuges et puces', 'texte' => 'Fréquence, produits, pipettes… comment protéger efficacement ton animal toute l\'année.'],
            ['icone' => 'goutte', 'titre' => 'Aménager l\'aquarium', 'texte' => 'Substrat, plantes, filtration : les bases pour créer un environnement sain pour tes poissons.'],
            ['icone' => 'patte', 'titre' => 'Comprendre son chat', 'texte' => 'Miaulements, griffades, ronronnements… décrypte les signaux de ton chat pour mieux répondre à ses besoins.'],
            ['icone' => 'feuille', 'titre' => 'Toilettage à domicile', 'texte' => 'Un brossage régulier limite les nœuds et les poils dans la maison. Commence par des séances courtes pour habituer ton animal.'],
            ['icone' => 'maison', 'titre' => 'Voyager avec son animal', 'texte' => 'Caisse de transport, carnet de santé, eau fraîche et pauses régulières : prépare le trajet à l\'avance pour qu\'il reste serein.'],
            ['icone' => 'sablier', 'titre' => 'Les besoins en exercice du chien', 'texte' => 'Selon la race, l\'âge et le caractère, les besoins en activité varient énormément. Adapte la durée des balades à ton chien.'],
            ['icone' => 'patte', 'titre' => 'Adopter un lapin nain', 'texte' => 'Habitat, alimentation, socialisation : tout ce qu\'il faut savoir avant d\'accueillir un lapin chez toi.'],
        ],
    ];

    return $conseils[$univers] ?? [];
}

/** Un conseil différent chaque jour, identique toute la journée. */
function conseil_du_jour(DateTimeInterface $date): array
{
    $tous = array_merge(conseils('vegetal'), conseils('animalier'));

    return $tous[(int) $date->format('z') % count($tous)];
}
