<?php
session_start();

$project_url = "https://mxemardtyidrhfsnxvad.supabase.co";
$api_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im14ZW1hcmR0eWlkcmhmc254dmFkIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzI4NzkwMzQsImV4cCI6MjA4ODQ1NTAzNH0.u1eFWdodluIqZQ-_Cr5IzSNMNUE1H4GQU-oDYT4Z1oo";

$error = "";

/* LOGIN */
if(isset($_POST["login"])){

$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

$url = $project_url."/rest/v1/student?select=*";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
"apikey: $api_key",
"Authorization: Bearer $api_key"
]);

$response = curl_exec($ch);
curl_close($ch);

$students = json_decode($response, true);

$found = false;

foreach($students as $student){

$db_email = trim($student["email"]);
$db_pass = trim($student["password"]);

if($db_email == $email && $db_pass == $password){

$_SESSION["student_id"] = $student["student_id"];
$_SESSION["email"] = $db_email;

$found = true;
break;

}

}

if(!$found){
$error = "Email ou mot de passe incorrect";
}

}

/* LOGOUT */
if(isset($_GET["logout"])){

session_destroy();
header("Location: index.php");
exit();

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Agent IA Université</title>

<style>

body{
font-family:Arial;
background:#f2f2f2;
text-align:center;
margin-top:80px;
}

.box{
background:white;
width:420px;
margin:auto;
padding:30px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

input{
width:90%;
padding:10px;
margin:10px;
}

button{
padding:10px 20px;
background:#4CAF50;
color:white;
border:none;
cursor:pointer;
}

#response{
margin-top:20px;
padding:15px;
background:#eee;
border-radius:5px;
}

a{
color:blue;
}

</style>

</head>

<body>

<div class="box">

<?php if(!isset($_SESSION["student_id"])): ?>

<h2>Connexion étudiant</h2>

<form method="POST">

<input type="hidden" name="login" value="1">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Mot de passe" required>

<br>

<button type="submit">Se connecter</button>

</form>

<p style="color:red;"><?php echo $error; ?></p>

<?php else: ?>

<h3>Bienvenue <?php echo $_SESSION["email"]; ?></h3>

<a href="?logout=1">Déconnexion</a>

<hr>

<h3>Agent IA Université</h3>

<input type="text" id="question" placeholder="Posez votre question">

<br>

<button onclick="sendQuestion()">Envoyer</button>

<div id="response"></div>

<script>

function sendQuestion(){

let question = document.getElementById("question").value;

if(question == ""){
alert("Veuillez écrire une question");
return;
}

document.getElementById("response").innerHTML = "Chargement...";

fetch("https://n8n-mcda.onrender.com/webhook-test/certificat", {

method: "POST",

headers: {
"Content-Type": "application/json"
},

body: JSON.stringify({
question: question,
student_id: "<?php echo $_SESSION["student_id"]; ?>"
})

})

.then(response => response.json())

.then(data => {

console.log(data);

if(data.pdf_url){

document.getElementById("response").innerHTML =
"Certificat généré avec succès ✅ <br><br>" +
"<a href='"+data.pdf_url+"' target='_blank'>Télécharger le certificat PDF</a>";

}
else if(data.answer){

document.getElementById("response").innerHTML = data.answer;

}
else{

document.getElementById("response").innerHTML = "Pas de réponse.";

}

})

.catch(error => {

document.getElementById("response").innerHTML = "Erreur serveur";

console.error(error);

});

}

</script>

<?php endif; ?>

</div>

</body>
</html>
