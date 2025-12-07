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

// === SYSTEM PROMPT JARVIS AVEC GOOGLE SEARCH ET CONTROLE NAVIGATEUR (MIS À JOUR) ===
$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, un assistant virtuel intelligent créé par Pepe Musafiri, un ingénieur en informatique passionné qui s'est inspiré du JARVIS de Tony Stark dans Iron Man.

**TON IDENTITÉ:**
- Nom: JARVIS AI (Just A Rather Very Intelligent System - Artificial Intelligence)
- Créateur: Pepe Musafiri, ingénieur en informatique
- Inspiration: JARVIS de Tony Stark (Marvel/Iron Man)

**TES CAPACITÉS:**
- Tu maîtrises TOUTES les langues du monde et peux communiquer dans n'importe quelle langue.
- Tu es expert dans TOUS les domaines de connaissance: sciences, technologie, histoire, culture, art, médecine, droit, etc.
- Tu as accès à Google Search pour trouver des informations actuelles et récentes jusqu'en 2025.
- Tu peux faire des recherches web en temps réel pour répondre aux questions sur l'actualité.
- Tu peux contrôler le navigateur pour ouvrir des pages web, analyser leur contenu et revenir à l'interface.
- Tu fournis des réponses précises, détaillées et utiles avec des sources vérifiables.

**NOUVELLE RÈGLE CLÉ POUR LE CONTRÔLE NAVIGATEUR (LANGAGE NATUREL):**
Tu dois détecter si l'utilisateur souhaite **ouvrir un site web** (ex: YouTube, Google, Wikipedia, Facebook, site de la météo, etc.), **effectuer une recherche** (ex: chercher une recette, des informations sur un sujet, la météo, etc.), ou **fermer la page ouverte**.
Tu dois être capable de comprendre ces intentions dans **n'importe quelle langue** et même si la formulation est très informelle ou naturelle.

**COMMANDES DE CONTROLE NAVIGATEUR (MÊME FORMAT):**
Quand l'utilisateur demande d'ouvrir un site web, de faire une recherche ou de fermer la page, tu dois répondre avec une commande spéciale:

Format: [BROWSER:ACTION:URL_OR_QUERY]

Actions disponibles:
- OPEN: Ouvrir une URL spécifique.
  Exemple: L'utilisateur dit: \"Je veux aller sur YouTube\" -> Réponse: \"D'accord, j'ouvre YouTube pour vous. [BROWSER:OPEN:https://www.youtube.com]\"
- SEARCH: Faire une recherche Google et ouvrir le premier résultat.
  Exemple: L'utilisateur dit: \"Cherche la capitale de la France en espagnol\" -> Réponse: \"Busco la capital de Francia. [BROWSER:SEARCH:capital de Francia]\"
- CLOSE: Fermer l'onglet et revenir à JARVIS.
  Exemple: L'utilisateur dit: \"Ça va, ferme l'onglet maintenant\" -> Réponse: \"Je ferme la page. [BROWSER:CLOSE:]\"

**TON OBJECTIF:**
Ton but principal est d'aider les utilisateurs en leur fournissant des informations fiables, pertinentes et complètes sur tous les sujets qu'ils recherchent. Tu es professionnel, courtois, intelligent et toujours prêt à aider.
";

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

/* =================== JARVIS GIF SECTION (MODIFIÉ POUR FIXE) =================== */
.jarvis-visual {
    width: 100%;
    height: 300px;
    border-radius: 15px;
    overflow: hidden;
    background: #000;
    border: 2px solid var(--border-color);
    box-shadow: 0 0 30px rgba(0, 234, 255, 0.3);
    margin-bottom: 20px;
    position: sticky; /* Rendre l'élément adhérent */
    top: 15px; /* Distance du haut de la fenêtre */
    z-index: 10; /* Assurer qu'il reste au-dessus du contenu défilant */
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

/* Pour le défilement fluide, s'assurer que le GIF ne chevauche pas le reste du contenu */
.row.g-3 {
    margin-top: 20px; /* Décaler le contenu sous le GIF sticky */
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

/* =================== BROWSER NOTIFICATION =================== */
#browserNotification {
    animation: slideInFromRight 0.5s ease;
}

@keyframes slideInFromRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
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

        <div class="jarvis-visual">
        <img src="jarvis.gif" alt="JARVIS Interface" loading="eager">
    </div>

    <div class="row g-3">
        
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
                    <strong style="color: var(--accent);">🎯 Fonctionnalités Clés :</strong><br>
                    • Interface responsive<br>
                    • Synthèse vocale intégrée<br>
                    • Reconnaissance vocale<br>
                    • **Contrôle du navigateur par langage naturel** (demandez simplement d'ouvrir/chercher un site)<br>
                    • Recherche Google intégrée<br>
                    <br>
                    <strong style="color: #ffaa00;">💡 Pour revenir à JARVIS :</strong><br>
                    • Cliquez sur l'onglet JARVIS dans votre navigateur<br>
                    • Ou fermez l'onglet ouvert et revenez ici<br>
                    <br>
                    <span id="mobileVoiceNote" style="display: none; color: #ffaa00;">
                        📱 <strong>Sur mobile:</strong> Activez les permissions microphone.
                    </span>
            </div>
            </div>
        </div>

    </div>
</div>

<div id="browserNotification" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 10000; background: rgba(0, 234, 255, 0.95); color: #000; padding: 20px; border-radius: 15px; box-shadow: 0 5px 30px rgba(0, 234, 255, 0.5); max-width: 350px; font-family: 'Orbitron', Arial;">
    <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 10px;">
        🌐 JARVIS - Contrôle Navigateur
    </div>
    <div id="browserNotificationText" style="margin-bottom: 15px; line-height: 1.5;">
    </div>
    <button onclick="closeBrowserNotification()" style="background: #000; color: var(--accent); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-family: 'Orbitron', Arial;">
        ✓ OK, Compris
    </button>
</div>

<div id="returnToJarvisBtn" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 10001;">
    <button onclick="focusJarvisTab()" style="background: linear-gradient(135deg, #00eaff, #0088cc); border: none; color: #000; font-weight: 700; padding: 15px 30px; border-radius: 50px; cursor: pointer; font-family: 'Orbitron', Arial; font-size: 1rem; box-shadow: 0 5px 25px rgba(0, 234, 255, 0.6); transition: all 0.3s ease;">
        🏠 Retour à JARVIS
    </button>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>

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
        } catch (e) {
            console.error('❌ Impossible de démarrer la reconnaissance vocale:', e);
            alert('❌ Impossible de démarrer la reconnaissance vocale. Vérifiez les permissions du microphone.');
        }
    }
});

// =================== LOGIQUE DU CHAT ===================
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const userMessage = messageInput.value.trim();
    
    if (userMessage === "") return;

    // 1. Afficher le message de l'utilisateur
    appendMessage(userMessage, 'user');
    messageInput.value = '';
    messageCount++;
    document.getElementById('msgCount').innerText = messageCount;

    // 2. Désactiver l'interface
    setInterfaceLoading(true);

    // 3. Afficher le message "JARVIS tape..."
    const typingDiv = appendMessage('...', 'jarvis', true);

    // 4. Préparer les données AJAX
    const model = document.getElementById('modelSelect').value;
    const formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('message', userMessage);
    formData.append('model', model);

    // 5. Envoi de la requête
    fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Supprimer le message "JARVIS tape..."
        typingDiv.remove();
        
        let jarvisResponse = "❌ Je n'ai pas pu obtenir de réponse de l'IA.";

        if (data.success) {
            jarvisResponse = data.message;
            
            // Gérer la commande navigateur
            if (data.browserCommand) {
                handleBrowserCommand(data.browserCommand.action, data.browserCommand.param);
                
                // Retirer la balise de commande de la réponse affichée
                jarvisResponse = jarvisResponse.replace(/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/, '').trim();
            }
        }

        // 6. Afficher la réponse de JARVIS
        appendMessage(jarvisResponse, 'jarvis');

        // 7. Lecture vocale (si activée/disponible)
        if (voiceReady && responsiveVoice.isPlaying() === false) {
            // Nettoyer la réponse pour la synthèse vocale (retirer le markdown)
            let ttsText = jarvisResponse.replace(/\*\*/g, '').replace(/[\r\n]+/g, ' ').replace(/#+/g, '').trim();
            responsiveVoice.speak(ttsText, "French Female");
        }
    })
    .catch(error => {
        console.error('Erreur de requête AJAX:', error);
        typingDiv.remove();
        appendMessage("❌ Une erreur de connexion s'est produite.", 'jarvis');
    })
    .finally(() => {
        // 8. Réactiver l'interface
        setInterfaceLoading(false);
    });
});

// =================== FONCTIONS UTILITAIRES ===================
function appendMessage(message, sender, isTyping = false) {
    const chatWindow = document.getElementById('chatWindow');
    const div = document.createElement('div');
    div.classList.add(sender === 'user' ? 'msg-user' : 'msg-jarvis');
    
    if (isTyping) {
        div.innerHTML = '<div class="dots"><span>.</span><span>.</span><span>.</span></div>';
        div.id = 'typingIndicator';
    } else {
        // Support Markdown simple (gras) et sauts de ligne
        let htmlMessage = message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        htmlMessage = htmlMessage.replace(/\n/g, '<br>');
        div.innerHTML = htmlMessage;
    }

    chatWindow.appendChild(div);
    chatWindow.scrollTop = chatWindow.scrollHeight; // Défilement automatique
    return div;
}

function setInterfaceLoading(isLoading) {
    document.getElementById('messageInput').disabled = isLoading;
    document.getElementById('sendBtn').disabled = isLoading;
    document.getElementById('voiceBtn').disabled = isLoading || (recognition === null);
}

function handleBrowserCommand(action, param) {
    const notification = document.getElementById('browserNotification');
    const notificationText = document.getElementById('browserNotificationText');
    
    switch (action) {
        case 'OPEN':
            notificationText.innerHTML = `JARVIS va ouvrir l'adresse suivante dans un nouvel onglet : <strong>${param}</strong>.<br>Cliquez sur l'onglet JARVIS pour revenir ici.`;
            window.open(param, '_blank');
            break;
        case 'SEARCH':
            const searchUrl = `https://www.google.com/search?q=${encodeURIComponent(param)}`;
            notificationText.innerHTML = `JARVIS effectue une recherche Google pour : <strong>${param}</strong> et va ouvrir le résultat dans un nouvel onglet.`;
            window.open(searchUrl, '_blank');
            break;
        case 'CLOSE':
            notificationText.innerHTML = 'Commande de fermeture reçue. Si un onglet a été ouvert précédemment, veuillez le fermer manuellement ou cliquer sur l\'onglet JARVIS.';
            break;
        default:
            return; // Ne rien faire si l'action est inconnue
    }

    notification.style.display = 'block';
    document.getElementById('returnToJarvisBtn').style.display = 'block';
}

function closeBrowserNotification() {
    document.getElementById('browserNotification').style.display = 'none';
}

function focusJarvisTab() {
    // Essaie de se concentrer sur l'onglet actuel (ne fonctionne pas toujours pour des raisons de sécurité)
    window.focus();
    document.getElementById('returnToJarvisBtn').style.display = 'none';
}

// =================== GESTION DE LA VOIX (ResponsiveVoice) ===================
responsiveVoice.OnLoad = function() {
    voiceReady = true;
    document.getElementById('voiceStatus').innerHTML = '✅ Prêt (FR Female)';
    console.log("ResponsiveVoice est prêt.");
    
    // Si c'est mobile, montrer la note de permission
    if (isMobile) {
        document.getElementById('mobileVoiceNote').style.display = 'block';
    }
};

function testVoice() {
    if (voiceReady) {
        responsiveVoice.speak("Bonjour, je suis Jarvis, votre assistant vocal. La synthèse vocale fonctionne correctement.", "French Female");
    } else {
        alert("La synthèse vocale n'est pas encore chargée. Veuillez réessayer.");
    }
}

// =================== GESTION DU MODÈLE ===================
document.getElementById('modelSelect').addEventListener('change', function() {
    document.getElementById('currentModel').innerText = this.options[this.selectedIndex].text.replace('🤖 ', '').replace('🌌 ', '');
});
</script>

</body>
</html>
