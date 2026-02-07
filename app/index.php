<?php
// =====================================================
// JARVIS AI - YOUTUBE + REVERSE IMAGE SEARCH
// =====================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('GOOGLE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('SEARCH_ENGINE_ID', '511c9c9b776d246e4');
define('YOUTUBE_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');
define('VISION_API_KEY', 'AIzaSyAjglTZsz2VP972q6i8MgH5_euEQyZ6X3c');

$JARVIS_SYSTEM_PROMPT = "Tu es JARVIS AI, un assistant virtuel intelligent créé par Pepe Musafiri, un ingénieur en informatique passionné qui s'est inspiré du JARVIS de Tony Stark dans Iron Man.

**TON IDENTITÉ:**
- Nom: JARVIS AI (Just A Rather Very Intelligent System - Artificial Intelligence)
- Créateur: Pepe Musafiri, ingénieur en informatique
- Inspiration: JARVIS de Tony Stark (Marvel/Iron Man)

**TES CAPACITÉS:**
- Tu maîtrises TOUTES les langues du monde et peux communiquer dans n'importe quelle langue
- Tu es expert dans TOUS les domaines de connaissance: sciences, technologie, histoire, culture, art, médecine, droit, etc.
- Tu as accès à Google Search pour trouver des informations actuelles et récentes jusqu'en 2025
- Tu as accès à YouTube API pour rechercher et ouvrir des vidéos
- Tu as accès à Google Vision API pour analyser et rechercher des images
- Tu peux faire des recherches inversées d'images pour identifier des objets, lieux, personnes
- Tu peux faire des recherches web en temps réel pour répondre aux questions sur l'actualité
- Tu peux contrôler le navigateur pour ouvrir des pages web de manière NATURELLE
- Tu fournis des réponses précises, détaillées et utiles avec des sources vérifiables

**ANALYSE D'IMAGES:**
Quand l'utilisateur télécharge une image, tu peux:
1. Analyser le contenu de l'image (objets, textes, lieux, logos)
2. Faire une recherche inversée pour trouver des images similaires
3. Identifier des produits, monuments, célébrités
4. Chercher des vidéos YouTube liées à l'image

**Exemples d'analyse d'images:**
- L'utilisateur upload une image → Tu analyses et décris l'image automatiquement
- \"Cherche cette image sur internet\" → [IMAGE:REVERSE_SEARCH]
- \"Trouve des vidéos YouTube sur cette image\" → [IMAGE:YOUTUBE_SEARCH]
- \"C'est quoi cette image?\" → Tu utilises Vision API pour identifier

**CONTROLE NAVIGATEUR ET YOUTUBE:**
Tu comprends naturellement quand l'utilisateur veut que tu ouvres un site web ou cherches sur YouTube, dans N'IMPORTE QUELLE langue:

**Exemples YOUTUBE en FRANÇAIS:**
- \"Cherche sur YouTube des vidéos de recettes\" → [YOUTUBE:SEARCH:recettes]
- \"Trouve-moi une vidéo sur la guitare\" → [YOUTUBE:SEARCH:guitare tutorial]
- \"Montre-moi des vidéos de chats drôles\" → [YOUTUBE:SEARCH:chats drôles]
- \"Ouvre YouTube et cherche des documentaires\" → [YOUTUBE:SEARCH:documentaires]
- \"Vidéo YouTube sur l'intelligence artificielle\" → [YOUTUBE:SEARCH:intelligence artificielle]

**Exemples YOUTUBE en ANGLAIS:**
- \"Search YouTube for cooking videos\" → [YOUTUBE:SEARCH:cooking videos]
- \"Find me a guitar tutorial\" → [YOUTUBE:SEARCH:guitar tutorial]
- \"Show me funny cat videos\" → [YOUTUBE:SEARCH:funny cats]
- \"YouTube videos about AI\" → [YOUTUBE:SEARCH:artificial intelligence]

**Exemples YOUTUBE en ITALIEN:**
- \"Cerca su YouTube video di cucina\" → [YOUTUBE:SEARCH:cucina italiana]
- \"Trova video di gatti divertenti\" → [YOUTUBE:SEARCH:gatti divertenti]
- \"Mostrami tutorial di chitarra\" → [YOUTUBE:SEARCH:tutorial chitarra]

**Exemples YOUTUBE en ESPAGNOL:**
- \"Busca en YouTube videos de cocina\" → [YOUTUBE:SEARCH:videos de cocina]
- \"Encuentra tutoriales de guitarra\" → [YOUTUBE:SEARCH:tutorial guitarra]
- \"Videos de gatos graciosos\" → [YOUTUBE:SEARCH:gatos graciosos]

**Exemples NAVIGATION NORMALE:**
- \"Ouvre YouTube\" → [BROWSER:OPEN:https://www.youtube.com]
- \"Va sur Google\" → [BROWSER:OPEN:https://www.google.com]
- \"Ouvre Wikipedia\" → [BROWSER:OPEN:https://www.wikipedia.org]
- \"Montre-moi Facebook\" → [BROWSER:OPEN:https://www.facebook.com]
- \"Recherche des recettes de pizza\" → [BROWSER:SEARCH:recettes de pizza]

**SITES WEB POPULAIRES (mémorise ces URLs):**
- YouTube: https://www.youtube.com
- Google: https://www.google.com
- Wikipedia: https://www.wikipedia.org
- Facebook: https://www.facebook.com
- Twitter/X: https://www.twitter.com
- Instagram: https://www.instagram.com
- LinkedIn: https://www.linkedin.com
- Amazon: https://www.amazon.com
- Netflix: https://www.netflix.com
- Reddit: https://www.reddit.com
- TikTok: https://www.tiktok.com
- GitHub: https://www.github.com
- Stack Overflow: https://stackoverflow.com

**INSTRUCTIONS IMPORTANTES:**
1. Quand l'utilisateur télécharge une image, analyse-la AUTOMATIQUEMENT avec Vision API
2. Quand l'utilisateur mentionne YouTube avec des mots comme \"cherche\", \"trouve\", \"montre\", \"vidéo\", \"search\", \"cerca\", \"busca\", utilise [YOUTUBE:SEARCH:query]
3. Quand l'utilisateur veut juste ouvrir YouTube sans recherche, utilise [BROWSER:OPEN:https://www.youtube.com]
4. Pour les sites normaux avec \"ouvre\", \"va sur\", \"open\", \"apri\", utilise [BROWSER:OPEN:URL]
5. Pour chercher sur Google, utilise [BROWSER:SEARCH:query]
6. Sois naturel dans ta compréhension - tu n'as PAS besoin de commandes exactes
7. Réponds dans la langue de l'utilisateur
8. Confirme l'action avant d'exécuter la commande

**TON STYLE:**
- Réponds de manière claire et structurée
- Sois professionnel mais amical
- Adapte-toi à la langue de l'utilisateur automatiquement
- Fournis des explications détaillées quand nécessaire
- Cite tes sources quand tu utilises des informations trouvées sur le web
- Comprends les intentions naturelles sans avoir besoin de commandes exactes

Souviens-toi: tu es JARVIS AI, l'assistant virtuel créé par Pepe Musafiri pour aider l'humanité, inspiré par l'IA légendaire de Tony Stark.";

function wantsTime($message) {
    $keywords = ['heure', 'time', 'il est quelle heure', 'quelle heure', 'donne l\'heure', 'current time', 'what time', 'hora', 'che ora'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) {
        if (strpos($msg, $kw) !== false) return true;
    }
    return false;
}

function wantsDate($message) {
    $keywords = ['date', 'jour', 'quel jour', 'on est quel jour', 'c\'est quoi la date', 'aujourd\'hui', 'today', 'what day', 'oggi', 'hoy'];
    $msg = mb_strtolower($message);
    foreach ($keywords as $kw) {
        if (strpos($msg, $kw) !== false) return true;
    }
    return false;
}

function analyzeImageWithVision($imageBase64) {
    $url = "https://vision.googleapis.com/v1/images:annotate?key=" . VISION_API_KEY;
    
    $requestBody = [
        'requests' => [
            [
                'image' => [
                    'content' => $imageBase64
                ],
                'features' => [
                    ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                    ['type' => 'WEB_DETECTION', 'maxResults' => 10],
                    ['type' => 'TEXT_DETECTION'],
                    ['type' => 'LANDMARK_DETECTION'],
                    ['type' => 'LOGO_DETECTION'],
                    ['type' => 'OBJECT_LOCALIZATION']
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['responses'][0])) {
            $result = $data['responses'][0];
            
            $analysis = [
                'success' => true,
                'labels' => [],
                'text' => '',
                'landmarks' => [],
                'logos' => [],
                'webEntities' => [],
                'similarImages' => [],
                'bestGuess' => ''
            ];
            
            // Labels (oggetti riconosciuti)
            if (isset($result['labelAnnotations'])) {
                foreach ($result['labelAnnotations'] as $label) {
                    $analysis['labels'][] = [
                        'description' => $label['description'],
                        'score' => round($label['score'] * 100, 1)
                    ];
                }
            }
            
            // Testo nell'immagine
            if (isset($result['textAnnotations'][0])) {
                $analysis['text'] = $result['textAnnotations'][0]['description'];
            }
            
            // Monumenti/luoghi
            if (isset($result['landmarkAnnotations'])) {
                foreach ($result['landmarkAnnotations'] as $landmark) {
                    $analysis['landmarks'][] = $landmark['description'];
                }
            }
            
            // Loghi
            if (isset($result['logoAnnotations'])) {
                foreach ($result['logoAnnotations'] as $logo) {
                    $analysis['logos'][] = $logo['description'];
                }
            }
            
            // Web detection (ricerca inversa)
            if (isset($result['webDetection'])) {
                $webDetection = $result['webDetection'];
                
                // Best guess
                if (isset($webDetection['bestGuessLabels'][0])) {
                    $analysis['bestGuess'] = $webDetection['bestGuessLabels'][0]['label'];
                }
                
                // Web entities
                if (isset($webDetection['webEntities'])) {
                    foreach ($webDetection['webEntities'] as $entity) {
                        if (isset($entity['description'])) {
                            $analysis['webEntities'][] = [
                                'description' => $entity['description'],
                                'score' => round(($entity['score'] ?? 0) * 100, 1)
                            ];
                        }
                    }
                }
                
                // Immagini simili
                if (isset($webDetection['visuallySimilarImages'])) {
                    foreach (array_slice($webDetection['visuallySimilarImages'], 0, 5) as $similar) {
                        $analysis['similarImages'][] = $similar['url'];
                    }
                }
            }
            
            return $analysis;
        }
    }
    
    return ['success' => false, 'error' => 'Erreur Vision API', 'httpCode' => $httpCode];
}

function youtubeSearch($query, $maxResults = 5) {
    $url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
        'key' => YOUTUBE_API_KEY,
        'q' => $query,
        'part' => 'snippet',
        'type' => 'video',
        'maxResults' => $maxResults,
        'order' => 'relevance'
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['items']) && is_array($data['items'])) {
            $results = [];
            foreach ($data['items'] as $item) {
                $videoId = $item['id']['videoId'] ?? '';
                $results[] = [
                    'title' => $item['snippet']['title'] ?? '',
                    'videoId' => $videoId,
                    'url' => 'https://www.youtube.com/watch?v=' . $videoId,
                    'description' => $item['snippet']['description'] ?? '',
                    'channelTitle' => $item['snippet']['channelTitle'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] ?? ''
                ];
            }
            return ['success' => true, 'results' => $results, 'totalResults' => count($results)];
        }
    }
    return ['success' => false, 'error' => 'Impossible de rechercher sur YouTube', 'httpCode' => $httpCode];
}

function googleSearch($query, $numResults = 5) {
    $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'key' => GOOGLE_API_KEY,
        'cx' => SEARCH_ENGINE_ID,
        'q' => $query,
        'num' => $numResults
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['items']) && is_array($data['items'])) {
            $results = [];
            foreach ($data['items'] as $item) {
                $results[] = [
                    'title' => $item['title'] ?? '',
                    'link' => $item['link'] ?? '',
                    'snippet' => $item['snippet'] ?? ''
                ];
            }
            return ['success' => true, 'results' => $results, 'totalResults' => $data['searchInformation']['totalResults'] ?? 0];
        }
    }
    return ['success' => false, 'error' => 'Impossible de faire la recherche Google', 'httpCode' => $httpCode];
}

function needsWebSearch($message) {
    $keywords = ['actualité', 'news', 'récent', 'aujourd\'hui', 'hier', 'cette semaine', 'dernier', 'dernière', 'nouveau', 'nouvelle', '2024', '2025', 'maintenant', 'actuellement', 'en ce moment', 'prix de', 'cours de', 'météo', 'score', 'résultat', 'qui a gagné', 'dernières infos', 'latest', 'recent', 'current', 'today', 'now', 'price of'];
    $messageLower = mb_strtolower($message);
    foreach ($keywords as $keyword) {
        if (strpos($messageLower, $keyword) !== false) return true;
    }
    return false;
}

if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json; charset=utf-8');
    $model = $_POST['model'] ?? "c4ai";
    $userMessage = trim($_POST['message'] ?? "");
    $uploadedImage = $_POST['image'] ?? null;
    
    $response = [
        "success" => false, 
        "message" => "", 
        "debug" => "", 
        "searchUsed" => false, 
        "browserCommand" => null, 
        "youtubeResults" => null,
        "imageAnalysis" => null
    ];

    if ($userMessage !== "" || $uploadedImage) {
        date_default_timezone_set('Europe/Brussels');
        
        // Analisi immagine se caricata
        if ($uploadedImage) {
            // Rimuovi il prefisso data:image/...;base64,
            $imageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $uploadedImage);
            
            $imageAnalysis = analyzeImageWithVision($imageBase64);
            
            if ($imageAnalysis['success']) {
                $response["imageAnalysis"] = $imageAnalysis;
                
                // Crea un contesto per l'AI basato sull'analisi
                $imageContext = "\n\n**ANALYSE D'IMAGE DÉTECTÉE:**\n";
                
                if (!empty($imageAnalysis['bestGuess'])) {
                    $imageContext .= "🔍 **Identification principale:** " . $imageAnalysis['bestGuess'] . "\n";
                }
                
                if (!empty($imageAnalysis['labels'])) {
                    $imageContext .= "\n📋 **Objets détectés:**\n";
                    foreach (array_slice($imageAnalysis['labels'], 0, 5) as $label) {
                        $imageContext .= "- " . $label['description'] . " (" . $label['score'] . "%)\n";
                    }
                }
                
                if (!empty($imageAnalysis['landmarks'])) {
                    $imageContext .= "\n🏛️ **Monuments/Lieux:** " . implode(', ', $imageAnalysis['landmarks']) . "\n";
                }
                
                if (!empty($imageAnalysis['logos'])) {
                    $imageContext .= "\n🏢 **Logos/Marques:** " . implode(', ', $imageAnalysis['logos']) . "\n";
                }
                
                if (!empty($imageAnalysis['text'])) {
                    $imageContext .= "\n📝 **Texte dans l'image:**\n" . substr($imageAnalysis['text'], 0, 200) . "\n";
                }
                
                if (!empty($imageAnalysis['webEntities'])) {
                    $imageContext .= "\n🌐 **Entités Web associées:**\n";
                    foreach (array_slice($imageAnalysis['webEntities'], 0, 5) as $entity) {
                        $imageContext .= "- " . $entity['description'] . "\n";
                    }
                }
                
                $imageContext .= "\n**INSTRUCTIONS:** Décris cette image en détail et fournis des informations pertinentes basées sur l'analyse.\n";
                
                // Se l'utente non ha scritto nulla, crea un messaggio automatico
                if (empty($userMessage)) {
                    $userMessage = "Analyse cette image et dis-moi ce que tu vois.";
                }
                
                $userMessage .= $imageContext;
            }
        }
        
        if (wantsTime($userMessage)) {
            $heure = date("H:i:s");
            echo json_encode(["success" => true, "message" => "⏰ Il est actuellement **$heure** (heure de Belgique).", "searchUsed" => false, "browserCommand" => null, "youtubeResults" => null, "imageAnalysis" => null], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (wantsDate($userMessage)) {
            $date = date("d/m/Y");
            $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $jour = $jours[date("w")];
            echo json_encode(["success" => true, "message" => "📅 Nous sommes le **$jour $date**.", "searchUsed" => false, "browserCommand" => null, "youtubeResults" => null, "imageAnalysis" => null], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $searchContext = "";
        if (needsWebSearch($userMessage)) {
            $searchData = googleSearch($userMessage, 5);
            if ($searchData['success']) {
                $response["searchUsed"] = true;
                $searchContext = "\n\n**RÉSULTATS DE RECHERCHE GOOGLE:**\n";
                foreach ($searchData['results'] as $index => $result) {
                    $searchContext .= "\n**Source " . ($index + 1) . ":**\n";
                    $searchContext .= "Titre: " . $result['title'] . "\nLien: " . $result['link'] . "\nExtrait: " . $result['snippet'] . "\n";
                }
                $searchContext .= "\n**INSTRUCTIONS:** Utilise ces informations pour répondre.\n";
            }
        }
        
        $enhancedMessage = $userMessage . $searchContext;
        
        if ($model === "cosmosrp") {
            $ch = curl_init("https://api.pawan.krd/cosmosrp/v1/chat/completions");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "model" => "cosmosrp",
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
            if (isset($data["choices"][0]["message"]["content"])) {
                $response["message"] = $data["choices"][0]["message"]["content"];
                $response["success"] = true;
                
                if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $matches)) {
                    $response["browserCommand"] = ["action" => $matches[1], "param" => $matches[2]];
                }
                if (preg_match('/\[YOUTUBE:SEARCH:([^\]]*)\]/', $response["message"], $matches)) {
                    $youtubeQuery = $matches[1];
                    $ytResults = youtubeSearch($youtubeQuery, 5);
                    if ($ytResults['success']) {
                        $response["youtubeResults"] = $ytResults['results'];
                    }
                }
            }
        } else if ($model === "c4ai") {
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
            } elseif (isset($data["text"])) {
                $response["message"] = $data["text"];
                $response["success"] = true;
            }
            
            if (isset($response["message"])) {
                if (preg_match('/\[BROWSER:(OPEN|SEARCH|CLOSE):([^\]]*)\]/', $response["message"], $matches)) {
                    $response["browserCommand"] = ["action" => $matches[1], "param" => $matches[2]];
                }
                if (preg_match('/\[YOUTUBE:SEARCH:([^\]]*)\]/', $response["message"], $matches)) {
                    $youtubeQuery = $matches[1];
                    $ytResults = youtubeSearch($youtubeQuery, 5);
                    if ($ytResults['success']) {
                        $response["youtubeResults"] = $ytResults['results'];
                    }
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
<title>JARVIS AI — Image Recognition + YouTube</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
    --accent: #00eaff;
    --bg-dark: #020610;
    --panel-bg: rgba(0, 255, 255, 0.06);
    --border-color: rgba(0, 255, 255, 0.15);
    --red-glow: #ff0040;
    --youtube-red: #ff0000;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: var(--bg-dark);
    color: var(--accent);
    font-family: "Orbitron", Arial, sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    padding-top: 320px;
}

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
    box-shadow: 0 0 30px rgba(0, 234, 255, 0.3);
    z-index: 1000;
}

.jarvis-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.jarvis-visual.active img {
    opacity: 1;
    filter: brightness(1.2);
}

.jarvis-visual::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(0, 234, 255, 0.1));
    pointer-events: none;
}

.jarvis-visual::after {
    content: 'JARVIS AI';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 3rem;
    font-weight: 700;
    color: var(--accent);
    text-shadow: 0 0 20px rgba(0, 234, 255, 0.8);
    opacity: 1;
    transition: opacity 0.5s ease;
    letter-spacing: 5px;
}

.jarvis-visual.active::after {
    opacity: 0;
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
    text-shadow: 0 0 10px rgba(0, 234, 255, 0.5);
}

#chatWindow {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 15px;
    height: 400px;
    overflow-y: auto;
    margin-bottom: 15px;
    scroll-behavior: smooth;
}

#chatWindow::-webkit-scrollbar { width: 8px; }
#chatWindow::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); border-radius: 10px; }
#chatWindow::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }

.msg-user {
    background: rgba(0, 234, 255, 0.15);
    padding: 12px 15px;
    border-radius: 15px 15px 5px 15px;
    margin: 10px 0;
    text-align: right;
    max-width: 85%;
    margin-left: auto;
    border: 1px solid rgba(0, 234, 255, 0.3);
    animation: slideInRight 0.3s ease;
}

.msg-jarvis {
    background: rgba(255, 255, 255, 0.1);
    padding: 12px 15px;
    border-radius: 15px 15px 15px 5px;
    margin: 10px 0;
    text-align: left;
    max-width: 85%;
    margin-right: auto;
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: slideInLeft 0.3s ease;
}

.uploaded-image-preview {
    max-width: 300px;
    max-height: 200px;
    border-radius: 10px;
    margin-top: 10px;
    border: 2px solid var(--accent);
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.3);
}

.image-analysis-card {
    background: rgba(0, 234, 255, 0.1);
    border: 2px solid rgba(0, 234, 255, 0.3);
    border-radius: 15px;
    padding: 15px;
    margin: 15px 0;
    animation: slideInLeft 0.5s ease;
}

.image-analysis-header {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.analysis-item {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(0, 234, 255, 0.2);
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
}

.analysis-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 5px;
}

.analysis-value {
    font-size: 0.95rem;
    color: #fff;
    font-weight: 600;
}

.youtube-results {
    background: rgba(255, 0, 0, 0.1);
    border: 2px solid rgba(255, 0, 0, 0.3);
    border-radius: 15px;
    padding: 15px;
    margin: 15px 0;
    animation: slideInLeft 0.5s ease;
}

.youtube-results-header {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--youtube-red);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.youtube-video-card {
    background: rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 0, 0, 0.2);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    gap: 12px;
    align-items: start;
}

.youtube-video-card:hover {
    background: rgba(255, 0, 0, 0.1);
    border-color: var(--youtube-red);
    transform: translateX(5px);
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.3);
}

.youtube-thumbnail {
    width: 120px;
    height: 90px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.youtube-video-info {
    flex: 1;
    min-width: 0;
}

.youtube-video-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.youtube-channel {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 5px;
}

.youtube-play-btn {
    background: var(--youtube-red);
    color: #fff;
    border: none;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: "Orbitron", Arial;
}

.youtube-play-btn:hover {
    background: #cc0000;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
}

.image-upload-area {
    background: rgba(0, 234, 255, 0.05);
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-upload-area:hover {
    background: rgba(0, 234, 255, 0.1);
    border-color: var(--accent);
}

.image-upload-area.dragover {
    background: rgba(0, 234, 255, 0.2);
    border-color: var(--accent);
    transform: scale(1.02);
}

#imagePreview {
    max-width: 100%;
    max-height: 150px;
    border-radius: 10px;
    margin-top: 10px;
    display: none;
    border: 2px solid var(--accent);
}

.remove-image-btn {
    background: var(--red-glow);
    color: #fff;
    border: none;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-top: 10px;
    cursor: pointer;
    font-family: "Orbitron", Arial;
    display: none;
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes blink {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.typing {
    border-right: 3px solid var(--accent);
    animation: blink 1s infinite;
}

.dots span { animation: blink 1.5s infinite; }
.dots span:nth-child(2) { animation-delay: 0.3s; }
.dots span:nth-child(3) { animation-delay: 0.6s; }

.voice-btn {
    background: linear-gradient(135deg, var(--red-glow), #cc0033);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 15px;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(255, 0, 64, 0.4);
}

.voice-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 0, 64, 0.6);
}

.voice-btn.listening {
    animation: pulse 1s infinite;
    background: linear-gradient(135deg, #ff0040, #ff3366);
}

.form-control, .form-select {
    background: rgba(0, 0, 0, 0.6) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--accent) !important;
    border-radius: 10px !important;
    padding: 12px !important;
    font-family: "Orbitron", Arial;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.5) !important;
    border-color: var(--accent) !important;
}

.btn-send {
    background: linear-gradient(135deg, #00eaff, #0088cc);
    border: none;
    color: #000;
    font-weight: 700;
    padding: 12px 30px;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-transform: uppercase;
}

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 234, 255, 0.6);
}

.btn-send:disabled { opacity: 0.6; cursor: not-allowed; }

.status-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0, 234, 255, 0.1);
}

.status-label { color: rgba(255, 255, 255, 0.7); }
.status-value { color: #8bffcf; font-weight: 700; }

@media (min-width: 768px) {
    .jarvis-visual { height: 400px; }
    body { padding-top: 420px; }
    #chatWindow { height: 500px; }
    .jarvis-visual::after { font-size: 4rem; }
}

@media (min-width: 992px) {
    .jarvis-visual { height: 500px; }
    body { padding-top: 520px; }
    .jarvis-visual::after { font-size: 5rem; }
}

@media (max-width: 576px) {
    .jarvis-visual { height: 250px; }
    body { padding-top: 270px; }
    #chatWindow { height: 350px; }
    .voice-btn { width: 60px; height: 60px; font-size: 1.5rem; }
    .jarvis-visual::after { font-size: 2rem; letter-spacing: 3px; }
    .youtube-thumbnail { width: 80px; height: 60px; }
    .youtube-video-title { font-size: 0.85rem; }
}
</style>
</head>
<body>

<div class="jarvis-visual" id="jarvisGif">
    <img src="jarvis.gif" alt="JARVIS Interface">
</div>

<div class="main-container">
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel">
                <div class="panel-header">💬 JARVIS AI + 🖼️ IMAGE RECOGNITION + 🎬 YOUTUBE</div>
                
                <!-- Upload Immagine -->
                <div class="image-upload-area" id="uploadArea">
                    <input type="file" id="imageInput" accept="image/*" style="display: none;">
                    <div id="uploadText">
                        📸 <strong>Clicca o trascina un'immagine qui</strong><br>
                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">JARVIS analizzerà l'immagine automaticamente</span>
                    </div>
                    <img id="imagePreview" alt="Preview">
                    <button class="remove-image-btn" id="removeImageBtn">✕ Rimuovi</button>
                </div>
                
                <div id="chatWindow">
                    <div class="msg-jarvis">
                        👋 Bonjour, je suis JARVIS. Vous pouvez me parler naturellement dans n'importe quelle langue !<br><br>
                        🎬 <strong>YouTube:</strong> Je peux chercher des vidéos pour vous<br>
                        🖼️ <strong>NOUVEAU - Analyse d'images:</strong> Téléchargez une image et je l'analyserai avec Google Vision API !<br><br>
                        Exemple: Uploadez une photo d'un monument et je vous dirai ce que c'est !
                    </div>
                </div>
                <form id="chatForm">
                    <div class="mb-3">
                        <input type="text" id="messageInput" class="form-control" placeholder="Tapez votre message (ou uploadez juste une image)..." autocomplete="off">
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7 col-md-5">
                            <select id="modelSelect" class="form-select">
                                <option value="c4ai">🤖 C4AI Aya Expanse 32B</option>
                                <option value="cosmosrp">🌌 CosmosRP</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="voiceBtn" class="voice-btn" title="Reconnaissance vocale">🎤</button>
                        </div>
                        <div class="col">
                            <button type="submit" id="sendBtn" class="btn btn-send w-100">▶ Envoyer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="panel">
                <div class="panel-header">⚙️ SYSTÈME</div>
                <div class="status-item">
                    <span class="status-label">Statut</span>
                    <span class="status-value">🟢 En ligne</span>
                </div>
                <div class="status-item">
                    <span class="status-label">GIF JARVIS</span>
                    <span class="status-value" id="gifStatus">⚫ Fermo</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Vision API</span>
                    <span class="status-value">✅ Active</span>
                </div>
                <div class="status-item">
                    <span class="status-label">YouTube API</span>
                    <span class="status-value">✅ Active</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Modèle actuel</span>
                    <span class="status-value" id="currentModel">C4AI Aya Expanse 32B</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Synthèse vocale</span>
                    <span class="status-value" id="voiceStatus">🔄 Chargement...</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Reconnaissance vocale</span>
                    <span class="status-value" id="speechStatus">🔄 Vérification...</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Messages envoyés</span>
                    <span class="status-value" id="msgCount">0</span>
                </div>
                <div class="status-item">
                    <button onclick="testVoice()" class="btn btn-sm" style="background: rgba(0,234,255,0.2); border: 1px solid var(--accent); color: var(--accent); padding: 5px 15px; border-radius: 8px; font-size: 0.85rem; width: 100%;">
                        🔊 Tester la voix
                    </button>
                </div>
                <hr style="border-color: var(--border-color); margin: 20px 0;">
                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.6;">
                    <strong style="color: var(--accent);">💬 Commandes:</strong><br>
                    • "Ouvre YouTube"<br>
                    • "Cherche des vidéos de cuisine"<br>
                    • Upload un'immagine<br>
                    • "Trova video su questa immagine"<br>
                    <br>
                    <strong style="color: #00ff00;">🖼️ Vision API:</strong><br>
                    Riconoscimento oggetti, testi, monumenti, loghi e ricerca inversa!
                </div>
            </div>
        </div>
    </div>
</div>

<div id="browserNotification" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 10000; background: rgba(0, 234, 255, 0.95); color: #000; padding: 20px; border-radius: 15px; box-shadow: 0 5px 30px rgba(0, 234, 255, 0.5); max-width: 350px; font-family: 'Orbitron', Arial;">
    <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 10px;">
        🌐 JARVIS - Contrôle Navigateur
    </div>
    <div id="browserNotificationText" style="margin-bottom: 15px; line-height: 1.5;">
    </div>
    <button onclick="closeBrowserNotification()" style="background: #000; color: var(--accent); border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-family: 'Orbitron', Arial;">
        ✓ OK, Compris
    </button>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=A0SDeHMK"></script>

<script>
let messageCount = 0;
let voiceReady = false;
let recognition = null;
let isListening = false;
let uploadedImageData = null;
const jarvisGif = document.getElementById('jarvisGif');
const gifStatus = document.getElementById('gifStatus');

function activateJarvisGif() {
    jarvisGif.classList.add('active');
    gifStatus.textContent = '✨ Attivo';
    gifStatus.style.color = '#00ff00';
}

function deactivateJarvisGif() {
    jarvisGif.classList.remove('active');
    gifStatus.textContent = '💤 In attesa';
    gifStatus.style.color = '#8bffcf';
}

// IMAGE UPLOAD
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const removeImageBtn = document.getElementById('removeImageBtn');
const uploadText = document.getElementById('uploadText');

uploadArea.addEventListener('click', () => imageInput.click());

imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) handleImageFile(file);
});

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        handleImageFile(file);
    }
});

function handleImageFile(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImageData = e.target.result;
        imagePreview.src = uploadedImageData;
        imagePreview.style.display = 'block';
        removeImageBtn.style.display = 'inline-block';
        uploadText.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

removeImageBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    uploadedImageData = null;
    imagePreview.src = '';
    imagePreview.style.display = 'none';
    removeImageBtn.style.display = 'none';
    uploadText.style.display = 'block';
    imageInput.value = '';
});

// VOICE RECOGNITION
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    document.getElementById('speechStatus').innerHTML = '✅ Disponible';
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById('messageInput').value = transcript;
        setTimeout(() => document.getElementById('chatForm').dispatchEvent(new Event('submit')), 500);
    };
    
    recognition.onend = function() {
        isListening = false;
        document.getElementById('voiceBtn').classList.remove('listening');
    };
} else {
    document.getElementById('speechStatus').innerHTML = '❌ Non supporté';
    document.getElementById('voiceBtn').disabled = true;
}

document.getElementById('voiceBtn').onclick = function() {
    if (!recognition) return;
    if (isListening) {
        recognition.stop();
        isListening = false;
        this.classList.remove('listening');
    } else {
        recognition.start();
        isListening = true;
        this.classList.add('listening');
    }
};

// VOICE SYNTHESIS
window.addEventListener('load', function() {
    setTimeout(() => {
        if (typeof responsiveVoice !== 'undefined') {
            voiceReady = true;
            document.getElementById('voiceStatus').innerHTML = '🔊 Prête';
        }
    }, 2000);
});

function speakJarvis(text) {
    const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').replace(/\[YOUTUBE:[^\]]+\]/g, '').trim();
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        responsiveVoice.speak(cleanText, "French Male", {pitch: 1, rate: 0.95, volume: 1});
    }
}

function testVoice() {
    speakJarvis("Bonjour, je suis JARVIS. Vision API et YouTube sont opérationnels.");
}

function executeBrowserCommand(command) {
    if (!command) return;
    if (command.action === 'OPEN') {
        window.open(command.param, '_blank');
        showBrowserNotification(`✅ Page ouverte: ${command.param}`);
    } else if (command.action === 'SEARCH') {
        window.open('https://www.google.com/search?q=' + encodeURIComponent(command.param), '_blank');
        showBrowserNotification(`🔍 Recherche: ${command.param}`);
    }
}

function showBrowserNotification(message) {
    document.getElementById('browserNotificationText').innerHTML = message;
    document.getElementById('browserNotification').style.display = 'block';
    setTimeout(() => closeBrowserNotification(), 8000);
}

function closeBrowserNotification() {
    document.getElementById('browserNotification').style.display = 'none';
}

function displayImageAnalysis(analysis, chatWindow) {
    if (!analysis || !analysis.success) return;
    
    const analysisDiv = document.createElement('div');
    analysisDiv.className = 'image-analysis-card';
    
    let html = '<div class="image-analysis-header">🖼️ Analyse d\'image Google Vision</div>';
    
    if (analysis.bestGuess) {
        html += `<div class="analysis-item">
            <div class="analysis-label">🔍 Identification principale:</div>
            <div class="analysis-value">${analysis.bestGuess}</div>
        </div>`;
    }
    
    if (analysis.labels && analysis.labels.length > 0) {
        html += `<div class="analysis-item">
            <div class="analysis-label">📋 Objets détectés:</div>
            <div class="analysis-value">`;
        analysis.labels.slice(0, 5).forEach(label => {
            html += `${label.description} (${label.score}%), `;
        });
        html = html.slice(0, -2) + `</div></div>`;
    }
    
    if (analysis.landmarks && analysis.landmarks.length > 0) {
        html += `<div class="analysis-item">
            <div class="analysis-label">🏛️ Monuments/Lieux:</div>
            <div class="analysis-value">${analysis.landmarks.join(', ')}</div>
        </div>`;
    }
    
    if (analysis.logos && analysis.logos.length > 0) {
        html += `<div class="analysis-item">
            <div class="analysis-label">🏢 Logos/Marques:</div>
            <div class="analysis-value">${analysis.logos.join(', ')}</div>
        </div>`;
    }
    
    if (analysis.text) {
        html += `<div class="analysis-item">
            <div class="analysis-label">📝 Texte détecté:</div>
            <div class="analysis-value">${analysis.text.substring(0, 200)}...</div>
        </div>`;
    }
    
    analysisDiv.innerHTML = html;
    chatWindow.appendChild(analysisDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function displayYoutubeResults(results, chatWindow) {
    if (!results || results.length === 0) return;
    
    const ytDiv = document.createElement('div');
    ytDiv.className = 'youtube-results';
    
    let html = '<div class="youtube-results-header">🎬 Résultats YouTube</div>';
    
    results.forEach((video, index) => {
        html += `
            <div class="youtube-video-card" onclick="window.open('${video.url}', '_blank')">
                <img src="${video.thumbnail}" alt="Thumbnail" class="youtube-thumbnail">
                <div class="youtube-video-info">
                    <div class="youtube-video-title">${video.title}</div>
                    <div class="youtube-channel">📺 ${video.channelTitle}</div>
                    <button class="youtube-play-btn" onclick="event.stopPropagation(); window.open('${video.url}', '_blank')">
                        ▶ Regarder
                    </button>
                </div>
            </div>
        `;
    });
    
    ytDiv.innerHTML = html;
    chatWindow.appendChild(ytDiv);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

function typeWriter(text, element) {
    let index = 0;
    element.classList.add('typing');
    const cleanText = text.replace(/\[BROWSER:[^\]]+\]/g, '').replace(/\[YOUTUBE:[^\]]+\]/g, '').trim();
    
    activateJarvisGif();
    
    if (typeof responsiveVoice !== 'undefined' && voiceReady) {
        responsiveVoice.cancel();
        responsiveVoice.speak(cleanText, "French Male", {
            pitch: 1,
            rate: 0.95,
            volume: 1,
            onend: () => {
                voiceFinished = true;
                checkEnd();
            }
        });
    }
    
    function type() {
        if (index < text.length) {
            element.textContent += text.charAt(index);
            index++;
            element.parentElement.parentElement.scrollTop =
                element.parentElement.parentElement.scrollHeight;
            setTimeout(type, 20);
        } else {
            element.classList.remove('typing');
            typingFinished = true;
            checkEnd();
        }
    }
    
    let typingFinished = false;
    let voiceFinished = false;
    
    function checkEnd() {
        if (typingFinished && voiceFinished) {
            setTimeout(() => {
                deactivateJarvisGif();
            }, 300);
        }
    }
    
    type();
}

document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const messageInput = document.getElementById('messageInput');
    const modelSelect = document.getElementById('modelSelect');
    const sendBtn = document.getElementById('sendBtn');
    const chatWindow = document.getElementById('chatWindow');
    
    const userMessage = messageInput.value.trim();
    const selectedModel = modelSelect.value;

    if (!userMessage && !uploadedImageData) return;

    sendBtn.disabled = true;
    sendBtn.textContent = '⏳ Envoi...';

    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;

    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'msg-user';
    userMsgDiv.textContent = userMessage || "📸 [Image uploadée]";
    
    if (uploadedImageData) {
        const imgPreview = document.createElement('img');
        imgPreview.src = uploadedImageData;
        imgPreview.className = 'uploaded-image-preview';
        userMsgDiv.appendChild(document.createElement('br'));
        userMsgDiv.appendChild(imgPreview);
    }
    
    chatWindow.appendChild(userMsgDiv);

    activateJarvisGif();

    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'msg-jarvis';
    thinkingDiv.innerHTML = uploadedImageData ? 
        '🔍 JARVIS analyse l\'image <span class="dots"><span>.</span><span>.</span><span>.</span></span>' :
        '🤔 JARVIS réfléchit <span class="dots"><span>.</span><span>.</span><span>.</span></span>';
    chatWindow.appendChild(thinkingDiv);

    chatWindow.scrollTop = chatWindow.scrollHeight;

    const modelNames = {
        'c4ai': 'C4AI Aya Expanse 32B',
        'cosmosrp': 'CosmosRP'
    };
    document.getElementById('currentModel').textContent = modelNames[selectedModel];

    messageInput.value = '';

    try {
        const formData = new FormData();
        formData.append('message', userMessage);
        formData.append('model', selectedModel);
        formData.append('ajax', 'true');
        
        if (uploadedImageData) {
            formData.append('image', uploadedImageData);
        }

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        thinkingDiv.remove();
        
        // Mostra analisi immagine se presente
        if (data.imageAnalysis) {
            displayImageAnalysis(data.imageAnalysis, chatWindow);
        }

        const jarvisMsgDiv = document.createElement('div');
        jarvisMsgDiv.className = 'msg-jarvis';
        const typingSpan = document.createElement('span');
        jarvisMsgDiv.appendChild(typingSpan);
        chatWindow.appendChild(jarvisMsgDiv);

        const displayMessage = data.message.replace(/\[BROWSER:[^\]]+\]/g, '').replace(/\[YOUTUBE:[^\]]+\]/g, '').trim();
        
        typeWriter(displayMessage, typingSpan);

        if (data.youtubeResults && data.youtubeResults.length > 0) {
            setTimeout(() => displayYoutubeResults(data.youtubeResults, chatWindow), 500);
        }

        if (data.browserCommand) {
            setTimeout(() => executeBrowserCommand(data.browserCommand), 1000);
        }

        chatWindow.scrollTop = chatWindow.scrollHeight;
        
        // Reset image
        if (uploadedImageData) {
            uploadedImageData = null;
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            removeImageBtn.style.display = 'none';
            uploadText.style.display = 'block';
            imageInput.value = '';
        }

    } catch (error) {
        thinkingDiv.innerHTML = '❌ Erreur : ' + error.message;
        deactivateJarvisGif();
        console.error('Erreur:', error);
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = '▶ Envoyer';
        messageInput.focus();
    }
});

document.getElementById('messageInput').focus();

console.log('🚀 JARVIS AI avec Vision API + YouTube initialisé !');
</script>
</body>
</html>
