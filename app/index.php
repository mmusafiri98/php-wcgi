<?php
// =====================================================
// JARVIS AI - GIF FIXE EN POSITION
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
<title>JARVIS AI - GIF Fixe</title>
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
}

#chatWindow {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 15px;
    height: 400px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.msg-user {
    background: rgba(0, 234, 255, 0.15);
    padding: 12px 15px;
    border-radius: 15px 15px 5px 15px;
    margin: 10px 0;
    text-align: right;
    max-width: 85%;
    margin-left: auto;
}

.msg-jarvis {
    background: rgba(255, 255, 255, 0.1);
    padding: 12px 15px;
    border-radius: 15px 15px 15px 5px;
    margin: 10px 0;
    max-width: 85%;
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
}

.form-control, .form-select {
    background: rgba(0, 0, 0, 0.6) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--accent) !important;
    border-radius: 10px !important;
    padding: 12px !important;
}

.btn-send {
    background: linear-gradient(135deg, #00eaff, #0088cc);
    border: none;
    color: #000;
    font-weight: 700;
    padding: 12px 30px;
    border-radius: 10px;
}

@media (min-width: 768px) {
    .jarvis-visual { height: 400px; }
    body { padding-top: 420px; }
}

@media (max-width: 576px) {
    .jarvis-visual { height: 250px; }
    body { padding-top: 270px; }
}
</style>
</head>
<body>

<div class="jarvis-visual">
    <img src="jarvis.gif" alt="JARVIS">
</div>

<div class="main-container">
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">💬 JARVIS AI</div>
                <div id="chatWindow">
                    <div class="msg-jarvis">👋 Bonjour, je suis JARVIS.</div>
                </div>
                <form id="chatForm">
                    <input type="text" id="messageInput" class="form-control mb-3" placeholder="Message..." required>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">C4AI Aya Expanse</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="voiceBtn" class="voice-btn">🎤</button>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-send w-100">▶ Envoyer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="panel">
                <div class="panel-header">⚙️ SYSTÈME</div>
                <div style="color: rgba(255,255,255,0.7);">
                    Statut: <span style="color: #8bffcf;">🟢 En ligne</span><br>
                    GIF: <span style="color: #8bffcf;">📌 Position fixe</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>
<script>
let recognition = null;
if ('webkitSpeechRecognition' in window) {
    recognition = new (window.webkitSpeechRecognition || window.SpeechRecognition)();
    recognition.lang = 'fr-FR';
    recognition.onresult = (e) => {
        document.getElementById('messageInput').value = e.results[0][0].transcript;
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    };
}

document.getElementById('voiceBtn').onclick = () => {
    if (recognition) recognition.start();
};

document.getElementById('chatForm').onsubmit = async (e) => {
    e.preventDefault();
    const msg = document.getElementById('messageInput').value.trim();
    if (!msg) return;
    
    const chat = document.getElementById('chatWindow');
    chat.innerHTML += `<div class="msg-user">${msg}</div>`;
    document.getElementById('messageInput').value = '';
    
    const fd = new FormData();
    fd.append('message', msg);
    fd.append('model', 'c4ai');
    fd.append('ajax', 'true');
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.success) {
        const cleanMsg = data.message.replace(/\[BROWSER:[^\]]+\]/g, '');
        chat.innerHTML += `<div class="msg-jarvis">${cleanMsg}</div>`;
        chat.scrollTop = chat.scrollHeight;
        
        if (typeof responsiveVoice !== 'undefined') {
            responsiveVoice.speak(cleanMsg, "French Male");
        }
    }
};
</script>
</body>
</html>
