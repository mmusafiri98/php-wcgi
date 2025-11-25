<?php
// =====================================================
// JARVIS AI - VERSION COMPLETE ET OPTIMISÉE
// Mobile First + Responsive + Voice + Animations
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
                    ["role" => "system", "content" => $JARVIS_SYSTEM_PROMPT],
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
		    ["role" => "system", "content" =>$JARVIS_SYSTEM_PROMPT],
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

/* =================== MICROPHONE BUTTON =================== */
.btn-mic {
    background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
    border: 1px solid rgba(0,234,255,0.12);
    color: var(--accent);
    padding: 10px 12px;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.15s ease;
}

.btn-mic.recording {
    box-shadow: 0 6px 18px rgba(255, 50, 50, 0.18);
    transform: translateY(-2px);
    border-color: rgba(255,80,80,0.9);
    color: #ff8b8b;
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

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">🤖 C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">🌌 CosmosRP</option>
                            </select>
                        </div>

                        <div class="col-6 col-sm-3 d-flex">
                            <!-- Microphone button for Speech-to-Text (STT) -->
                            <button type="button" id="micBtn" class="btn btn-mic w-100" title="Avvia/ferma microfono">
                                🎤 Avvia
                            </button>
                        </div>

                        <div class="col-6 col-sm-2">
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
                    <strong style="color: var(--accent);">ℹ️ Informations :</strong><br>
                    • Interface responsive optimisée<br>
                    • Synthèse vocale intégrée<br>
                    • Animation typing en temps réel<br>
                    • Support mobile & desktop<br>
                    <br>
                    <span id="mobileVoiceNote" style="display: none; color: #ffaa00;">
                        📱 <strong>Sur mobile:</strong> Cliquez sur "Tester la voix" ou invia un message per attivare il suono.
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
let voiceReady = false;
let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

// Fix per mobile: variabile per sbloccare la voce (evita errori se usata prima)
let voiceUnlocked = false;
function unlockVoice() {
    // semplice flag per evitare errori di chiamata su device mobile
    voiceUnlocked = true;
    // alcuni device richiedono un'interazione fisica per sbloccare audio
    try {
        if ('speechSynthesis' in window) {
            // play a silent utterance to unlock in some browsers
            const u = new SpeechSynthesisUtterance('');
            u.volume = 0;
            window.speechSynthesis.speak(u);
        }
    } catch (e) {
        // silent
    }
}

// =================== INITIALISATION RESPONSIVEVOICE ===================
window.addEventListener('load', function() {
    // Attendre que ResponsiveVoice soit chargé
    const checkRV = setInterval(() => {
        if (typeof responsiveVoice !== 'undefined') {
            clearInterval(checkRV);
            
            // Callback quand les voix sont prêtes
            responsiveVoice.OnVoiceReady = function() {
                voiceReady = true;
                const voices = responsiveVoice.getVoices ? responsiveVoice.getVoices() : [];
                const frenchVoices = voices.filter ? voices.filter(v => v.name && v.name.includes('French')) : [];
                
                console.log("✅ ResponsiveVoice prêt");
                console.log("🔊 Voix françaises:", frenchVoices.length);
                
                document.getElementById('voiceStatus').innerHTML = '🔊 Prête (ResponsiveVoice)';
                
                if (isMobile) {
                    document.getElementById('mobileVoiceNote').style.display = 'none';
                }
            };
            
            // Forcer l'initialisation
            try { responsiveVoice.init(); } catch (e) { /* ignore */ }
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
function speakJarvisTextImmediate(textChunk, opts = {}) {
    // speak a small chunk immediately (used during typing)
    // opts: {interrupt: true/false}
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try {
            if (opts.interrupt) responsiveVoice.cancel();
            responsiveVoice.speak(textChunk, "French Female", {
                rate: 0.95,
                pitch: 1,
                volume: 1,
                onstart: function() { document.getElementById('voiceStatus').innerHTML = '🔊 En cours...'; },
                onend: function() { document.getElementById('voiceStatus').innerHTML = '🔊 Prête (ResponsiveVoice)'; },
                onerror: function() { document.getElementById('voiceStatus').innerHTML = '⚠️ Erreur'; }
            });
            return;
        } catch (e) {
            console.warn('ResponsiveVoice speak error', e);
        }
    }
    // fallback native
    try {
        if (!('speechSynthesis' in window)) return;
        const u = new SpeechSynthesisUtterance(textChunk);
        u.lang = 'fr-FR';
        u.rate = 0.95;
        u.pitch = 1.0;
        const voices = window.speechSynthesis.getVoices();
        const french = voices.find(v => v.lang && v.lang.startsWith && v.lang.startsWith('fr'));
        if (french) u.voice = french;
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(u);
    } catch (err) {
        console.warn('native speak error', err);
    }
}

function speakJarvis(text) {
    // full speech after generation (kept for compatibility)
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try { responsiveVoice.cancel(); } catch(e){}
        try {
            responsiveVoice.speak(text, "French Female", {rate:0.95, pitch:1, volume:1});
            return;
        } catch(e) { console.warn(e); }
    }
    if ('speechSynthesis' in window) {
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'fr-FR'; u.rate = 0.95; u.pitch = 1;
        const voices = window.speechSynthesis.getVoices();
        const french = voices.find(v => v.lang && v.lang.startsWith && v.lang.startsWith('fr'));
        if (french) u.voice = french;
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(u);
    }
}

// =================== FONCTION TYPING ANIMATION + VOICE EN TEMPS RÉEL ===================
// This implementation types the text char-by-char AND speaks small chunks in real-time
function typeWriterWithVoice(text, element, options = {}) {
    const charsPerChunk = options.charsPerChunk || 40; // speak every N chars or on punctuation
    const speakOnPunctuation = options.speakOnPunctuation !== undefined ? options.speakOnPunctuation : true;
    let index = 0;
    let buffer = '';
    element.classList.add('typing');

    function shouldSpeakNow(ch) {
        if (speakOnPunctuation && /[\.\!\?\,;:
]/.test(ch)) return true;
        if (buffer.length >= charsPerChunk) return true;
        return false;
    }

    function type() {
        if (index < text.length) {
            const ch = text.charAt(index);
            element.textContent += ch;
            buffer += ch;
            index++;
            element.parentElement.parentElement.scrollTop = element.parentElement.parentElement.scrollHeight;

            if (shouldSpeakNow(ch)) {
                const chunk = buffer.trim();
                if (chunk.length > 0) {
                    // speak the chunk immediately, interrupt previous small chunk to keep sync
                    speakJarvisTextImmediate(chunk, {interrupt: true});
                }
                buffer = '';
            }

            setTimeout(type, 18); // typing speed
        } else {
            // speak remaining buffer if any, but don't interrupt long full speech
            if (buffer.trim().length > 0) {
                speakJarvisTextImmediate(buffer.trim(), {interrupt: false});
            }
            element.classList.remove('typing');
            // After finished typing, also trigger full speak (optional) after short delay
            setTimeout(() => speakJarvis(text), 600);
        }
    }
    type();
}

// =================== SPEECH-TO-TEXT (STT) - WEB SPEECH API ===================
let recognition = null;
let recognizing = false;
const micBtn = document.getElementById('micBtn');
const msgInput = document.getElementById('messageInput');

function initSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
    if (!SpeechRecognition) {
        if (micBtn) { micBtn.disabled = true; micBtn.title = 'Reconnaissance vocale non supportée'; }
        console.warn('SpeechRecognition non supporté par ce navigateur.');
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;
    recognition.continuous = false;

    recognition.onstart = function() {
        recognizing = true;
        if (micBtn) { micBtn.classList.add('recording'); micBtn.textContent = '⏺️ Enregistrement...'; }
        document.getElementById('voiceStatus').textContent = '🎙️ Écoute...';
    };

    recognition.onerror = function(event) {
        console.error('SpeechRecognition error', event);
        recognizing = false;
        if (micBtn) { micBtn.classList.remove('recording'); micBtn.textContent = '🎤 Avvia'; }
        document.getElementById('voiceStatus').textContent = '⚠️ Erreur STT';
    };

    recognition.onend = function() {
        recognizing = false;
        if (micBtn) { micBtn.classList.remove('recording'); micBtn.textContent = '🎤 Avvia'; }
        document.getElementById('voiceStatus').textContent = voiceReady ? '🔊 Prête (ResponsiveVoice)' : '🔊 Native (fallback)';
    };

    let finalTranscript = '';
    recognition.onresult = function(event) {
        let interimTranscript = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
            const res = event.results[i];
            if (res.isFinal) {
                finalTranscript += res[0].transcript + ' ';
            } else {
                interimTranscript += res[0].transcript;
            }
        }
        msgInput.value = (finalTranscript + interimTranscript).trim();
    };
}

if (micBtn) {
    initSpeechRecognition();
    micBtn.addEventListener('click', () => {
        if (isMobile && !voiceUnlocked) unlockVoice();
        if (!recognition) return;
        if (recognizing) {
            recognition.stop();
            recognizing = false;
            micBtn.classList.remove('recording');
            micBtn.textContent = '🎤 Avvia';
        } else {
            try {
                recognition.start();
            } catch (e) {
                console.warn('recognition.start() exception', e);
            }
        }
    });
}

// =================== GESTION DU FORMULAIRE ===================
const chatForm = document.getElementById('chatForm');
const sendBtn = document.getElementById('sendBtn');
const modelSelect = document.getElementById('modelSelect');
const chatWindow = document.getElementById('chatWindow');

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (isMobile && !voiceUnlocked) {
        unlockVoice();
    }

    const userMessage = msgInput.value.trim();
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

    const modelNames = { 'c4ai': 'C4AI Aya Expanse 32B', 'cosmosrp': 'CosmosRP' };
    document.getElementById('currentModel').textContent = modelNames[selectedModel];

    // keep a local copy before clearing input
    const pendingMessage = msgInput.value;
    msgInput.value = '';

    try {
        const formData = new FormData();
        formData.append('message', pendingMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');

        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error('HTTP error ' + response.status);
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

        // Use the enhanced typewriter with live voice
        typeWriterWithVoice(data.message, typingSpan, {charsPerChunk: 45, speakOnPunctuation: true});

        chatWindow.scrollTop = chatWindow.scrollHeight;

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
        console.error('Erreur:', error);
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = '▶ Envoyer';
        msgInput.focus();
    }
});

// =================== FOCUS AUTOMATIQUE ===================
try { document.getElementById('messageInput').focus(); } catch (e) {}

console.log('🚀 JARVIS AI Initialisé avec succès ! (avec STT + voix en temps réel)');

</script>

</body>
</html>


