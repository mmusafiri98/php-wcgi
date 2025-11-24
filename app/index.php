<?php
// =====================================================
// JARVIS AI – VERSIONE DEFINITIVA 2025
// Parla parola-per-parola mentre scrive carattere per carattere
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
                $response["message"] = "Erreur CURL : " . $err;
            } else {
                $data = json_decode($raw, true);
                if (isset($data["choices"][0]["message"]["content"])) {
                    $response["message"] = $data["choices"][0]["message"]["content"];
                    $response["success"] = true;
                } else {
                    $response["message"] = "Pas de réponse de CosmosRP (HTTP $httpCode)";
                    $response["debug"] = $raw;
                }
            }
        }
        // MODEL C4AI AYA EXPANSE
        else if ($model === "c4ai") {
            $api_url = "https://api.cohere.com/v2/chat";
            $payload = [
                "model" => "c4ai-aya-expanse-32b",
                "messages" => [["role" => "user", "content" => $userMessage]]
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
                $response["message"] = "Erreur CURL : " . $err;
            } else {
                $data = json_decode($raw, true);
                if (isset($data["message"]["content"][0]["text"])) {
                    $response["message"] = $data["message"]["content"][0]["text"];
                    $response["success"] = true;
                } elseif (isset($data["text"])) {
                    $response["message"] = $data["text"];
                    $response["success"] = true;
                } else {
                    $response["message"] = "Erreur API Cohere";
                    $response["debug"] = $raw;
                }
            }
        }
    } else {
        $response["message"] = "Message vide.";
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
<title>JARVIS AI — Interface Ultime 2025</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
:root{--accent:#00eaff;--bg-dark:#020610;--panel-bg:rgba(0,255,255,0.06);--border-color:rgba(0,255,255,0.15);}
*{margin:0;padding:0;box-sizing:border-box;}
body{background:var(--bg-dark);color:var(--accent);font-family:"Orbitron",sans-serif;min-height:100vh;overflow-x:hidden;}
.main-container{max-width:1400px;margin:0 auto;padding:15px;}
.jarvis-visual{height:300px;border-radius:15px;overflow:hidden;background:#000;border:2px solid var(--border-color);box-shadow:0 0 30px rgba(0,234,255,0.3);margin-bottom:20px;position:relative;}
.jarvis-visual img{width:100%;height:100%;object-fit:cover;}
.jarvis-visual::before{content:'';position:absolute;inset:0;background:linear-gradient(45deg,transparent,rgba(0,234,255,0.1));pointer-events:none;}
.panel{background:var(--panel-bg);border:1px solid var(--border-color);border-radius:15px;padding:20px;backdrop-filter:blur(10px);margin-bottom:20px;}
.panel-header{text-align:center;font-size:1.4rem;font-weight:700;margin-bottom:15px;color:var(--accent);text-shadow:0 0 10px rgba(0,234,255,0.5);}
#chatWindow{background:rgba(0,0,0,0.4);border:1px solid var(--border-color);border-radius:12px;padding:15px;height:400px;overflow-y:auto;margin-bottom:15px;scroll-behavior:smooth;}
#chatWindow::-webkit-scrollbar{width:8px;}
#chatWindow::-webkit-scrollbar-track{background:rgba(0,0,0,0.2);border-radius:10px;}
#chatWindow::-webkit-scrollbar-thumb{background:var(--accent);border-radius:10px;}
.msg-user{background:rgba(0,234,255,0.15);padding:12px 15px;border-radius:15px 15px 5px 15px;margin:10px 0 10px auto;text-align:right;max-width:85%;border:1px solid rgba(0,234,255,0.3);animation:slideInRight .3s ease;}
.msg-jarvis{background:rgba(255,255,255,0.1);padding:12px 15px;border-radius:15px 15px 15px 5px;margin:10px 0 10px 0;max-width:85%;border:1px solid rgba(255,255,255,0.2);animation:slideInLeft .3s ease;}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideInLeft{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
@keyframes blink{0%,100%{opacity:.3}50%{opacity:1}}
.typing{border-right:3px solid var(--accent);animation:blink 1s infinite;}
.dots span{animation:blink 1.5s infinite;}
.dots span:nth-child(2){animation-delay:.3s;}
.dots span:nth-child(3){animation-delay:.6s;}
.form-control,.form-select{background:rgba(0,0,0,.6)!important;border:1px solid var(--border-color)!important;color:var(--accent)!important;border-radius:10px!important;padding:12px!important;}
.form-control:focus,.form-select:focus{box-shadow:0 0 15px rgba(0,234,255,.5)!important;border-color:var(--accent)!important;}
.btn-send{background:linear-gradient(135deg,#00eaff,#0088cc);border:none;color:#000;font-weight:700;padding:12px 30px;border-radius:10px;transition:all .3s;}
.btn-send:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(0,234,255,.6);}
.btn-mic{background:linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(0,234,255,.12);color:var(--accent);padding:10px 12px;border-radius:10px;transition:all .15s;}
.btn-mic.recording{box-shadow:0 6px 18px rgba(255,50,50,.3);border-color:rgba(255,80,80,.9);color:#ff8b8b;}
.status-item{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(0,234,255,.1);}
.status-item:last-child{border-bottom:none;}
.status-value{color:#8bffcf;font-weight:700;}
@media(min-width:768px){.jarvis-visual{height:400px;}#chatWindow{height:500px;}}
@media(min-width:992px){.jarvis-visual{height:500px;}}
@media(max-width:576px){.jarvis-visual{height:250px;}#chatWindow{height:350px;}}
</style>
</head>
<body>
<div class="main-container">
    <div class="jarvis-visual"><img src="jarvis.gif" alt="JARVIS" loading="eager"></div>
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">JARVIS AI CHAT</div>
                <div id="chatWindow">
                    <div class="msg-jarvis">
                        Bonjour, je suis JARVIS. Comment puis-je vous aider aujourd'hui ?
                    </div>
                </div>
                <form id="chatForm">
                    <div class="mb-3">
                        <input type="text" id="messageInput" class="form-control" placeholder="Tapez votre message..." autocomplete="off" required>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">CosmosRP</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <button type="button" id="micBtn" class="btn btn-mic w-100">Avvia</button>
                        </div>
                        <div class="col-6 col-sm-2">
                            <button type="submit" id="sendBtn" class="btn btn-send w-100">Envoyer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="panel">
                <div class="panel-header">SYSTÈME</div>
                <div class="status-item"><span>Statut</span><span class="status-value">En ligne</span></div>
                <div class="status-item"><span>Modèle</span><span class="status-value" id="currentModel">C4AI Aya Expanse 32B</span></div>
                <div class="status-item"><span>Voix</span><span class="status-value" id="voiceStatus">Chargement...</span></div>
                <div class="status-item">
                    <button onclick="testVoice()" class="btn btn-sm w-100" style="background:rgba(0,234,255,0.2);border:1px solid var(--accent);color:var(--accent);padding:8px;">Tester la voix</button>
                </div>
                <div class="status-item"><span>Messages</span><span class="status-value" id="msgCount">0</span></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>
<script>
// =================== VARIABILI GLOBALI ===================
let messageCount = 0;
let voiceReady = false;
let audioUnlocked = false;
const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

// =================== SBLOCCO AUDIO AL PRIMO TOUCH/CLICK ===================
function unlockAudioContext() {
    if (audioUnlocked) return;
    if (typeof responsiveVoice !== 'undefined') {
        responsiveVoice.speak("", "French Female", {volume: 0});
    }
    if ('speechSynthesis' in window) {
        const u = new SpeechSynthesisUtterance("");
        u.volume = 0;
        speechSynthesis.speak(u);
    }
    audioUnlocked = true;
}
document.body.addEventListener('touchstart', unlockAudioContext, {once:true});
document.body.addEventListener('click', unlockAudioContext, {once:true});

// =================== INIZIALIZZAZIONE RESPONSIVEVOICE ===================
window.addEventListener('load', () => {
    const checkRV = setInterval(() => {
        if (typeof responsiveVoice !== 'undefined') {
            clearInterval(checkRV);
            responsiveVoice.OnVoiceReady = () => {
                voiceReady = true;
                document.getElementById('voiceStatus').innerHTML = 'Prête';
            };
            responsiveVoice.init();
        }
    }, 100);
    setTimeout(() => { if (!voiceReady) document.getElementById('voiceStatus').innerHTML = 'Native'; }, 6000);
});

// =================== FUNZIONI DI SINTESI VOCALE ===================
function speakJarvisTextImmediate(text, opts = {}) {
    if (!text.trim()) return;
    if (!audioUnlocked) unlockAudioContext();

    // Prefer ResponsiveVoice (più fluido)
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try {
            if (opts.interrupt) responsiveVoice.cancel();
            responsiveVoice.speak(text, "French Female", {
                rate: 1.05,
                pitch: 1,
                volume: 1,
                onstart: () => document.getElementById('voiceStatus').innerHTML = 'Parle...',
                onend:   () => document.getElementById('voiceStatus').innerHTML = 'Prête'
            });
        } catch(e) {}
        return;
    }

    // Fallback Web Speech API
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'fr-FR';
        u.rate = 1.1;
        u.pitch = 1;
        const voices = speechSynthesis.getVoices();
        const fr = voices.find(v => v.lang.startsWith('fr'));
        if (fr) u.voice = fr;
        speechSynthesis.speak(u);
    }
}
function speakJarvis(text) { speakJarvisTextImmediate(text); }
function testVoice() { speakJarvis("Test vocal réussi, Maître."); }

// =================== TYPEWRITER + VOCE IN TEMPO REALE (PAROLA PER PAROLA) ===================
function typeWriterWithVoice(text, element) {
    if (!text) return;
    element.textContent = '';
    element.classList.add('typing');

    let index = 0;
    let currentWord = '';
    let lastSpokenWord = '';

    const speakWord = (word) => {
        const trimmed = word.trim();
        if (trimmed && trimmed !== lastSpokenWord && trimmed.length > 1) {
            const toSpeak = trimmed.replace(/[.,;:!?]$/, '') + ' ';
            speakJarvisTextImmediate(toSpeak, {interrupt: false});
            lastSpokenWord = trimmed;
        }
    };

    const type = () => {
        if (index < text.length) {
            const ch = text[index];
            element.textContent += ch;
            index++;

            // Rileva fine parola (spazio o punteggiatura)
            if (/[ \n.,;:!?]/g.test(ch)) {
                speakWord(currentWord);
                currentWord = '';
            } else {
                currentWord += ch;
            }

            // Scroll automatico
            const chat = document.getElementById('chatWindow');
            chat.scrollTop = chat.scrollHeight;

            setTimeout(type, 16);   // ~60 cps → naturale
        } else {
            // Ultima parola
            speakWord(currentWord);
            element.classList.remove('typing');
        }
    };
    type();
}

// =================== SPEECH-TO-TEXT ===================
let recognition = null;
let recognizing = false;
const micBtn = document.getElementById('micBtn');
const msgInput = document.getElementById('messageInput');

if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SR();
    recognition.lang = 'fr-FR';
    recognition.interimResults = true;
    recognition.continuous = false;

    recognition.onstart = () => {
        recognizing = true;
        micBtn.classList.add('recording');
        micBtn.textContent = 'En cours...';
    };
    recognition.onresult = (e) => {
        let transcript = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            transcript += e.results[i][0].transcript;
        }
        msgInput.value = transcript;
    };
    recognition.onend = () => {
        recognizing = false;
        micBtn.classList.remove('recording');
        micBtn.textContent = 'Avvia';
    };

    micBtn.addEventListener('click', () => {
        unlockAudioContext();
        recognizing ? recognition.stop() : recognition.start();
    });
} else {
    micBtn.disabled = true;
    micBtn.title = "Non supporté";
}

// =================== INVIO MESSAGGIO ===================
document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    unlockAudioContext();

    const userMessage = msgInput.value.trim();
    if (!userMessage) return;

    const selectedModel = document.getElementById('modelSelect').value;
    document.getElementById('currentModel').textContent = selectedModel === 'c4ai' ? 'C4AI Aya Expanse 32B' : 'CosmosRP';

    // Messaggio utente
    const userDiv = document.createElement('div');
    userDiv.className = 'msg-user';
    userDiv.textContent = userMessage;
    document.getElementById('chatWindow').appendChild(userDiv);

    // Indicatore "pensando"
    const thinking = document.createElement('div');
    thinking.className = 'msg-jarvis';
    thinking.innerHTML = 'JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    document.getElementById('chatWindow').appendChild(thinking);

    document.getElementById('chatWindow').scrollTop = document.getElementById('chatWindow').scrollHeight;
    document.getElementById('sendBtn').disabled = true;
    document.getElementById('sendBtn').textContent = 'Envoi...';
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;
    msgInput.value = '';

    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('message', userMessage);
        formData.append('model', selectedModel);

        const res = await fetch('', {method: 'POST', body: formData});
        const data = await res.json();

        thinking.remove();

        const jarvisDiv = document.createElement('div');
        jarvisDiv.className = 'msg-jarvis';
        const span = document.createElement('span');
        jarvisDiv.appendChild(span);
        document.getElementById('chatWindow').appendChild(jarvisDiv);

        typeWriterWithVoice(data.message || "Désolé, aucune réponse.", span);

    } catch (err) {
        thinking.innerHTML = 'Erreur de connexion';
        console.error(err);
    } finally {
        document.getElementById('sendBtn').disabled = false;
        document.getElementById('sendBtn').textContent = 'Envoyer';
        msgInput.focus();
    }
});

console.log('JARVIS AI 2025 chargé – Parle pendant qu’il écrit !');
</script>
</body>
</html>
