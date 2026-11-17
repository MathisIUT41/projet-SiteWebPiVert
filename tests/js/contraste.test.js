import { test } from 'node:test';
import assert from 'node:assert/strict';

// Ratio de contraste WCAG 2.x entre deux couleurs hexadécimales.
function luminance(hex) {
  const [r, g, b] = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255)
    .map((c) => (c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4));
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
function ratio(a, b) {
  const [l1, l2] = [luminance(a), luminance(b)].sort((x, y) => y - x);
  return (l1 + 0.05) / (l2 + 0.05);
}

// Valeurs copiées de public/assets/css/tokens.css
const vert = '#12351F', citron = '#C6F05A', corail = '#FF8A66', fond = '#EEF2E6', blanc = '#FFFFFF';

test('vert sur citron ≥ 7', () => assert.ok(ratio(vert, citron) >= 7));
test('vert sur corail ≥ 4.5', () => assert.ok(ratio(vert, corail) >= 4.5));
test('vert sur fond ≥ 7', () => assert.ok(ratio(vert, fond) >= 7));
test('citron sur blanc < 3 (interdit en texte)', () => assert.ok(ratio(citron, blanc) < 3));
