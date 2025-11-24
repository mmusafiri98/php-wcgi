<?php
// =====================================================
// JARVIS AI - VERSION COMPLETE ET OPTIMISÉE (GIF FIXÉ)
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Détection automatique du chemin du GIF
$imageUrl = "/jarvis.gif"; // Forcer la racine du site pour éviter les erreurs

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

        // MODEL C4AI AYA EXPANSE 32B
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
body {
    background: var(--bg-dark);
    color: var(--accent);
    font-family: "Orbitron", Arial, sans-serif;
}

/* =================== JARVIS GIF =================== */
.jarvis-visual {
    width: 100%;
    height: 450px;
    border-radius: 20px;
    overflow: hidden;
    background: #000;
    border: 3px solid var(--accent);
    box-shadow: 0 0 40px rgba(0, 234, 255, 0.4);
    margin-bottom: 25px;
}

.jarvis-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* CHAT PANELS */
#chatWindow {
    background: rgba(0,0,0,0.5);
    padding: 15px;
    border-radius: 12px;
    height: 420px;
    overflow-y: auto;
}

.msg-user {
    background: rgba(0,234,255,0.2);
    margin: 10px 0;
    padding: 10px 15px;
    border-radius: 15px;
    text-align: right;
}

.msg-jarvis {
    background: rgba(255,255,255,0.1);
    margin: 10px 0;
    padding: 10px 15px;
    border-radius: 15px;
    text-align: left;
}

.typing {
    border-right: 3px solid var(--accent);
    animation: blink 0.7s infinite;
}

@keyframes blink {
    50% { opacity: 0; }
}
</style>
</head>
<body>
<div class="container py-3">

    <!-- ============= JARVIS GIF (FULL WIDTH) ============= -->
    <div class="jarvis-visual">
        <img src="<?php echo $imageUrl; ?>" alt="JARVIS Interface">
    </div>

    <div class="row g-3">

        <div class="col-12 col-lg-8">
            <div class="panel p-3">
                <h3 class="text-center mb-3">💬 JARVIS AI</h3>

                <div id="chatWindow">
                    <div class="msg-jarvis">👋 Bonjour, je suis JARVIS.</div>
                </div>

                <form id="chatForm" class="mt-3">
                    <input id="messageInput" class="form-control mb-2" placeholder="Message...">

                    <div class="d-flex gap-2">
                        <select id="modelSelect" class="form-select">
                            <option value="c4ai">C4AI Aya Expanse 32B</option>
                            <option value="cosmosrp">CosmosRP</option>
                        </select>
                        <button class="btn btn-info w-50" id="sendBtn">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="panel p-3">
                <h4 class="text-center">⚙️ Système</h4>
                <p>Status : <b style="color:#8bffcf">En ligne</b></p>
                <p>Modèle : <span id="currentModel">c4ai</span></p>
                <p>Messages envoyés : <span id="msgCount">0</span></p>
            </div>
        </div>

    </div>
</div>

<script>
function typeWriter(text, element){
    let i=0;
    element.classList.add('typing');
    function type(){
        if(i<text.length){
            element.textContent += text.charAt(i);
            i++;
            setTimeout(type,18);
        } else {
            element.classList.remove('typing');
            speak(text);
        }
    }
    type();
}

function speak(text){
    const msg=new SpeechSynthesisUtterance(text);
    msg.lang='fr-FR';
    msg.rate=1;
    speechSynthesis.speak(msg);
}

document.getElementById('chatForm').addEventListener('submit', async(e)=>{
    e.preventDefault();

    const msg=document.getElementById('messageInput').value.trim();
    if(!msg) return;

    const chat=document.getElementById('chatWindow');
    const model=document.getElementById('modelSelect').value;

    let div=document.createElement('div');
    div.className='msg-user';
    div.textContent=msg;
    chat.appendChild(div);

    const think=document.createElement('div');
    think.className='msg-jarvis';
    think.textContent='JARVIS réfléchit...';
    chat.appendChild(think);

    chat.scrollTop=chat.scrollHeight;

    const fd=new FormData();
    fd.append('ajax','true');
    fd.append('message',msg);
    fd.append('model',model);

    const res=await fetch('',{method:'POST',body:fd});
    const json=await res.json();

    think.remove();

    let bot=document.createElement('div');
    bot.className='msg-jarvis';
    let span=document.createElement('span');
    bot.appendChild(span);
    chat.appendChild(bot);

    typeWriter(json.message, span);
});
</script>

</body>
</html>

