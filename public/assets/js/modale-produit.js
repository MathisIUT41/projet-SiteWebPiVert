// Pop-up produit générique : une seule <dialog> par page, remplie avec les données
// (JSON) de la carte cliquée. Les données sont toujours insérées avec textContent.

/** 1299.5 → « 1 299,50 € » (espaces fines insécables). */
export function formaterPrix(valeur) {
  const [entier, decimales] = Number(valeur).toFixed(2).split('.');
  return `${entier.replace(/\B(?=(\d{3})+(?!\d))/g, ' ')},${decimales} €`;
}

/** Met en forme un produit pour la pop-up (fonction pure, testée). */
export function vueProduit(p) {
  const quantite = Number(p.quantite) || 0;
  return {
    id: p.id_produit,
    nom: p.nom,
    prix: formaterPrix(p.prix),
    etiquettes: [p.libelle_categorie, p.libelle_espece].filter(Boolean),
    description: p.description ?? '',
    image: `images/produits/${p.photo_nom ? p.photo_nom : 'defaut.svg'}`,
    rupture: quantite <= 0,
    qteMax: Math.max(quantite, 0),
    caracteristiques: Array.isArray(p.caracteristiques) ? p.caracteristiques : [],
    univers: p.univers ?? null,
  };
}

if (typeof document !== 'undefined') {
  const modale = document.getElementById('modale-produit');

  if (modale) {
    const $ = (id) => document.getElementById(id);
    const image = $('modale-produit-image');
    const champQte = $('modale-produit-qte');
    const groupeQte = $('modale-produit-quantite');
    const cta = $('modale-produit-cta');
    const liste = $('modale-produit-caracteristiques');
    const modele = $('modele-caracteristique');
    const prefixe = image.getAttribute('src').replace(/images\/produits\/defaut\.svg$/, '');
    let declencheur = null;

    const borner = () => {
      const max = Number(champQte.max) || 1;
      const v = Math.round(Number(champQte.value)) || 1;
      champQte.value = String(Math.min(Math.max(v, 1), max));
    };

    const remplir = (v) => {
      image.src = prefixe + v.image;
      image.alt = v.nom;
      $('modale-produit-nom').textContent = v.nom;
      $('modale-produit-prix').textContent = v.prix;
      $('modale-produit-description').textContent = v.description;
      $('modale-produit-id').value = v.id;

      $('modale-produit-etiquettes').replaceChildren(...v.etiquettes.map((texte) => {
        const span = document.createElement('span');
        span.className = 'etiquette etiquette--neutre';
        span.textContent = texte;
        return span;
      }));

      liste.replaceChildren(...v.caracteristiques.map((c) => {
        const li = modele.content.firstElementChild.cloneNode(true);
        const icone = modale.querySelector(`template[data-icone="${CSS.escape(c.icone)}"]`);
        if (icone) li.querySelector('.caracteristique-icone').append(icone.content.cloneNode(true));
        li.querySelector('.caracteristique-libelle').textContent = c.libelle;
        li.querySelector('.caracteristique-valeur').textContent = c.valeur;
        return li;
      }));
      liste.hidden = v.caracteristiques.length === 0;

      champQte.max = String(Math.max(v.qteMax, 1));
      champQte.value = '1';
      groupeQte.hidden = v.rupture;
      cta.disabled = v.rupture;
      cta.textContent = v.rupture ? 'Rupture de stock' : 'Ajouter au panier';

      modale.classList.toggle('theme-vegetal', v.univers === 'vegetal');
      modale.classList.toggle('theme-animalier', v.univers === 'animalier');
    };

    document.addEventListener('click', (e) => {
      const bouton = e.target.closest('[data-ouvrir-modale]');
      const carte = bouton?.closest('[data-produit]');
      if (!carte) return;
      let produit;
      try {
        produit = JSON.parse(carte.dataset.produit);
      } catch {
        return;
      }
      declencheur = carte.querySelector('.carte-produit-nom button') ?? bouton;
      remplir(vueProduit(produit));
      modale.showModal();
    });

    modale.addEventListener('click', (e) => {
      if (e.target === modale || e.target.closest('[data-fermer-modale]')) modale.close();
      const pas = e.target.closest('[data-quantite]');
      if (pas) {
        champQte.value = String(Number(champQte.value) + Number(pas.dataset.quantite));
        borner();
      }
    });

    champQte.addEventListener('change', borner);
    modale.addEventListener('close', () => declencheur?.focus());
  }
}
