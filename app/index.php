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
                    console.log("🔊 ResponsiveVoice: JARVIS parle");
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
            v.lang === 'fr-FR' || (v.lang && v.lang.startsWith && v.lang.startsWith('fr'))
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
            speakJarvis("Bonjour, je suis JARVIS. La synthèse vocale fonctionne correctement.");
        }, 200);
    } else {
        speakJarvis("Bonjour, je suis JARVIS. La synthèse vocale fonctionne correctement.");
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

// =================== SPEECH-TO-TEXT (STT) - WEB SPEECH API ===================
// This adds a microphone button that fills the input with recognized speech (fr-FR).
let recognition = null;
let recognizing = false;
const micBtn = document.getElementById('micBtn');
const messageInput = document.getElementById('messageInput');

function initSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
    if (!SpeechRecognition) {
        micBtn.disabled = true;
        micBtn.title = 'Reconnaissance vocale non supportée';
        console.warn('SpeechRecognition non supporté par ce navigateur.');
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;
    recognition.continuous = false; // better for short commands/messages

    recognition.onstart = function() {
        recognizing = true;
        micBtn.classList.add('recording');
        micBtn.textContent = '⏺️ Enregistrement...';
        document.getElementById('voiceStatus').textContent = '🎙️ Écoute...';
    };

    recognition.onerror = function(event) {
        console.error('SpeechRecognition error', event);
        recognizing = false;
        micBtn.classList.remove('recording');
        micBtn.textContent = '🎤 Avvia';
        document.getElementById('voiceStatus').textContent = '⚠️ Erreur STT';
    };

    recognition.onend = function() {
        recognizing = false;
        micBtn.classList.remove('recording');
        micBtn.textContent = '🎤 Avvia';
        document.getElementById('voiceStatus').textContent = voiceReady ? '🔊 Prête (ResponsiveVoice)' : '🔊 Native (fallback)';
    };

    let finalTranscript = '';

    recognition.onresult = function(event) {
        let interimTranscript = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
            const res = event.results[i];
            if (res.isFinal) {
                finalTranscript += res[0].transcript;
            } else {
                interimTranscript += res[0].transcript;
            }
        }

        // show interim + final in input (but don't submit automatically)
        messageInput.value = (finalTranscript + ' ' + interimTranscript).trim();
    };

    // If the user wants automatic submit on final result, uncomment below.
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
        messageInput.value = (finalTranscript + interimTranscript).trim();
    };
}

if (micBtn) {
    initSpeechRecognition();
    micBtn.addEventListener('click', () => {
        // ensure voice unlocked on mobile when user interacts
        if (isMobile && !voiceUnlocked) {
            unlockVoice();
        }

        if (!recognition) {
            return;
        }
        if (recognizing) {
            recognition.stop();
            recognizing = false;
            micBtn.classList.remove('recording');
            micBtn.textContent = '🎤 Avvia';
        } else {
            // start recognition
            finalTranscript = '';
            try {
                recognition.start();
            } catch (e) {
                // Some browsers throw if start() called twice quickly - handle gracefully
                console.warn('recognition.start() exception', e);
            }
        }
    });
}

// =================== GESTION DU FORMULAIRE ===================
// IMPORTANT: use a stable endpoint for fetch so mobile devices don't fail.
// Using PHP_SELF ensures the POST target is the executing script.
const chatForm = document.getElementById('chatForm');
const sendBtn = document.getElementById('sendBtn');
const modelSelect = document.getElementById('modelSelect');
const chatWindow = document.getElementById('chatWindow');

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // DÉVERROUILLAGE VOCAL sur mobile au premier clic
    if (isMobile && !voiceUnlocked) {
        unlockVoice();
    }

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

    // Vider l'input (UX: keep a copy if you want)
    messageInput.value = '';

    try {

        // Envoyer la requête AJAX
        const formData = new FormData();
        formData.append('message', userMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');

        // Use stable target: current script file (PHP_SELF)
        const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        // Fail early if not ok
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }

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

        // Animation typing — same behavior as before
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
try { document.getElementById('messageInput').focus(); } catch (e) {}

// Helpful console banner
console.log('🚀 JARVIS AI Initialisé avec succès ! (avec fix mobile + STT)');

</script>

</body>
</html>
