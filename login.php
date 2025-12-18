<?php

session_start();


require_once 'config.php';

$mensagem = "";
$tipo_mensagem = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $tipo_mensagem = "erro";
    } else {
        
        $stmt = $conn->prepare("SELECT id_utilizador, nome, password, isAdmin FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nome, $hash_password, $isAdmin);
            $stmt->fetch();

            
            if (password_verify($password, $hash_password)) {
                
                $_SESSION['loggedin'] = true;
                $_SESSION['id_utilizador'] = $id;
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $_SESSION['isAdmin'] = $isAdmin;
                header("Location: index.php");
                exit;
            } else {
                $mensagem = "Password incorreta.";
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Não existe conta com esse email.";
            $tipo_mensagem = "erro";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PedeJá</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .mensagem {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9em;
        }
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="contentor-login">
        <div class="lado-esquerdo">
            <div class="marca">PedeJá</div>
            <div class="subtitulo">A passar por cá outra vez? - Vamos lá</div>
            
            <hr>

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="grupo-input">
                    <div class="linha-label">
                        <label for="email">Email</label>
                    </div>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="grupo-input">
                    <div class="linha-label">
                        <label for="password">Password</label>
                        <a href="#" class="link-esqueci-senha">Esqueci-me da password</a>
                    </div>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="botao-entrar">Entrar</button>

                <div class="link-rodape">
                    Não tens conta criada? <a href="registo.php">Regista-te</a> </div>
            </form>
        </div>

        <div class="lado-direito">
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>