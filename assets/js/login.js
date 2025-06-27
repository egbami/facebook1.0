document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const response = await fetch('', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            // Stocker les infos de session dans sessionStorage
            sessionStorage.setItem('user_id', result.user_id);
            sessionStorage.setItem('username', result.username);
            sessionStorage.setItem('nom', result.nom);
            sessionStorage.setItem('prenom', result.prenom);
            sessionStorage.setItem('message', result.message);
            // Rediriger vers l'accueil
            window.location.href = '../../index.html';
        } else {
            // Afficher l'erreur
            alert('Nom d\'utilisateur, email ou mot de passe incorrect');
        }
    });
}); 