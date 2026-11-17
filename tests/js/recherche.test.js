import { test } from 'node:test';
import assert from 'node:assert/strict';
import { normaliser, correspond } from '../../public/assets/js/recherche.js';

test('accents et casse', () => assert.ok(correspond('Érable du Japon Arbres', 'erable')));
test('apostrophe', () => assert.ok(correspond("Cochon d'Inde Animaux Rongeurs", 'cochon d inde')));
test('apostrophe dans le terme', () => assert.ok(correspond('Cochon d Inde', "cochon d'inde")));
test('tous les mots requis', () => assert.equal(correspond('Monstera Plantes', 'monstera chat'), false));
test('terme vide', () => assert.ok(correspond('Ficus', '   ')));
test('normaliser', () => assert.equal(normaliser("  Pépinière—d'Été "), 'pepiniere d ete'));
