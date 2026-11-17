// Menu de navigation mobile : ouvre / ferme la liste des liens.
const bouton = document.querySelector('.nav-menu-bouton');
const liens = document.getElementById('nav-liens');

if (bouton && liens) {
  const fermer = () => {
    bouton.setAttribute('aria-expanded', 'false');
    liens.classList.remove('est-ouvert');
  };

  bouton.addEventListener('click', () => {
    const ouvert = bouton.getAttribute('aria-expanded') === 'true';
    bouton.setAttribute('aria-expanded', String(!ouvert));
    liens.classList.toggle('est-ouvert', !ouvert);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && liens.classList.contains('est-ouvert')) {
      fermer();
      bouton.focus();
    }
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav')) fermer();
  });
}
