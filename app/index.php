<?php
// =====================================================
// JARVIS AI 2025 - VERSIONE DEFINITIVA (NO CORS, VOCE LIVE)
// Parla mentre scrive + funziona ovunque
// =====================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');
    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $response = ["success" => false, "message" => "Message vide", "debug" => ""];

    if ($userMessage !== "") {
        if ($model === "cosmosrp") {
            $api_url = "https://api.pawan.krd/cosmosrp/v1/chat/completions";
            $payload = [
                "model" => "cosmosrp",
                "messages" => [
                    ["role" => "system", "content" => "Tu es JARVIS, l'assistant IA ultime génération."],
                    ["role" => "user", "content" => $userMessage]
                ]
            ];
        } else { // c4ai (default)
            $api_url = "https://api.cohere.com/v2/chat";
            $payload = [
                "model" => "c4ai-aya-expanse-32b",
                "messages" => [["role" => "user", "content" => $userMessage]]
            ];
        }

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                $model === "c4ai" ? "Authorization: Bearer Uw540GN865rNyiOs3VMnWhRaYQ97KAfudAHAnXzJ" : ""
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $code >= 400) {
            $response["message"] = "Erreur de connexion à l'IA";
            $response["debug"] = $raw;
        } else {
            $data = json_decode($raw, true);
            if ($model === "cosmosrp" && isset($data["choices"][0]["message"]["content"])) {
                $response["message"] = $data["choices"][0]["message"]["content"];
                $response["success"] = true;
            } elseif ($model === "c4ai" && isset($data["message"]["content"][0]["text"])) {
                $response["message"] = $data["message"]["content"][0]["text"];
                $response["success"] = true;
            } else {
                $response["message"] = "Réponse inattendue";
                $response["debug"] = $raw;
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
<title>JARVIS AI 2025 - Voix Live</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--c:#00eaff;--bg:#020610;--p:rgba(0,255,255,0.06);--b:rgba(0,255,255,0.15)}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--c);font-family:'Orbitron',sans-serif;min-height:100vh}
.main-container{max-width:1400px;margin:auto;padding:15px}
.jarvis-visual{height:300px;border:2px solid var(--b);border-radius:15px;overflow:hidden;box-shadow:0 0 30px rgba(0,234,255,0.4);margin-bottom:20px}
.jarvis-visual img{width:100%;height:100%;object-fit:cover}
.panel{background:var(--p);border:1px solid var(--b);border-radius:15px;padding:20px;backdrop-filter:blur(10px);margin-bottom:20px}
#chatWindow{background:rgba(0,0,0,0.5);border:1px solid var(--b);border-radius:12px;padding:15px;height:420px;overflow-y:auto}
.msg-user{background:rgba(0,234,255,0.15);border:1px solid rgba(0,234,255,0.3);border-radius:15px 15px 5px 15px;padding:12px 16px;margin:10px 0 10px auto;max-width:85%;text-align:right;animation:sr 0.4s}
.msg-jarvis{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:15px 15px 15px 5px;padding:12px 16px;margin:10px 0;max-width:85%;animation:sl 0.4s}
@keyframes sr{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}
@keyframes sl{from{opacity:0;transform:translateX(-30px)}to{opacity:1;transform:none}}
.typing{border-right:3px solid var(--c);animation:blink 1s infinite}
@keyframes blink{50%{border-color:transparent}}
.form-control,.form-select{background:rgba(0,0,0,0.6)!important;border:1px solid var(--b)!important;color:var(--c)!important;border-radius:10px!important}
.btn-send{background:linear-gradient(135deg,#00eaff,#0088cc);border:none;color:#000;font-weight:700;border-radius:10px;padding:12px 30px}
.btn-mic{background:rgba(255,255,255,0.05);border:1px solid var(--b);color:var(--c);border-radius:10px}
.btn-mic.recording{background:#330000;border-color:#ff5555;color:#ff8888;box-shadow:0 0 20px rgba(255,0,0,0.4)}
#voiceStatus{color:#8bffcf}
@media(min-width:768px){#chatWindow{height:520px}.jarvis-visual{height:420px}}
</style>
</head>
<body>
<div class="main-container">
    <div class="jarvis-visual"><img src="jarvis.gif" alt="JARVIS"></div>
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <h3 class="text-center mb-3">JARVIS AI</h3>
                <div id="chatWindow">
                    <div class="msg-jarvis">Bonjour Maître, je suis JARVIS. Comment puis-je vous aider ?</div>
                </div>
                <form id="chatForm">
                    <div class="mb-3"><input type="text" id="messageInput" class="form-control" placeholder="Votre message..." autocomplete="off" required></div>
                    <div class="row g-2 align-items-center">
                        <div class="col-7"><select id="modelSelect" class="form-select">
                            <option value="c4ai">C4AI Aya Expanse 32B</option>
                            <option value="cosmosrp">CosmosRP</option>
                        </select></div>
                        <div class="col-3"><button type="button" id="micBtn" class="btn btn-mic w-100">Mic</button></div>
                        <div class="col-2"><button type="submit" id="sendBtn" class="btn btn-send w-100">Envoyer</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="panel">
                <h5>Système</h5>
                <div class="d-flex justify-content-between py-1"><span>Statut</span><span class="text-success">En ligne</span></div>
                <div class="d-flex justify-content-between py-1"><span>Modèle</span><span id="currentModel">C4AI Aya</span></div>
                <div class="d-flex justify-content-between py-1"><span>Voix</span><span id="voiceStatus">Initialisation...</span></div>
                <div class="d-flex justify-content-between py-1"><span>Messages</span><span id="msgCount">0</span></div>
            </div>
        </div>
    </div>
</div>

<script>
// =================== VARIABILI + SBLOCCO AUDIO ===================
let audioUnlocked = false;
let messageCount = 0;

function unlockAudio() {
    if (audioUnlocked) return;
    const silent = new SpeechSynthesisUtterance("");
    silent.volume = 0;
    speechSynthesis.cancel();
    speechSynthesis.speak(silent);
    audioUnlocked = true;
}
document.body.addEventListener('click', unlockAudio, {once:true});
document.body.addEventListener('touchstart', unlockAudio, {once:true});

// =================== VOCE NATIVA (FUNZIONA SEMPRE, NO CORS) ===================
function speakText(text) {
    if (!text.trim()) return;
    unlockAudio();
    speechSynthesis.cancel();
    const utter = new SpeechSynthesisUtterance(text);
    utter.lang = 'fr-FR';
    utter.rate = 1.05;
    utter.pitch = 1;
    utter.volume = 1;
    
    const voices = speechSynthesis.getVoices();
    const frVoice = voices.find(v => v.lang.startsWith('fr') && v.name.includes('Google')) || 
                    voices.find(v => v.lang.startsWith('fr'));
    if (frVoice) utter.voice = frVoice;
    
    utter.onstart = () => document.getElementById('voiceStatus').textContent = 'Parle...';
    utter.onend = () => document.getElementById('voiceStatus').textContent = 'Prête';
    speechSynthesis.speak(utter);
}

// =================== TYPEWRITER + VOCE PAROLA PER PAROLA (PERFETTO) ===================
function typeAndSpeak(text, element) {
    element.textContent = '';
    element.classList.add('typing');
    let i = 0;
    let currentWord = '';

    const processChar = () => {
        if (i < text.length) {
            const char = text[i];
            element.textContent += char;
            i++;

            if (/[ \n.,;:!?]/g.test(char)) {
                if (currentWord.trim().length > 1) {
                    speakText(currentWord.trim() + ' ');
                }
                currentWord = '';
            } else {
                currentWord += char;
            }

            document.getElementById('chatWindow').scrollTop = 1e9;
            setTimeout(processChar, 16);
        } else {
            if (currentWord.trim()) speakText(currentWord.trim());
            element.classList.remove('typing');
        }
    };
    processChar();
}

// =================== INVIO MESSAGGIO ===================
document.getElementById('chatForm').onsubmit = async e => {
    e.preventDefault();
    unlockAudio();
    const input = document.getElementById('messageInput');
    const msg = input.value.trim();
    if (!msg) return;

    const model = document.getElementById('modelSelect').value;
    document.getElementById('currentModel').textContent = model === 'c4ai' ? 'C4AI Aya' : 'CosmosRP';

    // Messaggio utente
    const userDiv = Object.assign(document.createElement('div'), {className:'msg-user', textContent: msg});
    document.getElementById('chatWindow').appendChild(userDiv);

    // Pensando...
    const thinking = Object.assign(document.createElement('div'), {className:'msg-jarvis', innerHTML: 'JARVIS réfléchit...'});
    document.getElementById('chatWindow').appendChild(thinking);

    document.getElementById('sendBtn').disabled = true;
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;
    input.value = '';

    try {
        const fd = new FormData();
        fd.append('ajax', 'true');
        fd.append('message', msg);
        fd.append('model', model);

        const res = await fetch('', {method:'POST', body:fd});
        const data = await res.json();

        thinking.remove();
        const jarvisDiv = document.createElement('div');
        jarvisDiv.className = 'msg-jarvis';
        const span = document.createElement('span');
        jarvisDiv.appendChild(span);
        document.getElementById('chatWindow').appendChild(jarvisDiv);

        typeAndSpeak(data.message || "Désolé, je n'ai pas compris.", span);

    } catch (err) {
        thinking.textContent = 'Erreur de connexion';
    } finally {
        document.getElementById('sendBtn').disabled = false;
        input.focus();
    }
};

// =================== TEST VOCE ===================
function testVoice() { speakText("Système vocal opérationnel, Monsieur."); }

// Microfono (opzionale)
if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    const rec = new SR();
    rec.lang = 'fr-FR';
    rec.continuous = false;
    rec.interimResults = true;
    document.getElementById('micBtn').onclick = () => {
        unlockAudio();
        rec.start();
        document.getElementById('micBtn').classList.add('recording');
    };
    rec.onresult = e => {
        let text = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            text += e.results[i][0].transcript;
        }
        document.getElementById('messageInput').value = text;
    };
    rec.onend = () => document.getElementById('micBtn').classList.remove('recording');
}

document.getElementById('voiceStatus').textContent = 'Prête (Web Speech API)';
console.log('JARVIS AI 2025 chargé - Voix LIVE activée !');
</script>
</body>
</html>
