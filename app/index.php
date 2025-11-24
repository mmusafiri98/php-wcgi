<?php
// FULL RESPONSIVE VERSION — jarvis.gif visible + mobile layout (image on top)
// All previous issues fixed: GIF missing, mobile order, responsive layout
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === CHANGE THIS: use your real GIF file ===
// Place your jarvis.gif inside your hosting folder (ex: /public_html/jarvis.gif)
$imageUrl = "jarvis.gif"; // must exist on your server

// --- AJAX handler ---
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');

    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $response = ["success" => false, "message" => "", "debug" => ""];

    if ($userMessage !== "") {
        if ($model === "cosmosrp") {
            $api_url = "https://api.pawan.krd/cosmosrp/v1/chat/completions";
            $payload = [
                "model" => "cosmosrp",
                "messages" => [
                    ["role" => "system", "content" => "Tu es JARVIS AI, assistant virtuel professionnel."],
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
                $response["debug"] = $raw;
                $response["message"] = $data["choices"][0]["message"]["content"] ?? "Erreur : pas de réponse CosmosRP (HTTP $httpCode)";
                $response["success"] = true;
            }
        }
        else {
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
                "Authorization: Bearer YOUR_COHERE_API_KEY"
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
                $response["debug"] = $raw;

                if (isset($data["message"]["content"][0]["text"])) {
                    $response["message"] = $data["message"]["content"][0]["text"];
                    $response["success"] = true;
                } elseif (isset($data["text"])) {
                    $response["message"] = $data["text"];
                    $response["success"] = true;
                } else {
                    $response["message"] = "Réponse API inconnue (HTTP $httpCode)";
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
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>JARVIS AI — Mobile First</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

<style>
body{ background:#020610;color:#00eaff;font-family:"Orbitron",Arial;margin:0; }
.card-app{background:rgba(0,255,255,0.05);border-radius:12px;padding:12px;}
#chatWindow{background:rgba(0,0,0,0.25);padding:12px;border-radius:10px;height:45vh;overflow-y:auto;}
.msg-user{background:rgba(0,255,255,0.15);padding:8px 12px;border-radius:10px;margin:6px 0;text-align:right;}
.msg-jarvis{background:rgba(255,255,255,0.08);padding:8px 12px;border-radius:10px;margin:6px 0;text-align:left;}
.typing{border-right:2px solid #00eaff;}

/* MOBILE FIRST → jarvis.gif on top */
.visual-wrap{
  width:100%;height:260px;border-radius:10px;overflow:hidden;
  display:flex;align-items:center;justify-content:center;background:#000;
}
.visual-wrap img{
  width:100%;height:100%;object-fit:contain;
}

@media(min-width:768px){
  #chatWindow{ height:55vh; }
  .visual-wrap{ height:45vh; }
}
</style>
</head>
<body>
<div class="container py-3">

  <!-- IMAGE ALWAYS ON TOP FOR MOBILE -->
  <div class="visual-wrap mb-3">
    <img src="<?php echo $imageUrl; ?>" alt="jarvis gif">
  </div>

  <div class="row g-3">

    <!-- CHAT -->
    <div class="col-12 col-md-6">
      <div class="card-app">
        <h5 class="text-center">JARVIS — Chat</h5>

        <div id="chatWindow">
          <div class="msg-jarvis">Bonjour, je suis JARVIS.</div>
        </div>

        <form id="chatForm" class="mt-2" onsubmit="return false;">
          <input id="messageInput" class="form-control bg-dark text-light mb-2" placeholder="Message...">

          <div class="d-flex gap-2">
            <select id="modelSelect" class="form-select bg-black text-light">
              <option value="c4ai">C4AI 32B</option>
              <option value="cosmosrp">CosmosRP</option>
            </select>
            <button id="sendBtn" class="btn btn-info">Envoyer</button>
          </div>
        </form>
      </div>
    </div>

    <!-- RIGHT INFO CARD -->
    <div class="col-12 col-md-6">
      <div class="card-app h-100">
        <h5 class="text-center">Système</h5>
        <p>Status : <b style="color:#8bffcf">En ligne</b></p>
        <p>Modèle : <span id="currentModel">c4ai</span></p>
        <hr>
        <p style="font-size:0.9rem;color:#9ee;">- jarvis.gif est maintenant affiché correctement.<br>- Sur mobile il est placé au-dessus du chat.<br>- Layout totalement responsive Bootstrap.</p>
      </div>
    </div>

  </div>
</div>

<script>
function appendUserMessage(t){
  const c=document.getElementById("chatWindow");
  let d=document.createElement("div");d.className="msg-user";d.textContent=t;c.appendChild(d);c.scrollTop=c.scrollHeight;
}
function appendThinking(id){
  const c=document.getElementById("chatWindow");
  let d=document.createElement("div");d.className="msg-jarvis";d.id=id;d.innerHTML="JARVIS réfléchit ...";c.appendChild(d);c.scrollTop=c.scrollHeight;
}
function appendJarvisTyping(text){
  const c=document.getElementById("chatWindow");
  let d=document.createElement("div");d.className="msg-jarvis";
  let s=document.createElement("span");s.className="typing";d.appendChild(s);c.appendChild(d);
  let i=0;function type(){ if(i<text.length){s.textContent+=text.charAt(i);i++;c.scrollTop=c.scrollHeight;setTimeout(type,18);} else {s.classList.remove('typing');} }
  type();
}

document.getElementById("chatForm").addEventListener("submit", async(e)=>{
  e.preventDefault();
  const input=document.getElementById("messageInput");
  const model=document.getElementById("modelSelect").value;
  const text=input.value.trim();
  if(!text) return;

  appendUserMessage(text);
  const thinkId='t'+Date.now();
  appendThinking(thinkId);
  document.getElementById('currentModel').textContent=model;
  input.value='';

  let fd=new FormData();
  fd.append('ajax','true');fd.append('message',text);fd.append('model',model);

  const res=await fetch(window.location.href,{method:'POST',body:fd});
  const json=await res.json();

  document.getElementById(thinkId)?.remove();
  appendJarvisTyping(json.message || 'Erreur');
});
</script>
</body>
</html>
