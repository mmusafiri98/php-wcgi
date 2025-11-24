<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');
    $model = $_POST['model'] ?? 'c4ai';
    $userMessage = trim($_POST['message'] ?? '');
    $response = ['success'=>false, 'message'=>'', 'debug'=>''];

    if ($userMessage !== '') {
        if ($model === 'cosmosrp') {
            $api_url = 'https://api.pawan.krd/cosmosrp/v1/chat/completions';
            $payload = ['model'=>'cosmosrp', 'messages'=>[['role'=>'system','content'=>'Tu es JARVIS AI.'], ['role'=>'user','content'=>$userMessage]]];

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) $response['message'] = 'Erreur CURL: '.$err;
            else {
                $data = json_decode($raw,true);
                $response['message'] = $data['choices'][0]['message']['content'] ?? 'Pas de réponse';
                $response['success'] = true;
            }
        } else {
            $api_url = 'https://api.cohere.com/v2/chat';
            $payload = ['model'=>'c4ai-aya-expanse-32b','messages'=>[['role'=>'user','content'=>$userMessage]]];
            $ch = curl_init($api_url);
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_HTTPHEADER,["Content-Type: application/json", "Authorization: Bearer Uw540GN865rNyiOs3VMnWhRaYQ97KAfudAHAnXzJ"]);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload));
            curl_setopt($ch,CURLOPT_TIMEOUT,30);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) $response['message'] = 'Erreur CURL: '.$err;
            else {
                $data = json_decode($raw,true);
                $response['message'] = $data['message']['content'][0]['text'] ?? ($data['text'] ?? 'Pas de réponse');
                $response['success'] = true;
            }
        }
    } else $response['message'] = 'Message vide';

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JARVIS AI Chat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
body{background:#020610;color:#00eaff;font-family:'Orbitron',sans-serif;}
#chatWindow{height:400px;overflow-y:auto;background:rgba(0,0,0,0.4);padding:15px;border-radius:12px;border:1px solid rgba(0,234,255,0.15);}
.msg-user{background:rgba(0,234,255,0.15);padding:12px 15px;border-radius:15px 15px 5px 15px;margin:10px 0;text-align:right;}
.msg-jarvis{background:rgba(255,255,255,0.1);padding:12px 15px;border-radius:15px 15px 15px 5px;margin:10px 0;text-align:left;}
.typing{border-right:3px solid #00eaff;animation:blink 1s infinite;}
@keyframes blink{0%,100%{opacity:0.3;}50%{opacity:1;}}
</style>
</head>
<body>
<div class="container my-3">
<h3 class="text-center">JARVIS AI Chat</h3>
<div id="chatWindow">
<div class="msg-jarvis">Bonjour, je suis JARVIS.</div>
</div>
<form id="chatForm" class="mt-3">
<div class="input-group">
<input type="text" id="messageInput" class="form-control" placeholder="Tapez votre message" required>
<button class="btn btn-primary" type="submit">Envoyer</button>
</div>
<select id="modelSelect" class="form-select mt-2">
<option value="c4ai">C4AI Aya Expanse</option>
<option value="cosmosrp">CosmosRP</option>
</select>
</form>
</div>
<script>
let isMobile=/iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
function speakJarvis(text){
 if(!text)return;
 if('speechSynthesis' in window){
  let u=new SpeechSynthesisUtterance(text);u.lang='fr-FR';u.rate=0.9;window.speechSynthesis.speak(u);
 }
}
function typeWriter(text,element){
 let i=0,block='';element.classList.add('typing');
 function type(){
  if(i<text.length){
   element.textContent+=text[i];
   block+=text[i];
   i++;
   if(block.length>=40||/[.,!?]/.test(text[i-1])){speakJarvis(block.trim());block='';}
   element.parentElement.scrollTop = element.parentElement.scrollHeight;
   setTimeout(type,20);
  } else { if(block.length>0)speakJarvis(block.trim()); element.classList.remove('typing');}
 }
 type();
}
document.getElementById('chatForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const message=document.getElementById('messageInput').value.trim();
 if(!message)return;
 const model=document.getElementById('modelSelect').value;
 const chatWindow=document.getElementById('chatWindow');
 const userDiv=document.createElement('div');userDiv.className='msg-user';userDiv.textContent=message;chatWindow.appendChild(userDiv);
 const jarvisDiv=document.createElement('div');jarvisDiv.className='msg-jarvis';chatWindow.appendChild(jarvisDiv);
 chatWindow.scrollTop=chatWindow.scrollHeight;
 const formData=new FormData();formData.append('ajax','true');formData.append('message',message);formData.append('model',model);
 fetch('',{method:'POST',body:formData}).then(r=>r.json()).then(data=>{typeWriter(data.message,jarvisDiv);});
 document.getElementById('messageInput').value='';
});
</script>
</body>
</html>

