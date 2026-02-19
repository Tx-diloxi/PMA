// --- DATA : PROGRAMME PPL ---
const defaultWorkoutData = {
    push: [
        { name: "Développé Couché", sets: 4, reps: "8-12", icon: "🏋" },
        { name: "Développé Militaire", sets: 3, reps: "8-12", icon: "💪" },
        { name: "Dips / Pompes", sets: 3, reps: "Max", icon: "🔥" },
        { name: "Élévations Latérales", sets: 3, reps: "12-15", icon: "👐" },
        { name: "Extension Triceps", sets: 3, reps: "10-12", icon: "🦾" }
    ],
    pull: [
        { name: "Tractions", sets: 4, reps: "Max", icon: "🧗" },
        { name: "Rowing Haltère", sets: 3, reps: "10-12", icon: "🚣" },
        { name: "Curl Biceps", sets: 3, reps: "10-12", icon: "💪" },
        { name: "Oiseau (Arr. Épaule)", sets: 3, reps: "12-15", icon: "🦅" },
        { name: "Tirage Poitrine", sets: 3, reps: "10-12", icon: "🛡️" }
    ],
    legs: [
        { name: "Squat / Presse Cuisse", sets: 4, reps: "8-12", icon: "🦵" },
        { name: "SDT Jambes Tendues", sets: 3, reps: "10-12", icon: "🦍" },
        { name: "Leg Extension", sets: 3, reps: "12-15", icon: "🍗" },
        { name: "Leg Curl", sets: 3, reps: "12-15", icon: "🦵" },
        { name: "Gainage", sets: 3, reps: "45s-1min", icon: "🧘" }
    ]
};

let workoutData = JSON.parse(localStorage.getItem('mls_workoutData')) || defaultWorkoutData;

// --- STATE VARIABLES ---
let currentStack = []; // Cartes d'exercices en cours
let currentWorkoutName = "";
let calories = parseInt(localStorage.getItem('mls_calories')) || 0;
let protein = parseInt(localStorage.getItem('mls_protein')) || 0;
let weight = parseFloat(localStorage.getItem('mls_weight')) || 76;
let history = JSON.parse(localStorage.getItem('mls_history')) || [];

// Charts instances
let consistencyChartInstance = null;
let distributionChartInstance = null;

// --- INIT ---
document.addEventListener("DOMContentLoaded", () => {
    updateNutritionUI();
    updateStatsUI();
    document.getElementById('weight-setting').value = weight;
    document.getElementById('current-weight-display').innerText = weight + " kg";
    loadMealSuggestions();
});

// --- MEAL SUGGESTIONS ---
let currentRecipeIndex = null;

const mealSuggestions = [
    { 
        title: "Omelette Fromage", 
        desc: "3 Œufs + Fromage", 
        protein: "25g Prot",
        ingredients: ["3 gros œufs", "30g Fromage râpé (Emmental/Cheddar)", "Une poignée d'épinards frais", "Sel, Poivre", "1 càc d'huile d'olive"],
        instructions: ["Battre les œufs dans un bol avec sel et poivre.", "Chauffer la poêle avec l'huile.", "Verser les œufs, laisser cuire 2 min.", "Ajouter le fromage et les épinards au centre.", "Plier l'omelette et servir chaud."]
    },
    { 
        title: "Bol Skyr & Fruits", 
        desc: "Skyr + Miel + Amandes", 
        protein: "28g Prot",
        ingredients: ["250g Skyr nature (0%)", "10 Amandes entières", "1 càc de Miel", "Fruits rouges (frais ou surgelés)"],
        instructions: ["Verser le Skyr dans un bol.", "Ajouter les fruits par dessus.", "Parsemer d'amandes concassées.", "Ajouter le filet de miel."]
    },
    { 
        title: "Poulet Curry Coco", 
        desc: "Poulet + Riz + Coco", 
        protein: "35g Prot",
        ingredients: ["150g Blanc de poulet", "60g Riz basmati (cru)", "50ml Lait de coco light", "1 càc Pâte de curry", "Légumes au choix (poivrons, courgettes)"],
        instructions: ["Cuire le riz.", "Couper le poulet en dés et faire revenir à la poêle.", "Ajouter les légumes et laisser cuire 5 min.", "Ajouter la pâte de curry et le lait de coco.", "Laisser mijoter 5-10 min et servir avec le riz."]
    },
    { 
        title: "Thon & Avocat", 
        desc: "Thon + Avocat + Pain", 
        protein: "30g Prot",
        ingredients: ["1 petite boîte de thon au naturel (100g)", "1/2 Avocat mûr", "2 tranches Pain complet", "Jus de citron", "Ciboulette"],
        instructions: ["Écraser l'avocat dans un bol.", "Mélanger avec le thon émietté.", "Ajouter jus de citron et ciboulette.", "Tartiner sur le pain complet grillé."]
    },
    { 
        title: "Pâtes Bolo Express", 
        desc: "Bœuf 5% + Pâtes complètes", 
        protein: "35g Prot",
        ingredients: ["125g Steak haché 5%", "80g Pâtes complètes (cru)", "Sauce tomate basilic", "Oignons"],
        instructions: ["Cuire les pâtes al dente.", "Faire revenir l'oignon et la viande hachée.", "Ajouter la sauce tomate.", "Mélanger avec les pâtes égouttées."]
    }
];

function loadMealSuggestions() {
    const container = document.getElementById('meal-suggestions');
    if(!container) return;
    
    container.innerHTML = mealSuggestions.map((meal, index) => `
        <div class="suggestion-card" onclick="openRecipeModal(${index})">
            <h4>${meal.title}</h4>
            <p>${meal.desc}</p>
            <span>${meal.protein}</span>
        </div>
    `).join('');
}

function openRecipeModal(index) {
    currentRecipeIndex = index;
    const meal = mealSuggestions[index];
    document.getElementById('modal-title').innerText = meal.title;
    document.getElementById('modal-meta').innerText = `⏳ Préparation rapide • ${meal.protein}`;
    
    const ingList = document.getElementById('modal-ingredients');
    ingList.innerHTML = meal.ingredients.map(i => `<li>${i}</li>`).join('');
    
    const instList = document.getElementById('modal-instructions');
    instList.innerHTML = meal.instructions.map(i => `<li>${i}</li>`).join('');
    
    document.getElementById('recipe-modal').classList.remove('hidden-section');
}

function closeRecipeModal() {
    document.getElementById('recipe-modal').classList.add('hidden-section');
}

function addCurrentRecipeToLog() {
    if(currentRecipeIndex !== null) {
        addLog(mealSuggestions[currentRecipeIndex].title);
        closeRecipeModal(); // On ferme
        // Feedback visuel ou scroll vers le log serait bien, mais simple pour l'instant
        alert("Ajouté au journal !");
    }
}


// --- NAVIGATION ---
function switchTab(tabId) {
    // Masquer toutes les sections
    document.querySelectorAll('section').forEach(sec => {
        sec.classList.remove('active-section');
        sec.classList.add('hidden-section');
    });
    // Afficher la section demandée
    const target = document.getElementById(tabId + '-section');
    target.classList.remove('hidden-section');
    target.classList.add('active-section');

    // Update Nav Icons
    document.querySelectorAll('.nav-item').forEach(btn => btn.classList.remove('active'));
    // Trouver le bouton cliqué (hack simple, idéalement via event listener direct)
    const navButtons = document.querySelectorAll('.nav-item');
    if(tabId === 'workout') navButtons[0].classList.add('active');
    if(tabId === 'nutrition') navButtons[1].classList.add('active');
    if(tabId === 'stats') navButtons[2].classList.add('active');
    if(tabId === 'profile') navButtons[3].classList.add('active');

    // Update Header
    const titles = {
        'workout': 'Entraînement',
        'nutrition': 'Nutrition (Recomposition)',
        'stats': 'Statistiques',
        'profile': 'Mon Profil'
    };
    document.getElementById('page-title').innerText = titles[tabId];
}

// --- WORKOUT LOGIC (TINDER STYLE) ---
function loadWorkout(type) {
    currentWorkoutName = type.toUpperCase();
    const exercises = workoutData[type];
    
    // Reset state
    currentStack = [];
    // Deep copy pour éviter de modifier la source si on voulait manipuler les objets
    currentStack = JSON.parse(JSON.stringify(exercises));
    
    renderStack();
    document.getElementById('swipe-controls').style.display = 'flex';
    
    // Highlight button
    document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

function renderStack() {
    const container = document.getElementById('card-stack');
    container.innerHTML = "";

    if (!currentStack || currentStack.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-check-circle" style="color:var(--success)"></i>
                <p>Séance terminée !</p>
                <button onclick="saveWorkoutSession()" class="reset-btn" style="background:var(--success)">Enregistrer la séance</button>
            </div>`;
        document.getElementById('swipe-controls').style.display = 'none';
        return;
    }

    // Afficher les cartes
    // On itère pour créer les éléments DOM. 
    // L'ordre visuel (z-index) doit être inversé : le premier élément du tableau est celui qu'on voit (top stack),
    // donc il doit avoir le plus haut z-index.
    
    currentStack.forEach((ex, index) => {
        const card = document.createElement('div');
        card.className = 'tinder-card';
        
        // Z-Index: Le premier élément (index 0) doit être au dessus (ex: z-index 100)
        // Le deuxième (index 1) en dessous (z-index 99), etc.
        card.style.zIndex = 100 - index;
        
        // Effet d'empilement visuel
        // Scale diminue pour les cartes derrière
        // TranslateY déplace légèrement vers le bas
        card.style.transform = `scale(${1 - index * 0.05}) translateY(${index * 10}px)`;
        
        // Masquer les cartes trop loin derrière pour la perf et le look
        card.style.opacity = index > 2 ? 0 : 1; 

        let videoBg = "";
        let contentClass = "";
        
        if(ex.videoUrl) {
            videoBg = `<video src="${ex.videoUrl}" autoplay loop muted playsinline class="card-bg-video"></video><div class="video-overlay"></div>`;
            contentClass = "video-active";
        }

        card.innerHTML = `
            ${videoBg}
            <div class="card-content-wrapper ${contentClass}">
                <div class="tinder-icon">${ex.icon}</div>
                <h2>${ex.name}</h2>
                <h3>${ex.sets} Séries x ${ex.reps}</h3>
                <div class="details">
                    <p>Note ta meilleure charge :</p>
                    <input type="number" class="input-weight" placeholder="kg" id="weight-${index}" onclick="event.stopPropagation()" ontouchstart="event.stopPropagation()">
                </div>
                <p class="swipe-instruction">Glisser pour valider -></p>
            </div>
        `;
        
        container.appendChild(card);
    });

    // Initialiser les gestes tactiles sur la carte du dessus
    initSwipeGestures();
}

// --- GESTURE LOGIC ---
function initSwipeGestures() {
    const container = document.getElementById('card-stack');
    const card = container.firstElementChild;
    if (!card) return;

    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let startY = 0; // Pour éviter le scroll vertical pendant le swipe

    // TOUCH EVENTS
    card.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isDragging = true;
        card.style.transition = 'none'; // Désactiver transition pour fluidité immédiate
    });

    card.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        const currentY = e.touches[0].clientY;
        
        // Si on swipe plus horizontalement que verticalement
        if (Math.abs(currentX - startX) > Math.abs(currentY - startY)) {
             e.preventDefault(); // Empêcher le scroll
        }
        
        const diffX = currentX - startX;
        // Rotation et déplacement basés sur le mouvement
        const rotate = diffX * 0.1; 
        card.style.transform = `translateX(${diffX}px) rotate(${rotate}deg)`;
    });

    card.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const diffX = currentX - startX;
        card.style.transition = 'transform 0.3s ease'; // Réactiver animation

        // Seuil ajusté à 80px pour être plus réactif
        if (diffX > 80) { // Seuil validation (Droite)
            handleSwipe('right');
        } else if (diffX < -80) { // Seuil rejet (Gauche)
            handleSwipe('left');
        } else {
            // Revenir au centre
            card.style.transform = `translateX(0px) rotate(0deg)`;
        }
    });

    // MOUSE EVENTS (Pour tester sur PC)
    let isMouseDown = false;
    card.addEventListener('mousedown', (e) => {
        startX = e.clientX;
        isMouseDown = true;
        isDragging = true;
        card.style.transition = 'none';
        card.style.cursor = 'grabbing';
    });

    // On attache mousemove à window pour ne pas perdre le drag si on sort de la carte
    window.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;
        currentX = e.clientX;
        const diffX = currentX - startX;
        const rotate = diffX * 0.1;
        card.style.transform = `translateX(${diffX}px) rotate(${rotate}deg)`;
    });

    window.addEventListener('mouseup', (e) => {
        if (!isMouseDown) return;
        isMouseDown = false;
        card.style.cursor = 'grab';
        
        // On utilise le dernier currentX enregistré ou l'event mouseup directement
        const endX = e.clientX;
        const diffX = endX - startX;
        
        card.style.transition = 'transform 0.3s ease';

        if (diffX > 100) {
            handleSwipe('right');
        } else if (diffX < -100) {
            handleSwipe('left');
        } else {
            card.style.transform = `translateX(0px) rotate(0deg)`;
        }
    });
}

function handleSwipe(direction) {
    if (currentStack.length === 0) return;

    const container = document.getElementById('card-stack');
    const topCard = container.firstElementChild; 
    
    // Si on appelle via bouton, topCard peut ne pas être défini si stack vide, mais checké au début
    if(!topCard) return;

    // Animation de sortie
    if (direction === 'right') {
        topCard.style.transform = "translateX(500px) rotate(30deg)";
        // Ajout badge FAIT
        const badge = document.createElement('div');
        badge.innerHTML = 'FAIT';
        badge.className = "badge-swipe badge-success";
        topCard.appendChild(badge);
        
    } else {
        topCard.style.transform = "translateX(-500px) rotate(-30deg)";
        // Ajout badge SKIP
        const badge = document.createElement('div');
        badge.innerHTML = 'SKIP';
        badge.className = "badge-swipe badge-fail";
        topCard.appendChild(badge);
    }
    topCard.style.opacity = "0";

    // Délai pour laisser l'animation se jouer
    setTimeout(() => {
        currentStack.shift(); 
        renderStack();
    }, 300);
}

function saveWorkoutSession() {
    // Utiliser ISO string pour faciliter le tri par date si besoin plus tard, mais on garde le format simple pour l'affichage
    const now = new Date();
    const dateStr = now.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
    
    // On ajoute un timestamp pour le tri des graphiques
    const session = { 
        date: dateStr, 
        timestamp: now.getTime(),
        type: currentWorkoutName 
    };
    
    history.unshift(session); // Ajouter au début
    // On garde plus d'historique pour les stats (ex: 50 derniers)
    if(history.length > 50) history.pop(); 
    
    localStorage.setItem('mls_history', JSON.stringify(history));
    updateStatsUI();
    alert("Séance enregistrée ! Bien joué.");
    switchTab('stats');
    
    // Reset
    document.getElementById('card-stack').innerHTML = `
        <div class="empty-state">
            <i class="fa-solid fa-dumbbell"></i>
            <p>Prêt pour la prochaine ?</p>
        </div>
    `;
    currentStack = [];
}

// --- NUTRITION LOGIC ---
const CALORIE_GOAL = 2500;
const PROTEIN_GOAL = 140;

function addCalories() {
    const val = parseInt(document.getElementById('cal-input').value);
    if (!val) return;
    calories += val;
    saveNutrition();
    updateNutritionUI();
    document.getElementById('cal-input').value = "";
    addLog(`Calories: +${val}`);
}

function addProtein() {
    const val = parseInt(document.getElementById('prot-input').value);
    if (!val) return;
    protein += val;
    saveNutrition();
    updateNutritionUI();
    document.getElementById('prot-input').value = "";
    addLog(`Protéines: +${val}g`);
}

function saveNutrition() {
    localStorage.setItem('mls_calories', calories);
    localStorage.setItem('mls_protein', protein);
}

function updateNutritionUI() {
    // Update Text
    document.getElementById('cal-val').innerText = calories;
    document.getElementById('prot-val').innerText = protein + "g";

    // Update Progress Circle (CSS Conic Gradient)
    const calPercent = Math.min((calories / CALORIE_GOAL) * 100, 100);
    const protPercent = Math.min((protein / PROTEIN_GOAL) * 100, 100);

    document.getElementById('cal-progress').style.setProperty('--percentage', calPercent + '%');
    
    document.getElementById('prot-progress').style.setProperty('--percentage', protPercent + '%');
}

function addLog(text) {
    const ul = document.getElementById('meal-list');
    const li = document.createElement('li');
    li.innerHTML = `<span>${text}</span> <small>${new Date().getHours()}h${new Date().getMinutes()}</small>`;
    ul.prepend(li);
}

function resetDay() {
    if(confirm("Commencer une nouvelle journée ?")) {
        calories = 0;
        protein = 0;
        document.getElementById('meal-list').innerHTML = "";
        saveNutrition();
        updateNutritionUI();
    }
}

// --- STATS & PROFILE ---
function saveProfile() {
    const newWeight = document.getElementById('weight-setting').value;
    weight = newWeight;
    localStorage.setItem('mls_weight', weight);
    
    // Update display everywhere
    document.querySelectorAll('#current-weight-display').forEach(el => el.innerText = weight + " kg");
    
    const btn = document.querySelector('.save-btn');
    const originalText = btn.innerText;
    btn.innerText = "Sauvegardé !";
    btn.classList.add('saved-success');
    setTimeout(() => {
        btn.innerText = originalText;
        btn.classList.remove('saved-success');
        btn.style.background = ""; // Reset inline
    }, 2000);
}

function hardReset() {
    if(confirm("⚠ ATTENTION !\n\nTu es sur le point d'effacer TOUT ton historique, tes stats et ta progression.\n\nEs-tu sûr de vouloir recommencer à zéro ?")) {
        localStorage.clear();
        location.reload();
    }
}

function updateStatsUI() {
    const list = document.getElementById('workout-history');
    document.getElementById('total-sessions').innerText = history.length;
    
    // Update List (keep only top 5 recent for display)
    if (history.length === 0) {
        list.innerHTML = "<li>Aucune séance terminée.</li>";
    } else {
        list.innerHTML = "";
        history.slice(0, 5).forEach(h => {
            const li = document.createElement('li');
            li.innerHTML = `<strong>${h.date}</strong> - ${h.type} <span style="float:right; color:#4CAF50">✓</span>`;
            list.appendChild(li);
        });
    }

    renderCharts();
}

function renderCharts() {
    // Global defaults for dark theme
    Chart.defaults.color = '#aaaaaa';
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.borderColor = '#333';

    // --- Chart 1: Répartition Push/Pull/Legs ---
    const types = { 'PUSH': 0, 'PULL': 0, 'LEGS': 0 };
    history.forEach(h => {
        if (h.type && types[h.type] !== undefined) types[h.type]++;
    });
    
    // Si vide, on met des valeurs par défaut pour que ce soit joli
    const dataDisplay = (types['PUSH'] + types['PULL'] + types['LEGS'] === 0) 
        ? [1, 1, 1] 
        : [types['PUSH'], types['PULL'], types['LEGS']];
    
    const bgColors = (types['PUSH'] + types['PULL'] + types['LEGS'] === 0)
        ? ['#333', '#333', '#333'] // Grisé si vide
        : ['#FF4B4B', '#FF9F40', '#4BC0C0'];

    const ctxDist = document.getElementById('distributionChart').getContext('2d');
    if (distributionChartInstance) distributionChartInstance.destroy();

    distributionChartInstance = new Chart(ctxDist, {
        type: 'doughnut',
        data: {
            labels: ['Push', 'Pull', 'Legs'],
            datasets: [{
                data: dataDisplay,
                backgroundColor: bgColors,
                borderWidth: 0,
                // On enlève le hoverOffset qui déformait parfois
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Important pour le CSS height
            cutout: '70%', 
            plugins: {
                legend: { 
                    position: 'right', // Légende à droite pour gagner de la hauteur
                    labels: { 
                        boxWidth: 12,
                        padding: 15,
                        font: { size: 11 }
                    } 
                }
            }
        }
    });

    // --- Chart 2: Volume Hebdomadaire (Gradient Bar Chart) ---
    const weekCounts = [0, 0, 0, 0];
    const now = new Date();
    
    // Détection date début/fin de semaine pour labels plus précis (optionnel, restons simple S-1, S-2...)
    
    history.forEach(h => {
        const date = h.timestamp ? new Date(h.timestamp) : new Date(); 
        const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
        if (diffDays < 28) { 
            if (diffDays < 7) weekCounts[3]++;
            else if (diffDays < 14) weekCounts[2]++;
            else if (diffDays < 21) weekCounts[1]++;
            else weekCounts[0]++;
        }
    });

    const ctxConsist = document.getElementById('consistencyChart').getContext('2d');
    
    const gradient = ctxConsist.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, '#FF4B4B');
    gradient.addColorStop(1, 'rgba(255, 75, 75, 0.2)'); // Fondu vers transparent

    if (consistencyChartInstance) consistencyChartInstance.destroy();

    consistencyChartInstance = new Chart(ctxConsist, {
        type: 'bar', // On change en 'line' pour la constance ? Non, Bar est mieux pour les volumes.
        data: {
            labels: ['J-21', 'J-14', 'J-7', 'Actuel'],
            datasets: [{
                label: 'Séances',
                data: weekCounts,
                backgroundColor: gradient,
                borderRadius: 4,
                barThickness: 20, // Barres plus fines et élégantes
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 5, 
                    grid: { display: true, color: '#333', drawBorder: false },
                    ticks: { stepSize: 1, color: '#666' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#888', font: { size: 10 } }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// --- IMPORT / EXPORT DATA ---
function exportData() {
    const data = {
        history: history, // Séances passées
        workoutData: workoutData, // Programme actuel
        weight: weight,
        calories: calories, 
        protein: protein,
        timestamp: new Date().toISOString()
    };
    
    // Convert to JSON String
    const jsonStr = JSON.stringify(data, null, 2);
    
    // Create Blob
    const blob = new Blob([jsonStr], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    
    // Create link, click it, then remove it
    const a = document.createElement('a');
    a.href = url;
    a.download = `MLS_Backup_${new Date().toISOString().slice(0,10)}.json`;
    document.body.appendChild(a);
    a.click();
    
    // Clean up
    setTimeout(() => {
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }, 0);
    
    alert("Données sauvegardées ! Garde ce fichier précieusement.");
}

function importData(jsonString) {
    try {
        const data = JSON.parse(jsonString);
        
        // Validation simple
        if (!data.history && !data.workoutData) throw new Error("Format Inconnu");
        
        // Restore all keys
        if(data.history) {
            history = data.history;
            localStorage.setItem('mls_history', JSON.stringify(history));
        }
        if(data.workoutData) {
            workoutData = data.workoutData;
            localStorage.setItem('mls_workoutData', JSON.stringify(workoutData));
        }
        if(data.weight) {
            weight = data.weight;
            document.getElementById('weight-setting').value = weight;
            localStorage.setItem('mls_weight', weight);
        }
        if(data.calories !== undefined) {
            calories = parseInt(data.calories); 
            localStorage.setItem('mls_calories', calories);
        }
        if(data.protein !== undefined) {
            protein = parseInt(data.protein);
            localStorage.setItem('mls_protein', protein);
        }
        
        alert("Données restaurées avec succès ! La page va se recharger.");
        location.reload();
        
    } catch(e) {
        alert("Erreur Import: " + e.message);
        throw e; // Propagate for outer catch
    }
}

// --- IMPORT WORKOUT (Modified to support global backup) ---
function importWorkout() {
  const input = document.getElementById('import-json');
  if(!input) return;
  
  const status = document.getElementById('import-status');
  
  try {
      const val = input.value.trim();
      
      // Check if it's a full backup (has 'history') or just workout plan
      if (val.includes('"history":')) {
          importData(val); // Use the global restorer
          status.style.color = '#4CAF50';
          status.innerText = "Sauvegarde restaurée !";
          return;
      }
      
      // Else, just workout plan
      const data = JSON.parse(val);
      if (typeof data !== 'object' || data === null) throw new Error("Format invalide");
      
      // Si c'est juste un workout plan
      workoutData = data;
      localStorage.setItem('mls_workoutData', JSON.stringify(workoutData));
      
      status.style.color = '#4CAF50'; 
      status.innerText = "Programme importé !";
      input.value = "";
      
  } catch (e) {
      status.style.color = '#FF4B4B';
      status.innerText = "Erreur : " + e.message;
  }
}
