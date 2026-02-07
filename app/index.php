<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === "admin" && $password === "admin123") {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = "admin";
        header("Location: jarvis.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Login — JARVIS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    background:#020610;
    color:#00eaff;
    font-family:Orbitron,Arial;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0
}
.login-box{
    background:rgba(0,255,255,.05);
    border:1px solid rgba(0,255,255,.2);
    border-radius:15px;
    padding:30px;
    width:320px;
    box-shadow:0 0 30px rgba(0,234,255,.3)
}
h1{text-align:center;margin-bottom:20px}
input,button{
    width:100%;
    padding:12px;
    margin-top:10px;
    border-radius:10px;
    border:none;
    font-size:14px
}
input{
    background:#000;
    color:#00eaff;
    border:1px solid rgba(0,255,255,.3)
}
button{
    background:#00eaff;
    color:#000;
    font-weight:700;
    cursor:pointer
}
.error{
    color:#ff3366;
    text-align:center;
    margin-top:10px
}
</style>
</head>

<body>
<div class="login-box">
<h1>🔐 JARVIS LOGIN</h1>

<form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Connexion</button>
</form>

<?php if ($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

</div>
</body>
</html>
