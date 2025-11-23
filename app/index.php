<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si c'est une requête AJAX
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json');
    
    $model = $_POST['model'] ?? "c4ai";
    $userMessage = $_POST['message'] ?? "";
    $response = ["success" => false, "message" => "", "debug" => ""];

    if (!empty($userMessage)) {

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
            
            $res = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $response["message"] = "Erreur CURL : " . $curlError;
            } else {
                $data = json_decode($res, true);
                $response["message"] = $data["choices"][0]["message"]["content"] ?? "Erreur : pas de réponse de CosmosRP";
                $response["success"] = true;
            }

        } else if ($model === "c4ai") {

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

            $res = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $response["message"] = "Erreur CURL : " . $curlError;
            } else {
                $data = json_decode($res, true);
                $response["debug"] = print_r($data, true);

                if (isset($data["message"]["content"][0]["text"])) {
                    $response["message"] = $data["message"]["content"][0]["text"];
                    $response["success"] = true;
                } else if (isset($data["error"])) {
                    $response["message"] = "Erreur API : " . json_encode($data["error"]);
                } else {
                    $response["message"] = "Structure inconnue : " . json_encode($data);
                }
            }
        }
    }

    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JARVIS AI — Interface</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

<style>
:root{
    --accent:#00eaff;
    --panel-bg:rgba(0,255,255,0.06);
}
body{
    background:#020610;
    font-family:"Orbitron";
    color:var(--accent);
}

.typing {
    border-right: .12em solid #00eaff;
    white-space: pre-wrap;
    overflow: hidden;
}

@keyframes blink {
  0% {opacity: 0.2;}
  50% {opacity: 1;}
  100% {opacity: 0.2;}
}
.dots span { animation: blink 1.5s infinite; }
.dots span:nth-child(2) { animation-delay: 0.3s; }
.dots span:nth-child(3) { animation-delay: 0.6s; }

.app-grid{
    display:grid;
    grid-template-columns:320px 1fr 320px;
    gap:20px;
    padding:20px;
    min-height:100vh;
}

.panel{
    background:var(--panel-bg);
    border:1px solid rgba(0,255,255,0.1);
    border-radius:14px;
    padding:14px;
    display:flex;
    flex-direction:column;
    gap:10px;
}

#response{
    height:60vh;
    overflow:auto;
    color:#dff9ff;
    padding:12px;
    background:rgba(0,10,16,0.25);
    border-radius:8px;
}

.msg-user{
    text-align:right;
    background:rgba(0,255,255,0.05);
    padding:10px;
    border-radius:10px;
    margin:8px 0;
}

.msg-jarvis{
    background:rgba(255,255,255,0.05);
    padding:10px;
    border-radius:10px;
    margin:8px 0;
}

.center-panel{
    display:flex;
    justify-content:center;
    align-items:center;
    border-radius:14px;
    overflow:hidden;
    position: relative;
}

.center-panel img{
    width:100%;
    height:100%;
    object-fit:cover;
}

@media(max-width:992px){
    .center-panel{
        height: 250px;
    }
    .center-panel img{
        object-fit:contain;
    }
}
</style>
</head>

<body>
<div class="app-grid">

<!-- LEFT PANEL -->
<div class="panel left-panel">
<h3 style="text-align:center;">JARVIS AI</h3>

<div id="response">
    <div class="msg-jarvis">Bonjour, je suis JARVIS. Comment puis-je vous aider ?</div>
</div>

<form id="chatForm">
    <input type="text" id="messageInput" name="message" placeholder="Parle à JARVIS..." class="form-control" required>

    <select id="modelSelect" name="model" class="form-control mt-2" style="background:#000;color:var(--accent);">
        <option value="c4ai">C4AI Aya Expanse 32B</option>
        <option value="cosmosrp">CosmosRP</option>
    </select>

    <button type="submit" class="btn btn-info w-100 mt-3">Envoyer</button>
</form>
</div>

<!-- CENTER (JARVIS GIF) -->
<div class="panel center-panel">
    <img id="jarvis-gif" src="jarvis.gif" alt="JARVIS" loading="lazy">
</div>

<!-- RIGHT PANEL -->
<div class="panel right-panel">
    <h4 style="text-align:center;">Système</h4>
    <p>Statut : <b style="color:#8bffcf">En ligne</b></p>
    <p>Modèle sélectionné : <b id="currentModel">c4ai</b></p>
</div>

</div>

<!-- ResponsiveVoice (meilleure qualité vocale) -->
<script src="https://code.responsivevoice.org/responsivevoice.js?key=JvEZWtoL"></script>

<script>
// Variable pour tracker si ResponsiveVoice est prêt
let voiceReady = false;

// Attendre que ResponsiveVoice soit chargé
window.addEventListener('load', function() {
    setTimeout(() => {
        if (typeof responsiveVoice !== 'undefined') {
            voiceReady = true;
            console.log("✅ ResponsiveVoice chargé");
        }
    }, 1000);
});

function speakJarvis(text) {
    // OPTION 1: ResponsiveVoice (meilleure qualité)
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        try {
            // Liste des voix françaises de meilleure qualité
            const voiceOptions = [
                "French Male",
                "French Female",
                "Francais France Male",
                "Francais France Female"
            ];
            
            responsiveVoice.speak(text, voiceOptions[0], {
                pitch: 1,
                rate: 0.9,
                volume: 1,
                onstart: () => console.log("🔊 JARVIS parle..."),
                onerror: (e) => {
                    console.warn("⚠️ Erreur ResponsiveVoice:", e);
                    fallbackToNativeVoice(text);
                }
            });
            return;
        } catch (e) {
            console.warn("⚠️ ResponsiveVoice erreur:", e);
        }
    }
    
    // OPTION 2: Fallback vers l'API native du navigateur
    fallbackToNativeVoice(text);
}

function fallbackToNativeVoice(text) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'fr-FR';
        utterance.rate = 0.9; // Un peu plus lent pour mieux comprendre
        utterance.pitch = 1.1; // Légèrement plus aigu
        utterance.volume = 1.0;
        
        // Attendre que les voix soient chargées
        const setVoice = () => {
            const voices = window.speechSynthesis.getVoices();
            
            // Chercher la meilleure voix française
            const preferredVoices = [
                voices.find(v => v.lang === 'fr-FR' && v.name.includes('Thomas')),
                voices.find(v => v.lang === 'fr-FR' && v.name.includes('Google')),
                voices.find(v => v.lang === 'fr-FR' && !v.localService),
                voices.find(v => v.lang.startsWith('fr')),
                voices.find(v => v.lang === 'fr-FR')
            ];
            
            const bestVoice = preferredVoices.find(v => v);
            if (bestVoice) {
                utterance.voice = bestVoice;
                console.log("🔊 Utilisation de:", bestVoice.name);
            }
            
            window.speechSynthesis.speak(utterance);
        };
        
        // Sur iOS/Safari, les voix se chargent de manière asynchrone
        if (window.speechSynthesis.getVoices().length === 0) {
            window.speechSynthesis.onvoiceschanged = setVoice;
        } else {
            setVoice();
        }
    } else {
        console.warn("⚠️ Synthèse vocale non supportée");
    }
}

function typeWriter(text, elementId) {
    const typingDiv = document.getElementById(elementId);
    let index = 0;

    function type() {
        if (index < text.length) {
            typingDiv.innerHTML += text.charAt(index);
            index++;
            setTimeout(type, 20);
        } else {
            typingDiv.classList.remove('typing');
            // Attendre un peu avant de parler
            setTimeout(() => speakJarvis(text), 300);
        }
    }
    type();
}

// Gestion du formulaire
document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const messageInput = document.getElementById('messageInput');
    const modelSelect = document.getElementById('modelSelect');
    const responseDiv = document.getElementById('response');
    const userMessage = messageInput.value.trim();
    const selectedModel = modelSelect.value || 'c4ai'; // Par défaut C4AI

    if (!userMessage) return;

    // Afficher le message utilisateur
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'msg-user';
    userMsgDiv.textContent = userMessage;
    responseDiv.appendChild(userMsgDiv);

    // Afficher "JARVIS réfléchit..."
    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'msg-jarvis';
    thinkingDiv.id = 'thinking';
    thinkingDiv.innerHTML = 'JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    responseDiv.appendChild(thinkingDiv);

    // Scroll vers le bas
    responseDiv.scrollTop = responseDiv.scrollHeight;

    // Mettre à jour le modèle affiché
    document.getElementById('currentModel').textContent = selectedModel;

    // Vider l'input
    messageInput.value = '';

    try {
        // Envoyer la requête AJAX
        const formData = new FormData();
        formData.append('message', userMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        // Supprimer "JARVIS réfléchit"
        thinkingDiv.remove();

        // Afficher la réponse de JARVIS avec effet typing
        const jarvisMsgDiv = document.createElement('div');
        jarvisMsgDiv.className = 'msg-jarvis';
        const typingSpan = document.createElement('span');
        typingSpan.id = 'typedResponse_' + Date.now();
        typingSpan.className = 'typing';
        jarvisMsgDiv.appendChild(typingSpan);
        responseDiv.appendChild(jarvisMsgDiv);

        // Afficher debug si présent
        if (data.debug) {
            const debugDiv = document.createElement('pre');
            debugDiv.style.cssText = 'color:#ff6b6b;font-size:10px;';
            debugDiv.textContent = data.debug;
            responseDiv.appendChild(debugDiv);
        }

        // Lancer l'animation typing
        typeWriter(data.message, typingSpan.id);

        // Scroll vers le bas
        responseDiv.scrollTop = responseDiv.scrollHeight;

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
    }
});
</script>

</body>
</html>
