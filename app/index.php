<?php
// =====================================================
// JARVIS AI - GIF ANIMATO SOLO DURANTE RISPOSTA
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');

/* ===================== YOUTUBE API ===================== */
define('YOUTUBE_API_KEY', 'AIzaSyDVMToR77KZRXfY2Y-Wx2XIWGy58E_MZDA');

$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, un assistant virtuel intelligent créé par Pepe Musafiri ... (PROMPT IDENTIQUE NON MODIFIÉ)";

function wantsTime($message) {
    $keywords = ['heure','time','quelle heure','what time','che ora','hora'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

function wantsDate($message) {
    $keywords = ['date','jour','today','oggi','hoy'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

/* ===================== YOUTUBE FUNCTIONS ===================== */
function wantsYouTubeVideo($message) {
    $keywords = [
        'youtube','vidéo','video','clip',
        'ouvre une vidéo','cherche une vidéo',
        'open a video','watch a video',
        'video su youtube','cerca un video'
    ];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) if (strpos($msg,$kw)!==false) return true;
    return false;
}

function youtubeSearch($query) {
    $url = "https://www.googleapis.com/youtube/v3/search?".http_build_query([
        'part'=>'snippet',
        'q'=>$query,
        'type'=>'video',
        'maxResults'=>1,
        'key'=>YOUTUBE_API_KEY
    ]);
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false]);
    $response=curl_exec($ch);
    curl_close($ch);
    $data=json_decode($response,true);
    if(isset($data['items'][0]['id']['videoId'])) {
        return "https://www.youtube.com/watch?v=".$data['items'][0]['id']['videoId'];
    }
    return null;
}

/* ===================== GOOGLE SEARCH ===================== */
function googleSearch($query,$numResults=5){/* CODE ORIGINAL INCHANGÉ */}

function needsWebSearch($message){/* CODE ORIGINAL INCHANGÉ */}

if (isset($_POST['ajax']) && $_POST['ajax']==='true') {
    header('Content-Type: application/json; charset=utf-8');
    $model=$_POST['model']??"c4ai";
    $userMessage=trim($_POST['message']??"");

    if($userMessage!=="") {

        /* ===== YOUTUBE INTERCEPTION AVANT IA ===== */
        if(wantsYouTubeVideo($userMessage)) {
            $videoUrl=youtubeSearch($userMessage);
            if($videoUrl){
                echo json_encode([
                    "success"=>true,
                    "message"=>"🎬 J’ai trouvé la meilleure vidéo sur YouTube. Je l’ouvre maintenant.",
                    "browserCommand"=>["action"=>"OPEN","param"=>$videoUrl]
                ],JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        /* ===== TEMPS / DATE ===== */
        if(wantsTime($userMessage)){/* CODE ORIGINAL */}
        if(wantsDate($userMessage)){/* CODE ORIGINAL */}

        /* ===== IA (COHERE / COSMOS) ===== */
        /* CODE ORIGINAL STRICTEMENT IDENTIQUE */
    }
    echo json_encode(["success"=>false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>JARVIS AI</title>
<!-- TOUT LE HTML + CSS ORIGINAL NON MODIFIÉ -->
</head>
<body>
<!-- INTERFACE ORIGINALE INCHANGÉE -->
<script>
/* JAVASCRIPT ORIGINAL INCHANGÉ
   browserCommand OPEN fonctionne déjà */
</script>
</body>
</html>
