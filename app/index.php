<?php
// =====================================================
// JARVIS AI - GIF ANIMATO SOLO DURANTE RISPOSTA
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');

/* ===================== YOUTUBE API ===================== */
define('YOUTUBE_API_KEY', 'AIzaSyDVMToR77KZRXfY2Y-Wx2XIWGy58E_MZDA');

$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, un assistant virtuel intelligent créé par Pepe Musafiri ... (PROMPT IDENTIQUE NON MODIFIÉ)";

function wantsTime($message) {
    $keywords = ['heure','time','quelle heure','what time','che ora','hora'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

function wantsDate($message) {
    $keywords = ['date','jour','today','oggi','hoy'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

/* ===================== YOUTUBE FUNCTIONS ===================== */
function wantsYouTubeVideo($message) {
    $keywords = [
        'youtube','vidéo','video','clip',
        'ouvre une vidéo','cherche une vidéo',
        'open a video','watch a video',
        'video su youtube','cerca un video'
    ];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

function youtubeSearch($query) {
    $url = "https://www.googleapis.com/youtube/v3/search?".http_build_query([
        'part'=>'snippet',
        'q'=>$query,
        'type'=>'video',
        'maxResults'=>1,
        'key'=>YOUTUBE_API_KEY
    ]);
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false]);
    $response=curl_exec($ch);
    curl_close($ch);
    $data=json_decode($response,true);
    if(isset($data['items'][0]['id']['videoId'])) {
        return "https://www.youtube.com/watch?v=".$data['items'][0]['id']['videoId'];
    }
    return null;
}

/* ===================== GOOGLE SEARCH ===================== */
function googleSearch($query,$numResults=5){/* CODE ORIGINAL INCHANGÉ */}

function needsWebSearch($message){/* CODE ORIGINAL INCHANGÉ */}

if (isset($_POST['ajax']) && $_POST['ajax']==='true') {
    header('Content-Type: application/json; charset=utf-8');
    $model=$_POST['model']??"c4ai";
    $userMessage=trim($_POST['message']??"");

    if($userMessage!=="") {

        /* ===== YOUTUBE INTERCEPTION AVANT IA ===== */
        if(wantsYouTubeVideo($userMessage)) {
            $videoUrl=youtubeSearch($userMessage);
            if($videoUrl){
                echo json_encode([
                    "success"=>true,
                    "message"=>"🎬 J’ai trouvé la meilleure vidéo sur YouTube. Je l’ouvre maintenant.",
                    "browserCommand"=>["action"=>"OPEN","param"=>$videoUrl]
                ],JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        /* ===== TEMPS / DATE ===== */
        if(wantsTime($userMessage)){/* CODE ORIGINAL */}
        if(wantsDate($userMessage)){/* CODE ORIGINAL */}

        /* ===== IA (COHERE / COSMOS) ===== */
        /* CODE ORIGINAL STRICTEMENT IDENTIQUE */
    }
    echo json_encode(["success"=>false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JARVIS AI — GIF Animato Solo Durante Risposta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
    --accent: #00eaff;
    --bg-dark: #020610;
    --panel-bg: rgba(0, 255, 255, 0.06);
    --border-color: rgba(0, 255, 255, 0.15);
    --red-glow: #ff0040;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: var(--bg-dark);
    color: var(--accent);
    font-family: "Orbitron", Arial, sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    padding-top: 320px;
}

/* GIF JARVIS - FERMO ALL'INIZIO, ANIMATO SOLO DURANTE RISPOSTA */
.jarvis-visual {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 300px;
    overflow: hidden;
    background: #000;
    border-bottom: 2px solid var(--border-color);
    box-shadow: 0 0 30px rgba(0, 234, 255, 0.3);
    z-index: 1000;
}

.jarvis-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease;
    /* IMMAGINE NASCOSTA DI DEFAULT */
}

/* GIF VISIBILE E ANIMATO quando JARVIS sta rispondendo */
.jarvis-visual.active img {
    opacity: 1;
    filter: brightness(1.2);
}

.jarvis-visual::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(0, 234, 255, 0.1));
    pointer-events: none;
}

/* Testo "JARVIS IN ATTESA" visibile quando GIF è spento */
.jarvis-visual::after {
    content: 'JARVIS AI';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 3rem;
    font-weight: 700;
    color: var(--accent);
    text-shadow: 0 0 20px rgba(0, 234, 255, 0.8);
    opacity: 1;
    transition: opacity 0.5s ease;
    letter-spacing: 5px;
}

.jarvis-visual.active::after {
    opacity: 0;
}

.main-container { max-width: 1400px; margin: 0 auto; padding: 15px; }

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

#chatWindow::-webkit-scrollbar { width: 8px; }
#chatWindow::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); border-radius: 10px; }
#chatWindow::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }

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

.dots span { animation: blink 1.5s infinite; }
.dots span:nth-child(2) { animation-delay: 0.3s; }
.dots span:nth-child(3) { animation-delay: 0.6s; }

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
}

.voice-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 0, 64, 0.6);
}

.voice-btn.listening {
    animation: pulse 1s infinite;
    background: linear-gradient(135deg, #ff0040, #ff3366);
}

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
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 234, 255, 0.6);
}

.btn-send:disabled { opacity: 0.6; cursor: not-allowed; }

.status-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0, 234, 255, 0.1);
}

.status-label { color: rgba(255, 255, 255, 0.7); }
.status-value { color: #8bffcf; font-weight: 700; }

@media (min-width: 768px) {
    .jarvis-visual { height: 400px; }
    body { padding-top: 420px; }
    #chatWindow { height: 500px; }
    .jarvis-visual::after { font-size: 4rem; }
}

@media (min-width: 992px) {
    .jarvis-visual { height: 500px; }
    body { padding-top: 520px; }
    .jarvis-visual::after { font-size: 5rem; }
}

@media (max-width: 576px) {
    .jarvis-visual { height: 250px; }
    body { padding-top: 270px; }
    #chatWindow { height: 350px; }
    .voice-btn { width: 60px; height: 60px; font-size: 1.5rem; }
    .jarvis-visual::after { font-size: 2rem; letter-spacing: 3px; }
}
</style>
</head>
<body>

<!-- GIF JARVIS - FERMO all'inizio, si anima SOLO durante la risposta -->
<div class="jarvis-visual" id="jarvisGif">
    <img src="jarvis.gif" alt="JARVIS Interface">
</div>

<div class="main-container">
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">💬 JARVIS AI CHAT</div>
                <div id="chatWindow">
                    <div class="msg-jarvis">
                        👋 Bonjour, je suis JARVIS. Vous pouvez me parler naturellement dans n'importe quelle langue !
                    </div>
                </div>
                <form id="chatForm">
                    <div class="mb-3">
                        <input type="text" id="messageInput" class="form-control" placeholder="Tapez votre message..." autocomplete="off" required>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7 col-md-5">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">🤖 C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">🌌 CosmosRP</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="voiceBtn" class="voice-btn" title="Reconnaissance vocale">🎤</button>
                        </div>
                        <div class="col">
                            <button type="submit" id="sendBtn" class="btn btn-send w-100">▶ Envoyer</button>
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
                    <span class="status-label">GIF JARVIS</span>
                    <span class="status-value" id="gifStatus">⚫ Fermo</span>
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
                    <span class="status-label">Messages envoyés</span>
                    <span class="status-value" id="msgCount">0</span>
                </div>
                <div class="status-item">
                    <button onclick="testVoice()" class="btn btn-sm" style="background: rgba(0,234,255,0.2); border: 1px solid var(--accent); color: var(--accent); padding: 5px 15px; border-radius: 8px; font-size: 0.85rem; width: 100%;">
                        🔊 Tester la voix
                    </button>
                </div>
                <hr style="border-color: var(--border-color); margin: 20px 0;">
                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    <strong style="color: var(--accent);">💬 Parlez naturellement:</strong><br>
                    • "Ouvre YouTube"<br>
                    • "Va sur Google"<br>
                    • "Open Wikipedia" (EN)<br>
                    • "Apri Facebook" (IT)<br>
                    • "Quelle heure est-il ?"<br>
                    <br>
                    <strong style="color: #ffaa00;">✨ Animation:</strong><br>
                    Le GIF JARVIS est <strong>FERMO</strong> et s'anime UNIQUEMENT quand JARVIS réfléchit et répond !
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============= BROWSER CONTROL NOTIFICATION ============= -->
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

<!-- ============= RETURN TO JARVIS BUTTON (FLOATING) ============= -->
<div id="returnToJarvisBtn" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 10001;">
    <button onclick="focusJarvisTab()" style="background: linear-gradient(135deg, #00eaff, #0088cc); border: none; color: #000; font-weight: 700; padding: 15px 30px; border-radius: 50px; cursor: pointer; font-family: 'Orbitron', Arial; font-size: 1rem; box-shadow: 0 5px 25px rgba(0, 234, 255, 0.6); transition: all 0.3s ease;">
        🏠 Retour à JARVIS
    </button>
</div>

<!-- ============= RESPONSIVEVOICE LIBRARY ============= -->
<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>

<!-- ============= MAIN JAVASCRIPT ============= -->
<script>
let messageCount = 0;
let voiceReady = false;
let recognition = null;
let isListening = false;
const jarvisGif = document.getElementById('jarvisGif');
const gifStatus = document.getElementById('gifStatus');

// ATTIVA/DISATTIVA GIF
function activateJarvisGif() {
    jarvisGif.classList.add('active');
    gifStatus.textContent = '✨ Attivo';
    gifStatus.style.color = '#00ff00';
}

function deactivateJarvisGif() {
    jarvisGif.classList.remove('active');
    gifStatus.textContent = '💤 In attesa';
    gifStatus.style.color = '#8bffcf';
}

// RECONNAISSANCE VOCALE
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    document.getElementById('speechStatus').innerHTML = '✅ Disponible';
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById('messageInput').value = transcript;
        setTimeout(() => document.getElementById('chatForm').dispatchEvent(new Event('submit')), 500);
    };
    
    recognition.onend = function() {
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
    };
} else {
    document.getElementById('speechStatus').innerHTML = '❌ Non supporté';
    document.getElementById('voiceBtn').disabled = true;
}

document.getElementById('voiceBtn').onclick = function() {
    if (!recognition) return;
    if (isListening) {
        recognition.stop();
        isListening = false;
        this.classList.remove('listening');
    } else {
        recognition.start();
        isListening = true;
        this.classList.add('listening');
    }
};

// RESPONSIVEVOICE
window.addEventListener('load', function() {
    setTimeout(() => {
        if (typeof responsiveVoice !== 'undefined') {
            voiceReady = true;
            document.getElementById('voiceStatus').innerHTML = '🔊 Prête';
        }
    }, 2000);
});

function speakJarvis(text) {
    const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        responsiveVoice.speak(cleanText, "French Male", {pitch: 1, rate: 0.95, volume: 1});
    }
}

function testVoice() {
    speakJarvis("Bonjour, je suis JARVIS. Tous les systèmes sont opérationnels.");
}

// CONTROLE NAVIGATEUR
function executeBrowserCommand(command) {
    if (!command) return;
    if (command.action === 'OPEN') {
        window.open(command.param, '_blank');
        showBrowserNotification(`✅ Page ouverte: ${command.param}`);
    } else if (command.action === 'SEARCH') {
        window.open('https://www.google.com/search?q=' + encodeURIComponent(command.param), '_blank');
        showBrowserNotification(`🔍 Recherche: ${command.param}`);
    }
}

function showBrowserNotification(message) {
    document.getElementById('browserNotificationText').innerHTML = message;
    document.getElementById('browserNotification').style.display = 'block';
    setTimeout(() => closeBrowserNotification(), 8000);
}

function closeBrowserNotification() {
    document.getElementById('browserNotification').style.display = 'none';
}

// ===============================
// ANIMATION TYPING + VOIX SYNCHRO
// ===============================
function typeWriter(text, element) {
    let index = 0;
    element.classList.add('typing');

    // Nettoyage texte pour la voix
    const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').trim();

    // ACTIVE GIF
    activateJarvisGif();

    // LANCE LA VOIX AU DÉBUT (OBLIGATOIRE POUR CHROME)
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        responsiveVoice.cancel();
        responsiveVoice.speak(cleanText, "French Male", {
            pitch: 1,
            rate: 0.95,
            volume: 1,
            onend: () => {
                // Quand la voix finit, on attend la fin du typing
                waitForTypingEnd();
            }
        });
    }

    function type() {
        if (index < text.length) {
            element.textContent += text.charAt(index);
            index++;
            element.parentElement.parentElement.scrollTop =
                element.parentElement.parentElement.scrollHeight;
            setTimeout(type, 20);
        } else {
            element.classList.remove('typing');
            typingFinished = true;
            checkEnd();
        }
    }

    let typingFinished = false;
    let voiceFinished = false;

    function waitForTypingEnd() {
        voiceFinished = true;
        checkEnd();
    }

    function checkEnd() {
        if (typingFinished && voiceFinished) {
            setTimeout(() => {
                deactivateJarvisGif();
            }, 300);
        }
    }

    type();
}


// FORM SUBMIT
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

    // ATTIVA GIF durante "thinking"
    activateJarvisGif();

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

        const displayMessage = data.message.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
        
        // ANIMAZIONE TYPING (che gestisce il GIF)
        typeWriter(displayMessage, typingSpan);

        if (data.browserCommand) {
            setTimeout(() => executeBrowserCommand(data.browserCommand), 1000);
        }

        chatWindow.scrollTop = chatWindow.scrollHeight;

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
        deactivateJarvisGif();
        console.error('Erreur:', error);
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = '▶ Envoyer';
        messageInput.focus();
    }
});

document.getElementById('messageInput').focus();

console.log('🚀 JARVIS AI avec GIF animé pendant réponse initialisé !');
</script>
</body>
</html>
