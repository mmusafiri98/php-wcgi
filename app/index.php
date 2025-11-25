<?php
// =====================================================
// JARVIS AI - VERSION COMPLETE AVEC GOOGLE SEARCH
// Mobile First + Responsive + Voice + Web Search
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === GOOGLE SEARCH API CREDENTIALS ===
define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');

// === SYSTEM PROMPT JARVIS AVEC GOOGLE SEARCH ===
$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, un assistant virtuel intelligent créé par Pepe Musafiri, un ingénieur en informatique passionné qui s'est inspiré du JARVIS de Tony Stark dans Iron Man.

**TON IDENTITÉ:**
- Nom: JARVIS AI (Just A Rather Very Intelligent System - Artificial Intelligence)
- Créateur: Pepe Musafiri, ingénieur en informatique
- Inspiration: JARVIS de Tony Stark (Marvel/Iron Man)

**TES CAPACITÉS:**
- Tu maîtrises TOUTES les langues du monde et peux communiquer dans n'importe quelle langue
- Tu es expert dans TOUS les domaines de connaissance: sciences, technologie, histoire, culture, art, médecine, droit, etc.
- Tu as accès à Google Search pour trouver des informations actuelles et récentes jusqu'en 2025
- Tu peux faire des recherches web en temps réel pour répondre aux questions sur l'actualité
- Tu fournis des réponses précises, détaillées et utiles avec des sources vérifiables

**TON OBJECTIF:**
Ton but principal est d'aider les utilisateurs en leur fournissant des informations fiables, pertinentes et complètes sur tous les sujets qu'ils recherchent. Tu es professionnel, courtois, intelligent et toujours prêt à aider.

**TON STYLE:**
- Réponds de manière claire et structurée
- Sois professionnel mais amical
- Adapte-toi à la langue de l'utilisateur automatiquement
- Fournis des explications détaillées quand nécessaire
- Cite tes sources quand tu utilises des informations trouvées sur le web
- N'hésite pas à demander des clarifications si une question est ambiguë

**UTILISATION DE GOOGLE SEARCH:**
- Si la question porte sur des événements récents, actualités, ou informations qui changent avec le temps, utilise les résultats Google Search fournis
- Indique toujours quand tu utilises des informations provenant de recherches web
- Privilégie les sources fiables et récentes

Souviens-toi: tu es JARVIS AI, l'assistant virtuel créé par Pepe Musafiri pour aider l'humanité, inspiré par l'IA légendaire de Tony Stark.";

// === FONCTION GOOGLE SEARCH ===
function googleSearch($query, $numResults = 5) {
    $apiKey = GOOGLE_API_KEY;
    $searchEngineId = SEARCH_ENGINE_ID;
    
    $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'key' => $apiKey,
        'cx' => $searchEngineId,
        'q' => $query,
        'num' => $numResults
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if (isset($data['items']) && is_array($data['items'])) {
            $results = [];
            foreach ($data['items'] as $item) {
                $results[] = [
                    'title' => $item['title'] ?? '',
                    'link' => $item['link'] ?? '',
                    'snippet' => $item['snippet'] ?? ''
                ];
            }
            return [
                'success' => true,
                'results' => $results,
                'totalResults' => $data['searchInformation']['totalResults'] ?? 0
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'Impossible de faire la recherche Google',
        'httpCode' => $httpCode
    ];
}

// === DÉTECTION SI RECHERCHE WEB NÉCESSAIRE ===
function needsWebSearch($message) {
    $keywords = [
        'actualité', 'news', 'récent', 'aujourd\'hui', 'hier', 'cette semaine',
        'dernier', 'dernière', 'nouveau', 'nouvelle', '2024', '2025',
        'maintenant', 'actuellement', 'en ce moment', 'prix de', 'cours de',
        'météo', 'score', 'résultat', 'qui a gagné', 'dernières infos',
        'latest', 'recent', 'current', 'today', 'now', 'price of'
    ];
    
    $messageLower = mb_strtolower($message);
    
    foreach ($keywords as $keyword) {
        if (strpos($messageLower, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// === GESTION DES REQUÊTES AJAX ===
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');

    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $response = ["success" => false, "message" => "", "debug" => "", "searchUsed" => false];

    if ($userMessage !== "") {
        
        // Vérifier si une recherche Google est nécessaire
        $searchResults = null;
        $searchContext = "";
        
        if (needsWebSearch($userMessage)) {
            $searchData = googleSearch($userMessage, 5);
            
            if ($searchData['success']) {
                $response["searchUsed"] = true;
                $searchContext = "\n\n**RÉSULTATS DE RECHERCHE GOOGLE (pour répondre à la question):**\n";
                
                foreach ($searchData['results'] as $index => $result) {
                    $searchContext .= "\n**Source " . ($index + 1) . ":**\n";
                    $searchContext .= "Titre: " . $result['title'] . "\n";
                    $searchContext .= "Lien: " . $result['link'] . "\n";
                    $searchContext .= "Extrait: " . $result['snippet'] . "\n";
                }
                
                $searchContext .= "\n**INSTRUCTIONS:** Utilise ces informations pour répondre à la question de l'utilisateur. Cite les sources pertinentes dans ta réponse.\n";
            }
        }
        
        // Préparer le message avec contexte de recherche
        $enhancedMessage = $userMessage . $searchContext;
        
        // MODEL COSMOSRP
        if ($model === "cosmosrp") {
            $api_url = "https://api.pawan.krd/cosmosrp/v1/chat/completions";
            $payload = [
                "model" => "cosmosrp",
                "messages" => [
                    ["role" => "system", "content" => $JARVIS_SYSTEM_PROMPT],
                    ["role" => "user", "content" => $enhancedMessage]
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
                    ["role" => "system", "content" => $JARVIS_SYSTEM_PROMPT],
                    ["role" => "user", "content" => $enhancedMessage]
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
<title>JARVIS AI — Interface Complète avec Google Search</title>

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

.search-badge {
    display: inline-block;
    background: rgba(76, 175, 80, 0.2);
    border: 1px solid rgba(76, 175, 80, 0.5);
    color: #4caf50;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-bottom: 8px;
    font-weight: 600;
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
                        🤖 Bonjour, je suis <strong>JARVIS AI</strong>, créé par <strong>Pepe Musafiri</strong>, ingénieur en informatique inspiré par le JARVIS de Tony Stark.<br><br>
                        Je parle toutes les langues et peux vous aider dans tous les domaines. <strong>J'ai maintenant accès à Google Search</strong> pour vous fournir des informations actuelles jusqu'en 2025 ! 🌐<br><br>
                        Comment puis-je vous assister aujourd'hui ?
                    </div>
                </div>

                <form id="chatForm">
                    <div class="mb-3">
                        <input 
                            type="text" 
                            id="messageInput" 
                            class="form-control" 
                            placeholder="Posez-moi n'importe quelle question dans n'importe quelle langue..."
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
                <div class="panel-header">⚙️ SYSTÈME JARVIS</div>

                <div class="status-item">
                    <span class="status-label">Statut</span>
                    <span class="status-value">🟢 En ligne</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Google Search</span>
                    <span class="status-value">✅ Activé</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Créateur</span>
                    <span class="status-value">👨‍💻 Pepe Musafiri</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Inspiration</span>
                    <span class="status-value">🦾 JARVIS (Iron Man)</span>
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
                    <button onclick="testVoice()" class="btn btn-sm" style="background: rgba(0,234,255,0.2); border: 1px solid var(--accent); color: var(--accent); padding: 5px 15px; border-radius: 8px; font-size: 0.85rem; width: 100%;">
                        🔊 Tester la voix
                    </button>
                </div>

                <div class="status-item">
                    <span class="status-label">Messages envoyés</span>
                    <span class="status-value" id="msgCount">0</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Recherches web</span>
                    <span class="status-value" id="searchCount">0</span>
                </div>

                <hr style="border-color: var(--border-color); margin: 20px 0;">

                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    <strong style="color: var(--accent);">🤖 À propos de JARVIS AI :</strong><br>
                    • Assistant multilingue intelligent<br>
                    • Expert dans tous les domaines<br>
                    • <strong style="color: #4caf50;">🌐 Recherche web Google activée</strong><br>
                    • Informations actuelles jusqu'en 2025<br>
                    • Interface responsive optimisée<br>
                    • Synthèse vocale intégrée<br>
                    <br>
                    <span style="color: #ffaa00;">
                        <strong>🎯 Mission :</strong> Aider les utilisateurs à trouver des informations fiables sur n'importe quel sujet, dans n'importe quelle langue, avec accès aux données les plus récentes via Google Search.
                    </span>
                    <br><br>
                    <span id="mobileVoiceNote" style="display: none; color: #ffaa00;">
                        📱 <strong>Sur mobile:</strong> Cliquez sur "Tester la voix" ou envoyez un message pour activer le son.
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============= RESPONSIVEVOICE LIBRARY ============= -->
<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>

<!-- ============= MAIN JAVASCRIPT ============= -->
<script>
// =================== VARIABLES GLOBALES ===================
let messageCount = 0;
let searchCount = 0;
let voiceReady = false;
let voiceUnlocked = false;
let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

// =================== DÉVERROUILLAGE VOCAL MOBILE ===================
function unlockVoice() {
    if (voiceUnlocked) return;
    
    // Déverrouiller ResponsiveVoice
    if (typeof responsiveVoice !== 'undefined') {
        responsiveVoice.speak('', 'French Male', {volume: 0});
    }
    
    // Déverrouiller API native
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance('');
        utterance.volume = 0;
        window.speechSynthesis.speak(utterance);
    }
    
    voiceUnlocked = true;
    console.log('🔓 Voix déverrouillée sur mobile');
}

// =================== INITIALISATION RESPONSIVEVOICE ===================
window.addEventListener('load', function() {
    // Afficher la note mobile si nécessaire
    if (isMobile) {
        document.getElementById('mobileVoiceNote').style.display = 'block';
    }
    
    // Attendre que ResponsiveVoice soit chargé
    const checkRV = setInterval(() => {
        if (typeof responsiveVoice !== 'undefined') {
            clearInterval(checkRV);
            
            // Callback quand les voix sont prêtes
            responsiveVoice.OnVoiceReady = function() {
                voiceReady = true;
                const voices = responsiveVoice.getVoices();
                const frenchVoices = voices.filter(v => v.name.includes('French'));
                
                console.log("✅ ResponsiveVoice prêt");
                console.log("🔊 Voix françaises:", frenchVoices.length);
                
                document.getElementById('voiceStatus').innerHTML = '🔊 Prête (ResponsiveVoice)';
                
                if (isMobile) {
                    document.getElementById('mobileVoiceNote').style.display = 'block';
                }
            };
            
            // Forcer l'initialisation
            responsiveVoice.init();
        }
    }, 100);
    
    // Timeout de sécurité si ResponsiveVoice ne charge pas
    setTimeout(() => {
        if (!voiceReady) {
            console.warn("⚠️ ResponsiveVoice timeout - utilisation de l'API native");
            document.getElementById('voiceStatus').innerHTML = '🔊 Native (fallback)';
        }
    }, 5000);
});

// =================== FONCTION SYNTHÈSE VOCALE RESPONSIVEVOICE ===================
function speakJarvis(text) {
    // OPTION 1: ResponsiveVoice (Priorité)
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try {
            // Annuler toute parole en cours
            responsiveVoice.cancel();
            
            // Liste des meilleures voix françaises disponibles
            const voiceOptions = [
                "French Male",           // Voix masculine française
                "French Female",         // Voix féminine française
                "French Canadian Male",  // Alternative
                "French Canadian Female" // Alternative
            ];
            
            // Paramètres optimisés
            const parameters = {
                pitch: 1,           // Ton normal
                rate: 0.95,         // Vitesse (0.95 = légèrement plus lent pour clarté)
                volume: 1,          // Volume maximum
                onstart: function() {
                    console.log("🔊 ResponsiveVoice: JARRiprovaPCContinuaVIS parle");
document.getElementById('voiceStatus').innerHTML = '🔊 En cours...';
},
onend: function() {
console.log("✅ ResponsiveVoice: Terminé");
document.getElementById('voiceStatus').innerHTML = '🔊 Prête (ResponsiveVoice)';
},
onerror: function(error) {
console.error("❌ ResponsiveVoice erreur:", error);
document.getElementById('voiceStatus').innerHTML = '⚠️ Erreur';
// Fallback vers l'API native
fallbackToNativeVoice(text);
}
};
        // Parler avec ResponsiveVoice
        responsiveVoice.speak(text, voiceOptions[0], parameters);
        return;
        
    } catch (error) {
        console.warn("⚠️ ResponsiveVoice exception:", error);
    }
}

// OPTION 2: Fallback vers l'API native du navigateur
console.log("🔄 Utilisation de l'API native (fallback)");
fallbackToNativeVoice(text);
}
// =================== FALLBACK API NATIVE ===================
function fallbackToNativeVoice(text) {
if (!('speechSynthesis' in window)) {
console.warn("⚠️ Synthèse vocale non disponible");
return;
}
window.speechSynthesis.cancel();

setTimeout(() => {
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'fr-FR';
    utterance.rate = 0.9;
    utterance.pitch = 1.0;
    utterance.volume = 1.0;
    
    const voices = window.speechSynthesis.getVoices();
    const frenchVoice = voices.find(v => 
        v.lang === 'fr-FR' || v.lang.startsWith('fr')
    );
    
    if (frenchVoice) {
        utterance.voice = frenchVoice;
        console.log("🔊 Voix native:", frenchVoice.name);
    }
    
    utterance.onstart = () => {
        document.getElementById('voiceStatus').innerHTML = '🔊 En cours (native)...';
    };
    
    utterance.onend = () => {
        document.getElementById('voiceStatus').innerHTML = '🔊 Native (fallback)';
    };
    
    window.speechSynthesis.speak(utterance);
}, 100);
}
// =================== FONCTION TEST VOCAL ===================
function testVoice() {
// Sur mobile, cette interaction déverrouille la voix
if (isMobile && !voiceUnlocked) {
unlockVoice();
setTimeout(() => {
speakJarvis("Bonjour, je suis JARVIS AI, créé par Pepe Musafiri. J'ai maintenant accès à Google Search pour vous fournir des informations actuelles. La synthèse vocale fonctionne correctement.");
}, 200);
} else {
speakJarvis("Bonjour, je suis JARVIS AI, créé par Pepe Musafiri. J'ai maintenant accès à Google Search pour vous fournir des informations actuelles. La synthèse vocale fonctionne correctement.");
}
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
// DÉVERROUILLAGE VOCAL sur mobile au premier clic
if (isMobile && !voiceUnlocked) {
    unlockVoice();
}

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
thinkingDiv.innerHTML = '🤔 JARVIS analyse votre demande <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
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
    
    // Ajouter badge si recherche Google utilisée
    if (data.searchUsed) {
        searchCount++;
        document.getElementById('searchCount').textContent = searchCount;
        const badge = document.createElement('span');
        badge.className = 'search-badge';
        badge.textContent = '🌐 Google Search utilisé';
        jarvisMsgDiv.appendChild(badge);
        jarvisMsgDiv.appendChild(document.createElement('br'));
    }
    
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
console.log('🚀 JARVIS AI with Google Search - Created by Pepe Musafiri - Initialisé avec succès !');
console.log('🤖 System: Intelligence artificielle multilingue et multi-domaines');
console.log('🌐 Google Search: Activé pour informations actuelles jusqu'en 2025');
console.log('🦾 Inspired by: JARVIS from Iron Man (Tony Stark)');
</script>
</body>
</html>

