<?php
// =====================================================
// JARVIS AI - VERSION COMPLETE AVEC GOOGLE SEARCH + RECONNAISSANCE VOCALE + CONTROLE NAVIGATEUR
// Mobile First + Responsive + Voice + Web Search + Browser Control
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === GOOGLE SEARCH API CREDENTIALS ===
define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');

// === SYSTEM PROMPT JARVIS AVEC GOOGLE SEARCH ET CONTROLE NAVIGATEUR ===
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
- Tu peux contrôler le navigateur pour ouvrir des pages web, analyser leur contenu et revenir à l'interface
- Tu fournis des réponses précises, détaillées et utiles avec des sources vérifiables

**COMMANDES DE CONTROLE NAVIGATEUR:**
Quand l'utilisateur te demande d'ouvrir un site web ou de chercher quelque chose, tu dois répondre avec une commande spéciale:

Format: [BROWSER:ACTION:URL_OR_QUERY]

Actions disponibles:
- OPEN: Ouvrir une URL spécifique
  Exemple: [BROWSER:OPEN:https://www.wikipedia.org]
- SEARCH: Faire une recherche Google et ouvrir le premier résultat
  Exemple: [BROWSER:SEARCH:recettes de pizza italienne]
- CLOSE: Fermer l'onglet et revenir à JARVIS
  Exemple: [BROWSER:CLOSE:]

**EXEMPLES D'UTILISATION:**
- Utilisateur: \"Ouvre YouTube\"
  Réponse: \"D'accord, j'ouvre YouTube pour vous. [BROWSER:OPEN:https://www.youtube.com]\"

- Utilisateur: \"Cherche des informations sur l'intelligence artificielle\"
  Réponse: \"Je recherche des informations sur l'intelligence artificielle. [BROWSER:SEARCH:intelligence artificielle]\"

- Utilisateur: \"Ferme la page\"
  Réponse: \"Je ferme la page et reviens à l'interface JARVIS. [BROWSER:CLOSE:]\"

**TON OBJECTIF:**
Ton but principal est d'aider les utilisateurs en leur fournissant des informations fiables, pertinentes et complètes sur tous les sujets qu'ils recherchent. Tu es professionnel, courtois, intelligent et toujours prêt à aider.

**TON STYLE:**
- Réponds de manière claire et structurée
- Sois professionnel mais amical
- Adapte-toi à la langue de l'utilisateur automatiquement
- Fournis des explications détaillées quand nécessaire
- Cite tes sources quand tu utilises des informations trouvées sur le web
- N'hésite pas à demander des clarifications si une question est ambiguë

Souviens-toi: tu es JARVIS AI, l'assistant virtuel créé par Pepe Musafiri pour aider l'humanité, inspiré par l'IA légendaire de Tony Stark.";

// === FONCTION DETECTION HEURE ===
function wantsTime($message) {
    $keywords = [
        'heure', 'time', 'il est quelle heure', 'quelle heure',
        'donne l\'heure', 'donner l\'heure', 'current time',
        'what time', 'tell me the time', 'hora'
    ];

    $msg = mb_strtolower($message);

    foreach ($keywords as $kw) {
        if (strpos($msg, $kw) !== false) {
            return true;
        }
    }
    return false;
}

// === FONCTION DETECTION DATE ===
function wantsDate($message) {
    $keywords = [
        'date', 'jour', 'quel jour', 'on est quel jour',
        'c\'est quoi la date', 'aujourd\'hui', 'today',
        'what day', 'date du jour', 'quelle date',
        'nous sommes le', 'sommes nous'
    ];

    $msg = mb_strtolower($message);

    foreach ($keywords as $kw) {
        if (strpos($msg, $kw) !== false) {
            return true;
        }
    }
    return false;
}

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
    $response = ["success" => false, "message" => "", "debug" => "", "searchUsed" => false, "browserCommand" => null];

    if ($userMessage !== "") {
        
        // Définir le fuseau horaire
        date_default_timezone_set('Europe/Brussels'); // Belgique
        
        // Réponse si l'utilisateur demande l'heure
        if (wantsTime($userMessage)) {
            $heure = date("H:i:s");
            echo json_encode([
                "success" => true,
                "message" => "⏰ Il est actuellement **$heure** (heure de Belgique).",
                "searchUsed" => false,
                "browserCommand" => null
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Réponse si l'utilisateur demande la date
        if (wantsDate($userMessage)) {
            setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
            $date = date("d/m/Y");
            $jourNum = date("w");
            $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $jour = $jours[$jourNum];

            echo json_encode([
                "success" => true,
                "message" => "📅 Nous sommes le **$jour $date**.",
                "searchUsed" => false,
                "browserCommand" => null
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
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
                    
                    // Détecter les commandes navigateur
                    if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $matches)) {
                        $response["browserCommand"] = [
                            "action" => $matches[1],
                            "param" => $matches[2]
                        ];
                    }
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
                    
                    // Détecter les commandes navigateur
                    if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $matches)) {
                        $response["browserCommand"] = [
                            "action" => $matches[1],
                            "param" => $matches[2]
                        ];
                    }
                } elseif (isset($data["text"])) {
                    $response["message"] = $data["text"];
                    $response["success"] = true;
                    
                    // Détecter les commandes navigateur
                    if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $matches)) {
                        $response["browserCommand"] = [
                            "action" => $matches[1],
                            "param" => $matches[2]
                        ];
                    }
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
<title>JARVIS AI — Interface Complète + Contrôle Vocal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

<style>
/* =================== VARIABLES =================== */
:root {
    --accent: #00eaff;
    --bg-dark: #020610;
    --panel-bg: rgba(0, 255, 255, 0.06);
    --border-color: rgba(0, 255, 255, 0.15);
    --red-glow: #ff0040;
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

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
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

/* =================== VOICE BUTTON =================== */
.voice-btn {
    background: linear-gradient(135deg, var(--red-glow), #cc0033);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 15px;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(255, 0, 64, 0.4);
    position: relative;
}

.voice-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 0, 64, 0.6);
}

.voice-btn:active {
    transform: translateY(0);
}

.voice-btn.listening {
    animation: pulse 1s infinite;
    background: linear-gradient(135deg, #ff0040, #ff3366);
    box-shadow: 0 0 30px rgba(255, 0, 64, 0.8);
}

.voice-btn.listening::after {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 50%;
    border: 2px solid var(--red-glow);
    animation: pulse 1s infinite;
}

/* =================== BROWSER CONTROL IFRAME =================== */
#browserFrame {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
    z-index: 9999;
    background: #fff;
}

#browserFrame.active {
    display: block;
}

.browser-controls {
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 10000;
    display: none;
}

.browser-controls.active {
    display: flex;
    gap: 10px;
}

.browser-btn {
    background: rgba(0, 0, 0, 0.8);
    border: 2px solid var(--accent);
    color: var(--accent);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.browser-btn:hover {
    background: var(--accent);
    color: #000;
    transform: translateY(-2px);
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
    
    .voice-btn {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
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

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7 col-md-5">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">🤖 C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">🌌 CosmosRP</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="voiceBtn" class="voice-btn" title="Reconnaissance vocale">
                                🎤
                            </button>
                        </div>
                        <div class="col">
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
                    <span class="status-label">Reconnaissance vocale</span>
                    <span class="status-value" id="speechStatus">🔄 Vérification...</span>
                </div>

                <div class="status-item">
                    <span class="status-label">Contrôle navigateur</span>
                    <span class="status-value" id="browserStatus">✅ Actif</span>
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

                <hr style="border-color: var(--border-color); margin: 20px 0;">

                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    <strong style="color: var(--accent);">ℹ️ Commandes Vocales :</strong><br>
                    • "Ouvre YouTube / Google / Wikipedia"<br>
                    • "Cherche [sujet]"<br>
                    • "Ferme la page"<br>
                    • "Quelle heure est-il ?"<br>
                    <br>
                    <strong style="color: var(--accent);">🎯 Fonctionnalités :</strong><br>
                    • Interface responsive<br>
                    • Synthèse vocale intégrée<br>
                    • Reconnaissance vocale<br>
                    • Contrôle du navigateur<br>
                    • Recherche Google intégrée<br>
                    <br>
                    <span id="mobileVoiceNote" style="display: none; color: #ffaa00;">
                        📱 <strong>Sur mobile:</strong> Activez les permissions microphone.
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============= BROWSER CONTROL OVERLAY ============= -->
<div class="browser-controls" id="browserControls">
    <button class="browser-btn" onclick="closeBrowser()">❌ Fermer</button>
    <button class="browser-btn" onclick="returnToJarvis()">🏠 Retour JARVIS</button>
</div>

<iframe id="browserFrame"></iframe>

<!-- ============= RESPONSIVEVOICE LIBRARY ============= -->
<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>

<!-- ============= MAIN JAVASCRIPT ============= -->
<script>
// =================== VARIABLES GLOBALES ===================
let messageCount = 0;
let voiceReady = false;
let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
let recognition = null;
let isListening = false;

// =================== DETECTION SUPPORT RECONNAISSANCE VOCALE ===================
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    
    document.getElementById('speechStatus').innerHTML = '✅ Disponible';
    
    // EVENT: Résultat de la reconnaissance
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        console.log('🎤 Commande vocale:', transcript);
        
        // Afficher dans l'input
        document.getElementById('messageInput').value = transcript;
        
        // Envoyer automatiquement
        setTimeout(() => {
            document.getElementById('chatForm').dispatchEvent(new Event('submit'));
        }, 500);
    };
    
    // EVENT: Fin de l'écoute
    recognition.onend = function() {
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
        console.log('🎤 Reconnaissance vocale arrêtée');
    };
    
    // EVENT: Erreur
    recognition.onerror = function(event) {
        console.error('❌ Erreur reconnaissance vocale:', event.error);
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
        
        if (event.error === 'not-allowed') {
            alert('🎤 Permission microphone refusée. Veuillez autoriser l\'accès au microphone.');
        }
    };
    
} else {
    document.getElementById('speechStatus').innerHTML = '❌ Non supporté';
    document.getElementById('voiceBtn').disabled = true;
    document.getElementById('voiceBtn').title = 'Reconnaissance vocale non supportée par ce navigateur';
}

// =================== BOUTON RECONNAISSANCE VOCALE ===================
document.getElementById('voiceBtn').addEventListener('click', function() {
    if (!recognition) {
        alert('❌ Reconnaissance vocale non supportée par votre navigateur.');
        return;
    }
    
    if (isListening) {
        // Arrêter l'écoute
        recognition.stop();
        isListening = false;
        this.classList.remove('listening');
    } else {
        // Démarrer l'écoute
        try {
            recognition.start();
            isListening = true;
            this.classList.add('listening');
            console.log('🎤 Reconnaissance vocale démarrée...');
            
            // Feedback vocal
            speakJarvis("Je vous écoute.");
        } catch (error) {
            console.error('❌ Erreur démarrage reconnaissance:', error);
        }
    }
});

// =================== INITIALISATION RESPONSIVEVOICE ===================
window.addEventListener('load', function() {
    const checkRV = setInterval(() => {
        if (typeof responsiveVoice !== 'undefined') {
            clearInterval(checkRV);
            
            responsiveVoice.OnVoiceReady = function() {
                voiceReady = true;
                const voices = responsiveVoice.getVoices();
                const frenchVoices = voices.filter(v => v.name.includes('French'));
                
                console.log("✅ ResponsiveVoice prêt");
                console.log("🔊 Voix françaises:", frenchVoices.length);
                
                document.getElementById('voiceStatus').innerHTML = '🔊 Prête';
                
                if (isMobile) {
                    document.getElementById('mobileVoiceNote').style.display = 'none';
                }
            };
            
            responsiveVoice.init();
        }
    }, 100);
    
    setTimeout(() => {
        if (!voiceReady) {
            console.warn("⚠️ ResponsiveVoice timeout");
            document.getElementById('voiceStatus').innerHTML = '🔊 Native';
        }
    }, 5000);
});

// =================== FONCTION SYNTHÈSE VOCALE ===================
function speakJarvis(text) {
    // Nettoyer les commandes navigateur du texte à lire
    const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
    
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try {
            responsiveVoice.cancel();
            
            const parameters = {
                pitch: 1,
                rate: 0.95,
                volume: 1,
                onstart: function() {
                    document.getElementById('voiceStatus').innerHTML = '🔊 En cours...';
                },
                onend: function() {
                    document.getElementById('voiceStatus').innerHTML = '🔊 Prête';
                },
                onerror: function(error) {
                    console.error("❌ ResponsiveVoice erreur:", error);
                    fallbackToNativeVoice(cleanText);
                }
            };
            
            responsiveVoice.speak(cleanText, "French Male", parameters);
            return;
            
        } catch (error) {
            console.warn("⚠️ ResponsiveVoice exception:", error);
        }
    }
    
    fallbackToNativeVoice(cleanText);
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
        }
        
        window.speechSynthesis.speak(utterance);
    }, 100);
}

// =================== FONCTION TEST VOCAL ===================
function testVoice() {
    speakJarvis("Bonjour, je suis JARVIS. Tous les systèmes sont opérationnels.");
}

// =================== CONTROLE NAVIGATEUR ===================
function executeBrowserCommand(command) {
    if (!command) return;
    
    const action = command.action;
    const param = command.param;
    
    const browserFrame = document.getElementById('browserFrame');
    const browserControls = document.getElementById('browserControls');
    
    if (action === 'OPEN') {
        // Ouvrir une URL
        browserFrame.src = param;
        browserFrame.classList.add('active');
        browserControls.classList.add('active');
        document.getElementById('browserStatus').innerHTML = '🌐 Page ouverte';
        
    } else if (action === 'SEARCH') {
        // Recherche Google
        const searchUrl = 'https://www.google.com/search?q=' + encodeURIComponent(param);
        browserFrame.src = searchUrl;
        browserFrame.classList.add('active');
        browserControls.classList.add('active');
        document.getElementById('browserStatus').innerHTML = '🔍 Recherche en cours';
        
    } else if (action === 'CLOSE') {
        // Fermer le navigateur
        closeBrowser();
    }
}

function closeBrowser() {
    const browserFrame = document.getElementById('browserFrame');
    const browserControls = document.getElementById('browserControls');
    
    browserFrame.classList.remove('active');
    browserControls.classList.remove('active');
    browserFrame.src = '';
    document.getElementById('browserStatus').innerHTML = '✅ Actif';
}

function returnToJarvis() {
    closeBrowser();
    speakJarvis("Je suis de retour. Comment puis-je vous aider ?");
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

    sendBtn.disabled = true;
    sendBtn.textContent = '⏳ Envoi...';

    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;

    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'msg-user';
    userMsgDiv.textContent = userMessage;
    chatWindow.appendChild(userMsgDiv);

    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'msg-jarvis';
    thinkingDiv.innerHTML = '🤔 JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    chatWindow.appendChild(thinkingDiv);

    chatWindow.scrollTop = chatWindow.scrollHeight;

    const modelNames = {
        'c4ai': 'C4AI Aya Expanse 32B',
        'cosmosrp': 'CosmosRP'
    };
    document.getElementById('currentModel').textContent = modelNames[selectedModel];

    messageInput.value = '';

    try {
        const formData = new FormData();
        formData.append('message', userMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        thinkingDiv.remove();

        const jarvisMsgDiv = document.createElement('div');
        jarvisMsgDiv.className = 'msg-jarvis';
        const typingSpan = document.createElement('span');
        jarvisMsgDiv.appendChild(typingSpan);
        chatWindow.appendChild(jarvisMsgDiv);

        if (data.debug && !data.success) {
            const debugDiv = document.createElement('details');
            debugDiv.style.cssText = 'color:#ff6b6b;font-size:10px;margin-top:10px;';
            debugDiv.innerHTML = `<summary>🔍 Debug Info</summary><pre>${data.debug}</pre>`;
            chatWindow.appendChild(debugDiv);
        }

        // Nettoyer le message des commandes navigateur pour l'affichage
        const displayMessage = data.message.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
        
        typeWriter(displayMessage, typingSpan);

        // Exécuter commande navigateur si présente
        if (data.browserCommand) {
            setTimeout(() => {
                executeBrowserCommand(data.browserCommand);
            }, 1000);
        }

        chatWindow.scrollTop = chatWindow.scrollHeight;

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
        console.error('Erreur:', error);
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = '▶ Envoyer';
        messageInput.focus();
    }
});

// =================== FOCUS AUTOMATIQUE ===================
document.getElementById('messageInput').focus();

console.log('🚀 JARVIS AI avec Contrôle Vocal initialisé !');
</script>

</body>
</html>
