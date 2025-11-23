<?php
// -------------------------------
// API HANDLER (AJAX REQUEST)
// -------------------------------
if (!empty($_POST["ajax"])) {

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $model = $_POST["model"];
    $userMessage = $_POST["message"];
    $response = "";

    // ------------------------
    // CALL COSMOSRP
    // ------------------------
    if ($model === "cosmosrp") {

        $url = "https://api.pawan.krd/cosmosrp/v1/chat/completions";

        $payload = [
            "model" => "cosmosrp",
            "messages" => [
                ["role" => "system", "content" => "Tu es JARVIS AI, assistant virtuel professionnel créé par Pepe Musafiri."],
                ["role" => "user", "content" => $userMessage]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $raw = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($raw, true);
        $response = $data["choices"][0]["message"]["content"] ?? "Erreur CosmosRP.";
    }

    // ------------------------
    // CALL COHERE
    // ------------------------
    if ($model === "c4ai") {

        $url = "https://api.cohere.com/v2/chat";

        $payload = [
            "model" => "c4ai-aya-expanse-32b",
            "messages" => [
                ["role" => "user", "content" => $userMessage]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer YOUR_API_KEY"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $raw = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($raw, true);

        if (isset($data["message"]["content"][0]["text"])) {
            $response = $data["message"]["content"][0]["text"];
        } else {
            $response = json_encode($data);
        }
    }

    echo json_encode(["reply" => $response]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>JARVIS AI</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.responsivevoice.org/responsivevoice.js?key=YOUR_RESPONSIVEVOICE_KEY"></script>

<style>
body{
    background:#020610;
    color:#00eaff;
    font-family:"Orbitron";
}
#chat{
    height:60vh;
    overflow-y:auto;
    background:rgba(0,255,255,0.04);
    padding:15px;
    border-radius:10px;
}
.msg-user{
    text-align:right;
    background:rgba(0,255,255,0.1);
    margin:8px;
    padding:10px;
    border-radius:8px;
}
.msg-jarvis{
    background:rgba(255,255,255,0.05);
    margin:8px;
    padding:10px;
    border-radius:8px;
}
/* Typing effect */
.typing {
    border-right: 2px solid #00eaff;
    white-space: pre-wrap;
}
/* Thinking animation */
.dots span {
    animation: blink 1.5s infinite;
}
@keyframes blink {
    0% { opacity: 0.2; }
    50% { opacity: 1; }
    100% { opacity: 0.2; }
}
</style>
</head>

<body class="container py-4">

<h1 class="text-center">JARVIS AI</h1>

<div id="chat"></div>

<div class="mt-3">
    <input id="message" type="text" class="form-control" placeholder="Parle à JARVIS...">
    <select id="model" class="form-control mt-2">
        <option value="cosmosrp">CosmosRP</option>
        <option value="c4ai">C4AI Aya Expanse 32B</option>
    </select>
    <button onclick="send()" class="btn btn-info w-100 mt-3">Envoyer</button>
</div>

<script>
// ----------------------------
// SEND MESSAGE (AJAX)
// ----------------------------
function send() {
    const msg = document.getElementById("message").value;
    const model = document.getElementById("model").value;

    if (!msg) return;

    // Print user message
    document.getElementById("chat").innerHTML +=
        `<div class='msg-user'>${msg}</div>`;

    // Show thinking animation
    const thinkId = "think_" + Date.now();
    document.getElementById("chat").innerHTML += `
        <div class='msg-jarvis' id="${thinkId}">
            JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>
        </div>
    `;

    // Scroll
    document.getElementById("chat").scrollTop = 999999;

    // AJAX Request
    fetch("", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: new URLSearchParams({
            ajax: 1,
            message: msg,
            model: model
        })
    })
    .then(res => res.json())
    .then(data => {

        const reply = data.reply;

        // Remove thinking
        document.getElementById(thinkId).remove();

        // Add JARVIS reply with typing effect
        const id = "reply_" + Date.now();
        document.getElementById("chat").innerHTML +=
            `<div class='msg-jarvis'><span id="${id}" class="typing"></span></div>`;

        typeWriter(id, reply);

        // Text-to-speech
        responsiveVoice.speak(reply, "French Male");
    });

    document.getElementById("message").value = "";
}

// ----------------------------
// TYPING EFFECT
// ----------------------------
function typeWriter(id, text) {
    let i = 0;
    function type() {
        if (i < text.length) {
            document.getElementById(id).innerHTML += text.charAt(i);
            i++;
            setTimeout(type, 20);
        }
    }
    type();
}
</script>

</body>
</html>


