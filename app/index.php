<?php
// =====================================================
// JARVIS AI - VERSION COMPLETE ET OPTIMISÉE
// Mobile First + Responsive + Voice + Animations
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === GESTION DES REQUÊTES AJAX ===
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');

    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $response = ["success" => false, "message" => "", "debug" => ""];

    if ($userMessage !== "") {
        
        // MODEL COSMOSRP
        if ($model === "cosmosrp") {
            $api_url = "https://api.pawan.krd/cosmosrp/v1/chat/completions";
            $payload = [
                "model" => "cosmosrp",
                "messages" => [
                    ["role" => "system", "content" => "Tu es JARVIS AI, assistant virtuel professionnel créé par Pepe Musafiri."],
                    ["role" => "user", "content" => $userMessage]
                ]
            ];

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                $response["message"] = "❌ Erreur CURL : " . $err;
            } else {
                $data = json_decode($raw, true);
                if (isset($data["choices"][0]["message"]["content"])) {
                    $response["message"] = $data["choices"][0]["message"]["content"];
                    $response["success"] = true;
                } else {
                    $response["message"] = "❌ Pas de réponse de CosmosRP (HTTP $httpCode)";
                    $response["debug"] = $raw;
                }
            }
        }
        
        // MODEL C4AI AYA EXPANSE
        else if ($model === "c4ai") {
            $api_url = "https://api.cohere.com/v2/chat";
            $payload = [
                "model" => "c4ai-aya-expanse-32b",
                "messages" => [
                    ["role" => "user", "content" => $userMessage]
                ]
            ];

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer Uw540GN865rNyiOs3VMnWhRaYQ97KAfudAHAnXzJ"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                $response["message"] = "❌ Erreur CURL : " . $err;
            } else {
                $data = json_decode($raw, true);

                if (isset($data["message"]["content"][0]["text"])) {
                    $response["message"] = $data["message"]["content"][0]["text"];
                    $response["success"] = true;
                } elseif (isset($data["text"])) {
                    $response["message"] = $data["text"];
                    $response["success"] = true;
                } elseif (isset($data["error"])) {
                    $response["message"] = "❌ Erreur API : " . json_encode($data["error"]);
                    $response["debug"] = $raw;
                } else {
                    $response["message"] = "❌ Réponse API inconnue (HTTP $httpCode)";
                    $response["debug"] = $raw;
                }
            }
        }
    } else {
        $response["message"] = "❌ Message vide.";
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>JARVIS AI — Interface Complète</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

<style>
/* =================== VARIABLES =================== */
:root {
    --accent: #00eaff;
    --bg-dark: #020610;
    --panel-bg: rgba(0, 255, 255, 0.06);
    --border-color: rgba(0, 255, 255, 0.15);
}

/* =================== BASE =================== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--bg-dark);
    color: var(--accent);
    font-family: "Orbitron", Arial, sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
}

/* =================== LAYOUT CONTAINER =================== */
.main-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 15px;
}

/* =================== JARVIS GIF SECTION =================== */
.jarvis-visual {
    width: 100%;
    height: 300px;
    border-radius: 15px;
    overflow: hidden;
    background: #000;
    border: 2px solid var(--border-color);
    box-shadow: 0 0 30px rgba(0, 234, 255, 0.3);
    margin-bottom: 20px;
    position: relative;
}

.jarvis-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.jarvis-visual::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(0, 234, 255, 0.1));
    pointer-events: none;
}

/* =================== PANELS =================== */
.panel {
    background: var(--panel-bg);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    padding: 20px;
    backdrop-filter: blur(10px);
    margin-bottom: 20px;
}

.panel-header {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--accent);
    text-shadow: 0 0 10px rgba(0, 234, 255, 0.5);
}

/* =================== CHAT WINDOW =================== */
#chatWindow {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 15px;
    height: 400px;
    overflow-y: auto;
    margin-bottom: 15px;
    scroll-behavior: smooth;
}

#chatWindow::-webkit-scrollbar {
    width: 8px;
}

#chatWindow::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}

#chatWindow::-webkit-scrollbar-thumb {
    background: var(--accent);
    border-radius: 10px;
}

/* =================== MESSAGES =================== */
.msg-user {
    background: rgba(0, 234, 255, 0.15);
    padding: 12px 15px;
    border-radius: 15px 15px 5px 15px;
    margin: 10px 0;
    text-align: right;
    max-width: 85%;
    margin-left: auto;
    border: 1px solid rgba(0, 234, 255, 0.3);
    animation: slideInRight 0.3s ease;
}

.msg-jarvis {
    background: rgba(255, 255, 255, 0.1);
    padding: 12px 15px;
    border-radius: 15px 15px 15px 5px;
    margin: 10px 0;
    text-align: left;
    max-width: 85%;
    margin-right: auto;
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: slideInLeft 0.3s ease;
}

/* =================== ANIMATIONS =================== */
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes blink {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
}

.typing {
    border-right: 3px solid var(--accent);
    animation: blink 1s infinite;
}

.dots span {
    animation: blink 1.5s infinite;
}

.dots span:nth-child(2) {
    animation-delay: 0.3s;
}

.dots span:nth-child(3) {
    animation-delay: 0.6s;
}

/* =================== FORM ELEMENTS =================== */
.form-control, .form-select {
    background: rgba(0, 0, 0, 0.6) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--accent) !important;
    border-radius: 10px !important;
    padding: 12px !important;
    font-family: "Orbitron", Arial;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.5) !important;
    border-color: var(--accent) !important;
}

.btn-send {
    background: linear-gradient(135deg, #00eaff, #0088cc);
    border: none;
    color: #000;
    font-weight: 700;
    padding: 12px 30px;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 234, 255, 0.6);
}

.btn-send:active {
    transform: translateY(0);
}

/* =================== STATUS PANEL =================== */
.status-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0, 234, 255, 0.1);
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    color: rgba(255, 255, 255, 0.7);
}

.status-value {
    color: #8bffcf;
    font-weight: 700;
}

/* =================== RESPONSIVE =================== */
@media (min-width: 768px) {
    .jarvis-visual {
        height: 400px;
    }
    
    #chatWindow {
        height: 500px;
    }
}

@media (min-width: 992px) {
    .jarvis-visual {
        height: 500px;
    }
}

@media (max-width: 576px) {
    .main-container {
        padding: 10px;
    }
    
    .jarvis-visual {
        height: 250px;
    }
    
    #chatWindow {
        height: 350px;
    }
    
    .panel {
        padding: 15px;
    }
    
    .panel-header {
        font-size: 1.2rem;
    }
}

/* =================== LOADING STATE =================== */
.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
</head>

<body>
<div class="main-container">

    <!-- ============= JARVIS GIF (ALWAYS ON TOP) ============= -->
    <div class="jarvis-visual">
        <img src="jarvis.gif" alt="JARVIS Interface" loading="eager">
    </div>

    <div class="row g-3">
        
        <!-- ============= CHAT PANEL ============= -->
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">💬 JARVIS AI CHAT</div>

                <div id="chatWindow">
                    <div class="msg-jarvis">
                        👋 Bonjour, je suis JARVIS. Comment puis-je vous aider aujourd'hui ?
                    </div>
                </div>

                <form id="chatForm">
                    <div class="mb-3">
                        <input 
                            type="text" 
                            id="messageInput" 
                            class="form-control" 
                            placeholder="Tapez votre message ici..."
                            autocomplete="off"
                            required
                        >
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-7">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">🤖 C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">🌌 CosmosRP</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-5">
                            <button type="submit" id="sendBtn" class="btn btn-send w-100">
                                ▶ Envoyer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============= STATUS PANEL ============= -->
        <div class="col-12 col-lg-4">
            <div class="panel">
                <div class="panel-header">⚙️ SYSTÈME</div>

                <div class="status-item">
                    <span class="status-label">Statut</span>
                    <span class="status-value">🟢 En ligne</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Modèle actuel</span>
                    <span class="status-value" id="currentModel">C4AI Aya Expanse 32B</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Synthèse vocale</span>
                    <span class="status-value" id="voiceStatus">🔄 Chargement...</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Messages envoyés</span>
                    <span class="status-value" id="msgCount">0</span>
                </div>

                <hr style="border-color: var(--border-color); margin: 20px 0;">

                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    <strong style="color: var(--accent);">ℹ️ Informations :</strong><br>
                    • Interface responsive optimisée<br>
                    • Synthèse vocale intégrée<br>
                    • Animation typing en temps réel<br>
                    • Support mobile & desktop
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============= MAIN JAVASCRIPT ============= -->
<script>
// =================== VARIABLES GLOBALES ===================
let messageCount = 0;
let voiceReady = false;

// =================== INITIALISATION ===================
let availableVoices = [];
let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

// Charger les voix disponibles
function loadVoices() {
    availableVoices = window.speechSynthesis.getVoices();
    
    if (availableVoices.length > 0) {
        const frenchVoices = availableVoices.filter(v => v.lang.startsWith('fr'));
        console.log("🔊 Voix françaises trouvées:", frenchVoices.length);
        
        document.getElementById('voiceStatus').innerHTML = '🔊 Prête';
        voiceReady = true;
    }
}

// Sur mobile/Safari, les voix se chargent de manière asynchrone
if ('speechSynthesis' in window) {
    loadVoices();
    window.speechSynthesis.onvoiceschanged = loadVoices;
    
    setTimeout(() => {
        if (!voiceReady) {
            loadVoices();
        }
    }, 1000);
} else {
    document.getElementById('voiceStatus').innerHTML = '❌ Non supportée';
    console.warn("⚠️ Synthèse vocale non supportée");
}

// =================== FONCTION SYNTHÈSE VOCALE (OPTIMISÉE MOBILE) ===================
function speakJarvis(text) {
    if (!('speechSynthesis' in window)) {
        console.warn("⚠️ Synthèse vocale non disponible");
        return;
    }

    // IMPORTANT: Sur mobile, il faut annuler AVANT de parler
    window.speechSynthesis.cancel();
    
    // Court délai pour que l'annulation soit effective (crucial sur mobile)
    setTimeout(() => {
        const utterance = new SpeechSynthesisUtterance(text);
        
        // Configuration optimisée pour mobile ET desktop
        utterance.lang = 'fr-FR';
        utterance.rate = isMobile ? 0.95 : 0.9;  // Légèrement plus rapide sur mobile
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        // Sélectionner la meilleure voix française disponible
        const voices = window.speechSynthesis.getVoices();
        
        // Priorités de sélection de voix
        const voicePriorities = [
            // Voix premium
            v => v.lang === 'fr-FR' && v.name.toLowerCase().includes('thomas'),
            v => v.lang === 'fr-FR' && v.name.toLowerCase().includes('google'),
            v => v.lang === 'fr-FR' && v.name.toLowerCase().includes('amelie'),
            // Voix cloud (meilleure qualité)
            v => v.lang === 'fr-FR' && !v.localService,
            // N'importe quelle voix française
            v => v.lang === 'fr-FR',
            v => v.lang.startsWith('fr-'),
            v => v.lang.startsWith('fr')
        ];
        
        let selectedVoice = null;
        for (const priority of voicePriorities) {
            selectedVoice = voices.find(priority);
            if (selectedVoice) break;
        }
        
        if (selectedVoice) {
            utterance.voice = selectedVoice;
            console.log("🔊 Utilisation de:", selectedVoice.name);
        } else {
            console.log("🔊 Utilisation de la voix par défaut");
        }
        
        // Événements pour debug
        utterance.onstart = () => {
            console.log("▶️ JARVIS commence à parler");
        };
        
        utterance.onend = () => {
            console.log("✅ JARVIS a fini de parler");
        };
        
        utterance.onerror = (e) => {
            console.error("❌ Erreur synthèse vocale:", e.error);
            // Sur mobile, si erreur "interrupted", on réessaie une fois
            if (e.error === 'interrupted' || e.error === 'canceled') {
                console.log("🔄 Tentative de relance...");
                setTimeout(() => {
                    window.speechSynthesis.speak(utterance);
                }, 100);
            }
        };
        
        // Parler !
        window.speechSynthesis.speak(utterance);
        
        // FIX MOBILE: Sur iOS/Android, parfois la voix s'arrête après 15 secondes
        // On force la continuation pour les longs textes
        if (isMobile && text.length > 200) {
            let spokenLength = 0;
            const checkInterval = setInterval(() => {
                if (!window.speechSynthesis.speaking) {
                    clearInterval(checkInterval);
                } else {
                    // Forcer la continuation (bug iOS connu)
                    window.speechSynthesis.pause();
                    window.speechSynthesis.resume();
                }
            }, 10000); // Toutes les 10 secondes
        }
        
    }, 100); // Délai de 100ms crucial pour mobile
}

// =================== FONCTION TYPING ANIMATION ===================
function typeWriter(text, element) {
    let index = 0;
    element.classList.add('typing');

    function type() {
        if (index < text.length) {
            element.textContent += text.charAt(index);
            index++;
            element.parentElement.parentElement.scrollTop = element.parentElement.parentElement.scrollHeight;
            setTimeout(type, 20);
        } else {
            element.classList.remove('typing');
            setTimeout(() => speakJarvis(text), 300);
        }
    }
    type();
}

// =================== GESTION DU FORMULAIRE ===================
document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const messageInput = document.getElementById('messageInput');
    const modelSelect = document.getElementById('modelSelect');
    const sendBtn = document.getElementById('sendBtn');
    const chatWindow = document.getElementById('chatWindow');
    
    const userMessage = messageInput.value.trim();
    const selectedModel = modelSelect.value;

    if (!userMessage) return;

    // Désactiver le bouton pendant l'envoi
    sendBtn.disabled = true;
    sendBtn.textContent = '⏳ Envoi...';

    // Incrémenter le compteur
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;

    // Afficher le message utilisateur
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'msg-user';
    userMsgDiv.textContent = userMessage;
    chatWindow.appendChild(userMsgDiv);

    // Afficher "JARVIS réfléchit..."
    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'msg-jarvis';
    thinkingDiv.innerHTML = '🤔 JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    chatWindow.appendChild(thinkingDiv);

    // Scroll
    chatWindow.scrollTop = chatWindow.scrollHeight;

    // Mettre à jour le modèle affiché
    const modelNames = {
        'c4ai': 'C4AI Aya Expanse 32B',
        'cosmosrp': 'CosmosRP'
    };
    document.getElementById('currentModel').textContent = modelNames[selectedModel];

    // Vider l'input
    messageInput.value = '';

    try {
        // Envoyer la requête AJAX
        const formData = new FormData();
        formData.append('message', userMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        // Supprimer "JARVIS réfléchit"
        thinkingDiv.remove();

        // Afficher la réponse avec effet typing
        const jarvisMsgDiv = document.createElement('div');
        jarvisMsgDiv.className = 'msg-jarvis';
        const typingSpan = document.createElement('span');
        jarvisMsgDiv.appendChild(typingSpan);
        chatWindow.appendChild(jarvisMsgDiv);

        // Debug si présent (masqué par défaut)
        if (data.debug && !data.success) {
            const debugDiv = document.createElement('details');
            debugDiv.style.cssText = 'color:#ff6b6b;font-size:10px;margin-top:10px;';
            debugDiv.innerHTML = `<summary>🔍 Debug Info</summary><pre>${data.debug}</pre>`;
            chatWindow.appendChild(debugDiv);
        }

        // Animation typing
        typeWriter(data.message, typingSpan);

        // Scroll final
        chatWindow.scrollTop = chatWindow.scrollHeight;

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
        console.error('Erreur:', error);
    } finally {
        // Réactiver le bouton
        sendBtn.disabled = false;
        sendBtn.textContent = '▶ Envoyer';
        messageInput.focus();
    }
});

// =================== FOCUS AUTOMATIQUE ===================
document.getElementById('messageInput').focus();

console.log('🚀 JARVIS AI Initialisé avec succès !');
</script>

</body>
</html>
