<?php
// =====================================================
// JARVIS AI - GIF FIXE + ANIMATION DACTYLOGRAPHIQUE
// =====================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');

$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, créé par Pepe Musafiri. Tu maîtrises toutes les langues et tous les domaines. Tu peux contrôler le navigateur avec [BROWSER:ACTION:PARAM].";

function wantsTime($msg) {
    return preg_match('/(heure|time|quelle heure)/i', $msg);
}

function wantsDate($msg) {
    return preg_match('/(date|jour|quel jour|aujourd\'hui)/i', $msg);
}

function googleSearch($query, $num = 5) {
    $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'key' => GOOGLE_API_KEY, 'cx' => SEARCH_ENGINE_ID, 'q' => $query, 'num' => $num
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['items'])) {
            $results = [];
            foreach ($data['items'] as $item) {
                $results[] = ['title' => $item['title'] ?? '', 'link' => $item['link'] ?? '', 'snippet' => $item['snippet'] ?? ''];
            }
            return ['success' => true, 'results' => $results];
        }
    }
    return ['success' => false];
}

function needsWebSearch($msg) {
    return preg_match('/(actualité|news|récent|2024|2025|prix|météo|score)/i', $msg);
}

if (isset($_POST['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $response = ["success" => false, "message" => "", "browserCommand" => null];
    
    if ($userMessage) {
        date_default_timezone_set('Europe/Brussels');
        
        if (wantsTime($userMessage)) {
            echo json_encode(["success" => true, "message" => "⏰ Il est " . date("H:i:s")]);
            exit;
        }
        
        if (wantsDate($userMessage)) {
            $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
            echo json_encode(["success" => true, "message" => "📅 " . $jours[date("w")] . " " . date("d/m/Y")]);
            exit;
        }
        
        $searchContext = "";
        if (needsWebSearch($userMessage)) {
            $searchData = googleSearch($userMessage);
            if ($searchData['success']) {
                $searchContext = "\n\nRÉSULTATS GOOGLE:\n";
                foreach ($searchData['results'] as $i => $r) {
                    $searchContext .= "\nSource " . ($i+1) . ": " . $r['title'] . "\n" . $r['snippet'] . "\n";
                }
            }
        }
        
        $enhancedMessage = $userMessage . $searchContext;
        
        if ($model === "c4ai") {
            $ch = curl_init("https://api.cohere.com/v2/chat");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Authorization: Bearer Uw540GN865rNyiOs3VMnWhRaYQ97KAfudAHAnXzJ"],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "model" => "c4ai-aya-expanse-32b",
                    "messages" => [
                        ["role" => "system", "content" => $JARVIS_SYSTEM_PROMPT],
                        ["role" => "user", "content" => $enhancedMessage]
                    ]
                ]),
                CURLOPT_TIMEOUT => 30
            ]);
            
            $raw = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($raw, true);
            
            if (isset($data["message"]["content"][0]["text"])) {
                $response["message"] = $data["message"]["content"][0]["text"];
                $response["success"] = true;
                if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $m)) {
                    $response["browserCommand"] = ["action" => $m[1], "param" => $m[2]];
                }
            } elseif (isset($data["text"])) {
                $response["message"] = $data["text"];
                $response["success"] = true;
            }
        } else if ($model === "cosmosrp") {
            $ch = curl_init("https://api.pawan.krd/cosmosrp/v1/chat/completions");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "model" => "cosmosrp",
                    "messages" => [
                        ["role" => "system", "content" => $JARVIS_SYSTEM_PROMPT],
                        ["role" => "user", "content" => $enhancedMessage]
                    ]
                ]),
                CURLOPT_TIMEOUT => 30
            ]);
            
            $raw = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($raw, true);
            
            if (isset($data["choices"][0]["message"]["content"])) {
                $response["message"] = $data["choices"][0]["message"]["content"];
                $response["success"] = true;
                if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $m)) {
                    $response["browserCommand"] = ["action" => $m[1], "param" => $m[2]];
                }
            }
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JARVIS AI - GIF Fixe + Animation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
    --accent: #00eaff;
    --bg-dark: #020610;
    --panel-bg: rgba(0, 255, 255, 0.06);
    --border-color: rgba(0, 255, 255, 0.15);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: var(--bg-dark);
    color: var(--accent);
    font-family: "Orbitron", Arial, sans-serif;
    min-height: 100vh;
    padding-top: 320px;
}

/* GIF FIXE */
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
    box-shadow: 0 5px 30px rgba(0, 234, 255, 0.3);
    z-index: 1000;
}

.jarvis-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.jarvis-visual::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(0, 234, 255, 0.1));
    pointer-events: none;
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
    max-width: 85%;
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: slideInLeft 0.3s ease;
}

.msg-thinking {
    background: rgba(255, 200, 0, 0.15);
    padding: 12px 15px;
    border-radius: 15px;
    margin: 10px 0;
    max-width: 85%;
    border: 1px solid rgba(255, 200, 0, 0.3);
    font-style: italic;
    color: #ffcc00;
}

/* ANIMATION TYPING */
.typing-cursor {
    display: inline-block;
    width: 2px;
    height: 1em;
    background: var(--accent);
    margin-left: 2px;
    animation: blink 0.8s infinite;
}

@keyframes blink {
    0%, 49% { opacity: 1; }
    50%, 100% { opacity: 0; }
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.dots span {
    animation: dotPulse 1.5s infinite;
}
.dots span:nth-child(2) { animation-delay: 0.3s; }
.dots span:nth-child(3) { animation-delay: 0.6s; }

@keyframes dotPulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
}

.voice-btn {
    background: linear-gradient(135deg, #ff0040, #cc0033);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 15px;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    font-size: 1.8rem;
    cursor: pointer;
    box-shadow: 0 5px 20px rgba(255, 0, 64, 0.4);
    transition: all 0.3s ease;
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
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 234, 255, 0.6);
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

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
}

@media (max-width: 576px) {
    .jarvis-visual { height: 250px; }
    body { padding-top: 270px; }
    #chatWindow { height: 350px; }
    .voice-btn { width: 60px; height: 60px; font-size: 1.5rem; }
}
</style>
</head>
<body>

<div class="jarvis-visual">
    <img src="jarvis.gif" alt="JARVIS Interface">
</div>

<div class="main-container">
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">💬 JARVIS AI CHAT</div>
                <div id="chatWindow">
                    <div class="msg-jarvis">👋 Bonjour, je suis JARVIS. Comment puis-je vous aider ?</div>
                </div>
                <form id="chatForm">
                    <div class="mb-3">
                        <input type="text" id="messageInput" class="form-control" placeholder="Tapez votre message..." required autocomplete="off">
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
                    <span class="status-label">Modèle actuel</span>
                    <span class="status-value" id="currentModel">C4AI Aya Expanse 32B</span>
                </div>
                <div class="status-item">
                    <span class="status-label">GIF JARVIS</span>
                    <span class="status-value">📌 Position fixe</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Animation</span>
                    <span class="status-value">✍️ Dactylographique</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Messages envoyés</span>
                    <span class="status-value" id="msgCount">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>
<script>
let messageCount = 0;
let recognition = null;
let isListening = false;

// RECONNAISSANCE VOCALE
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = false;
    
    recognition.onresult = (e) => {
        const transcript = e.results[0][0].transcript;
        document.getElementById('messageInput').value = transcript;
        setTimeout(() => {
            document.getElementById('chatForm').dispatchEvent(new Event('submit'));
        }, 500);
    };
    
    recognition.onend = () => {
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
    };
    
    recognition.onerror = (event) => {
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
        if (event.error === 'not-allowed') {
            alert('🎤 Permission microphone refusée.');
        }
    };
}

document.getElementById('voiceBtn').onclick = () => {
    if (!recognition) {
        alert('❌ Reconnaissance vocale non supportée.');
        return;
    }
    
    if (isListening) {
        recognition.stop();
    } else {
        try {
            recognition.start();
            isListening = true;
            document.getElementById('voiceBtn').classList.add('listening');
        } catch (error) {
            console.error('Erreur reconnaissance:', error);
        }
    }
};

// FONCTION ANIMATION DACTYLOGRAPHIQUE
function typeWriter(text, element, speed = 30) {
    let index = 0;
    element.innerHTML = '';
    
    const cursor = document.createElement('span');
    cursor.className = 'typing-cursor';
    element.appendChild(cursor);
    
    function type() {
        if (index < text.length) {
            cursor.remove();
            element.textContent += text.charAt(index);
            element.appendChild(cursor);
            index++;
            
            // Scroll automatique
            element.parentElement.parentElement.scrollTop = element.parentElement.parentElement.scrollHeight;
            
            setTimeout(type, speed);
        } else {
            cursor.remove();
            
            // Synthèse vocale après typing
            if (typeof responsiveVoice !== 'undefined') {
                const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
                responsiveVoice.speak(cleanText, "French Male", {
                    pitch: 1,
                    rate: 0.95,
                    volume: 1
                });
            }
        }
    }
    
    type();
}

// SOUMISSION FORMULAIRE
document.getElementById('chatForm').onsubmit = async (e) => {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const modelSelect = document.getElementById('modelSelect');
    const sendBtn = document.getElementById('sendBtn');
    const chatWindow = document.getElementById('chatWindow');
    
    const userMessage = messageInput.value.trim();
    const selectedModel = modelSelect.value;
    
    if (!userMessage) return;
    
    // Désactiver bouton
    sendBtn.disabled = true;
    sendBtn.textContent = '⏳ Envoi...';
    
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;
    
    // Afficher message utilisateur
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'msg-user';
    userMsgDiv.textContent = userMessage;
    chatWindow.appendChild(userMsgDiv);
    
    // Afficher "JARVIS thinking..."
    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'msg-thinking';
    thinkingDiv.innerHTML = '🤔 JARVIS thinking<span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    chatWindow.appendChild(thinkingDiv);
    
    chatWindow.scrollTop = chatWindow.scrollHeight;
    
    // Mettre à jour modèle
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
        
        // Retirer "thinking"
        thinkingDiv.remove();
        
        if (data.success) {
            // Créer div JARVIS avec animation
            const jarvisMsgDiv = document.createElement('div');
            jarvisMsgDiv.className = 'msg-jarvis';
            const typingSpan = document.createElement('span');
            jarvisMsgDiv.appendChild(typingSpan);
            chatWindow.appendChild(jarvisMsgDiv);
            
            // Nettoyer message des commandes navigateur
            const displayMessage = data.message.replace(/\[BROWSER:[^\]]+\]/g, '').trim();
            
            // Lancer animation dactylographique
            typeWriter(displayMessage, typingSpan);
            
            // Exécuter commandes navigateur si présentes
            if (data.browserCommand) {
                setTimeout(() => {
                    executeBrowserCommand(data.browserCommand);
                }, 1000);
            }
        } else {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'msg-jarvis';
            errorDiv.textContent = '❌ Erreur : ' + (data.message || 'Réponse invalide');
            chatWindow.appendChild(errorDiv);
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
};

// COMMANDES NAVIGATEUR
function executeBrowserCommand(command) {
    if (!command) return;
    
    const action = command.action;
    const param = command.param;
    
    if (action === 'OPEN') {
        window.open(param, '_blank');
        alert(`✅ Page ouverte: ${param}`);
    } else if (action === 'SEARCH') {
        const searchUrl = 'https://www.google.com/search?q=' + encodeURIComponent(param);
        window.open(searchUrl, '_blank');
        alert(`🔍 Recherche: ${param}`);
    } else if (action === 'CLOSE') {
        alert('ℹ️ Commande CLOSE reçue');
    }
}

// Focus automatique
document.getElementById('messageInput').focus();

console.log('🚀 JARVIS AI avec animation dactylographique initialisé !');
</script>
</body>
</html>
