<?php
// index.php - Responsive, AJAX chat, typing effect, TTS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
                $response["debug"] = $raw;
                $response["message"] = $data["choices"][0]["message"]["content"] ?? "Erreur : pas de réponse de CosmosRP (HTTP $httpCode)";
                $response["success"] = true;
            }

        } else { // c4ai (Cohere)
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
                // === REPLACE THIS WITH YOUR REAL COHERE KEY ===
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
                $response["debug"] = $raw;

                // Different possible Cohere shapes: try common ones
                if (isset($data["message"]["content"][0]["text"])) {
                    $response["message"] = $data["message"]["content"][0]["text"];
                    $response["success"] = true;
                } else if (isset($data["text"])) {
                    $response["message"] = $data["text"];
                    $response["success"] = true;
                } else if (isset($data["error"])) {
                    $response["message"] = "Erreur API : " . ($data["error"]["message"] ?? json_encode($data["error"]));
                } else {
                    $response["message"] = "Structure inconnue (HTTP $httpCode).";
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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>JARVIS AI — Interface</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --accent:#00eaff;
      --panel-bg: rgba(0,255,255,0.06);
      --muted: rgba(223,249,255,0.12);
    }
    body{
      background:#020610;
      color:var(--accent);
      font-family:"Orbitron", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      margin:0;
      -webkit-font-smoothing:antialiased;
    }

    /* Layout */
    .app-container {
      max-width: 1200px;
      margin: 18px auto;
      padding: 12px;
    }

    .card-app {
      background: var(--panel-bg);
      border: 1px solid rgba(0,255,255,0.08);
      border-radius: 12px;
      padding: 12px;
      min-height: 72vh;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    /* Chat area */
    #chatWindow {
      background: rgba(0,10,16,0.24);
      padding: 14px;
      border-radius: 10px;
      height: 56vh;
      overflow-y: auto;
      color: #e8fbff;
    }

    .msg-user {
      text-align: right;
      background: rgba(0,255,255,0.05);
      color: #bff6ff;
      padding: 10px 12px;
      border-radius: 10px;
      display: inline-block;
      margin: 8px 0;
      max-width: 95%;
      word-wrap: break-word;
    }

    .msg-jarvis {
      text-align: left;
      background: rgba(255,255,255,0.03);
      color: #e6fbff;
      padding: 10px 12px;
      border-radius: 10px;
      display: inline-block;
      margin: 8px 0;
      max-width: 95%;
      word-wrap: break-word;
    }

    .typing {
      border-right: .12em solid var(--accent);
      white-space: pre-wrap;
      overflow: hidden;
      display: inline-block;
    }

    /* thinking dots */
    .dots span { animation: blink 1.5s infinite; display:inline-block; margin-right:2px; }
    .dots span:nth-child(2) { animation-delay: 0.25s; }
    .dots span:nth-child(3) { animation-delay: 0.5s; }
    @keyframes blink { 0%{opacity:.2}50%{opacity:1}100%{opacity:.2} }

    /* Right panel small stats */
    .sys-card {
      background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));
      border-radius: 10px;
      padding: 12px;
      color: #dff9ff;
    }

    /* Responsive tweaks */
    @media (max-width: 991.98px) {
      #chatWindow { height: 50vh; }
    }
    @media (max-width: 575.98px) {
      #chatWindow { height: 48vh; }
      .card-app { padding: 8px; }
    }
  </style>
</head>
<body>
  <div class="container app-container">
    <div class="row g-3">
      <!-- Left: Chat -->
      <div class="col-12 col-lg-4">
        <div class="card-app h-100">
          <h5 class="text-center">JARVIS AI</h5>

          <div id="chatWindow" aria-live="polite" aria-atomic="false">
            <div class="msg-jarvis">Bonjour, je suis JARVIS. Comment puis-je vous aider ?</div>
          </div>

          <form id="chatForm" class="mt-auto" onsubmit="return false;">
            <div class="mb-2">
              <input id="messageInput" name="message" type="text" class="form-control bg-dark text-light border-0" placeholder="Parle à JARVIS..." autocomplete="off" required>
            </div>

            <div class="d-flex gap-2">
              <select id="modelSelect" name="model" class="form-select bg-black text-light" aria-label="Choisir un modèle">
                <option value="c4ai">C4AI Aya Expanse 32B</option>
                <option value="cosmosrp">CosmosRP</option>
              </select>
              <button id="sendBtn" class="btn btn-info flex-shrink-0">Envoyer</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Center: Visual / large -->
      <div class="col-12 col-lg-4">
        <div class="card-app h-100 d-flex flex-column align-items-center justify-content-center">
          <!-- Use uploaded file path as image source (was provided in session) -->
          <img src="/mnt/data/773cfaa9-881a-4acb-a0ac-63ff13af967d.png" alt="Jarvis" class="img-fluid" style="max-height:60vh; border-radius:8px; object-fit:cover;">
          <p class="mt-3 text-center" style="color:var(--muted); font-size:0.95rem;">JARVIS — assistant virtuel</p>
        </div>
      </div>

      <!-- Right: system / info -->
      <div class="col-12 col-lg-4">
        <div class="card-app h-100">
          <h6 class="text-center">Système</h6>
          <div class="sys-card">
            <p class="mb-1">Statut : <strong style="color:#8bffcf">En ligne</strong></p>
            <p class="mb-1">Modèle sélectionné : <strong id="currentModel">c4ai</strong></p>
            <hr style="opacity:.06;">
            <p style="font-size:.9rem; color:var(--muted)">Conseils :</p>
            <ul style="color:var(--muted); font-size:.9rem; padding-left:1rem;">
              <li>Utilise le modèle <code>c4ai</code> pour LLM texte.</li>
              <li>Remplace <code>YOUR_COHERE_API_KEY</code> par ta clé Cohere côté serveur.</li>
              <li>Si tu as une page blanche, active les erreurs PHP (déjà actives ici).</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ResponsiveVoice (TTS) - replace key if you have one -->
  <script src="https://code.responsivevoice.org/responsivevoice.js?key=JvEZWtoL"></script>

  <!-- Bootstrap JS (optional, for dropdowns/tooltips) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // ---------- Utilities ----------
  function appendUserMessage(text) {
    const chat = document.getElementById('chatWindow');
    const d = document.createElement('div');
    d.className = 'msg-user';
    d.textContent = text;
    chat.appendChild(d);
    chat.scrollTop = chat.scrollHeight;
  }

  function appendThinking(id) {
    const chat = document.getElementById('chatWindow');
    const d = document.createElement('div');
    d.className = 'msg-jarvis';
    d.id = id;
    d.innerHTML = 'JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    chat.appendChild(d);
    chat.scrollTop = chat.scrollHeight;
  }

  function appendJarvisTyping(text) {
    const chat = document.getElementById('chatWindow');
    const wrapper = document.createElement('div');
    wrapper.className = 'msg-jarvis';
    const span = document.createElement('span');
    const spanId = 'typed_' + Date.now();
    span.id = spanId;
    span.className = 'typing';
    wrapper.appendChild(span);
    chat.appendChild(wrapper);
    chat.scrollTop = chat.scrollHeight;
    // typing effect
    let i = 0;
    function type() {
      if (i < text.length) {
        span.textContent += text.charAt(i);
        i++;
        chat.scrollTop = chat.scrollHeight;
        setTimeout(type, 18);
      } else {
        span.classList.remove('typing');
      }
    }
    type();
    return spanId;
  }

  // ---------- Text-to-Speech ----------
  function speakJarvis(text) {
    // Try responsiveVoice first
    if (typeof responsiveVoice !== 'undefined') {
      try {
        // Use a French male voice option (depends on responsiveVoice availability)
        responsiveVoice.speak(text, "French Male", {rate: 0.95, pitch:1, volume:1});
        return;
      } catch (e) {
        console.warn('ResponsiveVoice error', e);
      }
    }

    // Fallback to Web Speech API
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
      const u = new SpeechSynthesisUtterance(text);
      u.lang = 'fr-FR';
      u.rate = 0.95;
      u.pitch = 1;
      // choose a french voice if available
      const voices = window.speechSynthesis.getVoices().filter(v => v.lang && v.lang.startsWith('fr'));
      if (voices.length) u.voice = voices[0];
      window.speechSynthesis.speak(u);
    } else {
      console.warn('No TTS available');
    }
  }

  // ---------- AJAX send ----------
  document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const input = document.getElementById('messageInput');
    const model = document.getElementById('modelSelect').value || 'c4ai';
    const text = input.value.trim();
    if (!text) return;

    // UI updates
    appendUserMessage(text);
    const thinkId = 'thinking_' + Date.now();
    appendThinking(thinkId);
    document.getElementById('currentModel').textContent = model;
    input.value = '';
    input.disabled = true;
    document.getElementById('sendBtn').disabled = true;

    // build form data
    const fd = new FormData();
    fd.append('ajax', 'true');
    fd.append('message', text);
    fd.append('model', model);

    try {
      const res = await fetch(window.location.href, {
        method: 'POST',
        body: fd
      });

      const json = await res.json();

      // remove thinking
      const thinkingEl = document.getElementById(thinkId);
      if (thinkingEl) thinkingEl.remove();

      // show debug if any (server-side)
      if (json.debug) {
        const dbg = document.createElement('pre');
        dbg.style.cssText = 'color:#ff6b6b;font-size:11px;white-space:pre-wrap;';
        dbg.textContent = json.debug;
        document.getElementById('chatWindow').appendChild(dbg);
      }

      const reply = json.message || "Aucune réponse.";

      // typing + TTS
      appendJarvisTyping(reply);
      // small delay to ensure typing element exists/readable
      setTimeout(() => speakJarvis(reply), 500);

    } catch (err) {
      // remove thinking and show error
      const thinkingEl = document.getElementById(thinkId);
      if (thinkingEl) thinkingEl.innerHTML = '❌ Erreur réseau';
      console.error(err);
    } finally {
      input.disabled = false;
      document.getElementById('sendBtn').disabled = false;
      input.focus();
    }
  });

  // Enter to send
  document.getElementById('messageInput').addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      document.getElementById('chatForm').dispatchEvent(new Event('submit', {cancelable: true}));
    }
  });
  </script>
</body>
</html>

