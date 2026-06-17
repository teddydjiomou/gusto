document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem('token');

    if (!token) {
        console.error("Token manquant !");
        return;
    }

    // ======================
    // CHARTS
    // ======================
    fetch('/api-commande/routes/statistique.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(res => res.json())
    .then(result => {

        console.log("API RESULT :", result);

        if (!result?.success || !Array.isArray(result.data)) {
            console.error("Données invalides :", result);
            return;
        }

        const data = result.data;

        const moisNoms = [
            "Jan","Fév","Mar","Avr","Mai","Juin",
            "Juil","Août","Sep","Oct","Nov","Déc"
        ];

        const labels = data.map(i => moisNoms[i.mois - 1]);
        const values = data.map(i => Number(i.total));

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total',
                    data: values,
                    backgroundColor: '#ff7a00',
                    borderRadius: 10
                }]
            }
        });

        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#ff7a00','#ff8924','#ff9535','#ffa245',
                        '#ffaf57','#ffbc69','#ffd08a','#ffdca6',
                        '#ffe6c0','#fff0d6','#fff5e5','#fff9f0'
                    ]
                }]
            }
        });

    });

    // ======================
    // TABLES
    // ======================
    fetch('/api-commande/routes/table.php', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(res => res.json())
    .then(result => {

        console.log("TABLE API:", result);

        if (!result?.success || !Array.isArray(result.data)) {
            console.error("Erreur API table :", result);
            return;
        }

        tables.clear();

        result.data.forEach(table => {

            const isOpen = table.statu === "Ouvert";

            const badgeClass = isOpen ? "success" : "danger";
            const badgeText = isOpen ? "Ouvert" : "Fermé";

            tables.row.add([
                table.nom,
                `<span class="badge ${badgeClass}">
                    ${badgeText}
                </span>`,
                `
                <button class="icon-btn"><i class="fa-solid fa-pen"></i></button>
                <button class="icon-btn danger"><i class="fa-solid fa-trash"></i></button>
                <button class="icon-btn qr"><i class="fa-solid fa-qrcode"></i></button>
                <button class="icon-btn view"><i class="fa-solid fa-eye"></i></button>
                `
            ]);

        });

        tables.draw();

    })
    .catch(err => console.error("ERROR TABLE:", err));
});


document.addEventListener("DOMContentLoaded", () => {

    const menuItems = document.querySelectorAll(".menu li");
    const sections = document.querySelectorAll(".content-section");

    function showSection(id) {
        sections.forEach(sec => {
            sec.style.display = (sec.id === id) ? "block" : "none";
        });
    }

    menuItems.forEach(item => {
        item.addEventListener("click", () => {

            menuItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");

            const target = item.getAttribute("data-target");
            showSection(target);

        });
    });

});


let tables = $('.info-table').DataTable({
    pageLength: 5,
    language:{
        paginate:{
            previous:"<i class='fas fa-angle-left'></i>",
            next:"<i class='fas fa-angle-right'></i>"
        }
    }
});

let t = $('.info-user').DataTable({
    pageLength: 5,
    language:{
        paginate:{
            previous:"<i class='fas fa-angle-left'></i>",
            next:"<i class='fas fa-angle-right'></i>"
        }
    }
});


function parseJwt(token) {
    try {
        // Extraire la partie "payload" du token (la deuxième section après les points)
        const base64Url = token.split('.')[1];

        // Remplacer les caractères spécifiques URL-safe par des caractères base64 standard
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');

        // Décoder la chaîne base64 en JSON
        const jsonPayload = decodeURIComponent(
            atob(base64) // atob décode la chaîne base64 en texte
            .split('')    // transformer chaque caractère en tableau
            .map(function(c) { 
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2); 
            })
            .join('')     // reconstituer la chaîne encodée pour decodeURIComponent
        );

        // Convertir la chaîne JSON en objet JavaScript
        return JSON.parse(jsonPayload);
    } catch(e) {
        // En cas d'erreur (token malformé ou absent), retourner null
        return null;
    }
}

// Récupérer le token stocké dans localStorage
const token = localStorage.getItem('token');

const headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
};

if (token) {
  const payload = parseJwt(token);

  if (payload && payload.data && payload.data.login) {
      document.getElementById('userLogin').textContent = payload.data.login;
  } else {
      document.getElementById('userLogin').textContent = 'Invité';
  }
} else {
  window.location.href = './user.php';
}

document.getElementById('logoutBtn').addEventListener('click', function(e) {
  e.preventDefault();
  // Supprimer le token du localStorage
  localStorage.removeItem('token');
  // Rediriger vers la page de login
  window.location.href = './user.php';
});