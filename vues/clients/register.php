<?php
session_start();

// Inclusion du fichier de configuration de la base de données
require_once '../../api/config.php';

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $errors = [];
    
    // Validation des champs
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    // Vérifier si l'email ou le nom d'utilisateur existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Cet email ou nom d'utilisateur existe déjà";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification des données: " . $e->getMessage();
        }
    }
    
    // Si pas d'erreurs, traiter l'inscription
    if (empty($errors)) {
        try {
            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Génération d'un token de confirmation unique
            $token = bin2hex(random_bytes(32));

            // Insertion en base de données (ajout des champs confirmation_token et is_active)
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, username, password, confirmation_token, is_active) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nom, $prenom, $email, $username, $hashedPassword, $token]);

            // Préparation de l'email de confirmation
            $to = $email;
            $subject = "Confirmation de votre inscription";
            $confirmation_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/confirm.php?token=" . $token;
            $message = '<html><body>';
            $message .= '<h1>Bienvenue ' . htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom) . ' !</h1>';
            $message .= '<p>Merci de vous être inscrit. Veuillez confirmer votre adresse email en cliquant sur le lien ci-dessous :</p>';
            $message .= '<p><a href="' . $confirmation_link . '">Confirmer mon email</a></p>';
            $message .= '<p>Si vous n\'êtes pas à l\'origine de cette inscription, ignorez cet email.</p>';
            $message .= '</body></html>';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";

            mail($to, $subject, $message, $headers);

            $_SESSION['message'] = "Inscription réussie ! Un email de confirmation vous a été envoyé.";
            $_SESSION['message_type'] = "success";

            // Redirection vers la page d'accueil
            header("Location: ../../index.html");
            exit();

        } catch (PDOException $e) {
            $errors[] = "Erreur : " . $e->getMessage(); // À supprimer en production
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    
    <div class="register-container">
        <h2>Inscription</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Tu dois mettre un attribut id sur le form. Tu lui ajoutes la valeur que tu veux (id comme ce que tu mets dans les input). Tu crées ensuite un fichier script (register.js may be) Dès que c'est bon on continue. -->
        <form id="registerForm" method="post" action="">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Créer le compte</button>
        </form>
        
        <div class="login-link">
            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        </div>
    </div>
</body>
</html> 