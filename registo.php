<?php
// 1. Incluir a configuração da base de dados
require_once 'config.php';

$mensagem = "";
$tipo_mensagem = ""; // "sucesso" ou "erro"

// 2. Verificar se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolher e limpar os dados
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validação básica
    if (empty($nome) || empty($email) || empty($password)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $tipo_mensagem = "erro";
    } else {
        // 3. Verificar se o email já existe
        $stmt_check = $conn->prepare("SELECT id_utilizador FROM utilizadores WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensagem = "Este email já se encontra registado.";
            $tipo_mensagem = "erro";
        } else {
            // 4. Encriptar a password (NUNCA guardar em texto limpo)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 5. Inserir na base de dados
            // Nota: isAdmin assume 0 por defeito na BD, não precisamos de enviar
            $stmt_insert = $conn->prepare("INSERT INTO utilizadores (nome, email, password) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $nome, $email, $password_hash);

            if ($stmt_insert->execute()) {
                $mensagem = "Conta criada com sucesso! Podes fazer login.";
                $tipo_mensagem = "sucesso";
                // Opcional: Redirecionar para o login após x segundos
                // header("refresh:3;url=login.html"); 
            } else {
                $mensagem = "Ocorreu um erro ao criar a conta: " . $conn->error;
                $tipo_mensagem = "erro";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar - PedeJá</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* Pequeno estilo extra para as mensagens de erro/sucesso */
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
        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

    <div class="contentor-login">
        <div class="lado-esquerdo">
            <div class="marca">PedeJá</div>
            <div class="subtitulo">Primeira vez? - Estamos cá para isso.</div>
            
            <hr>

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="registo.php">
                <div class="grupo-input">
                    <div class="linha-label">
                        <label for="email">Email</label>
                    </div>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="grupo-input">
                    <div class="linha-label">
                        <label for="nome">Nome</label>
                    </div>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="grupo-input">
                    <div class="linha-label">
                        <label for="password">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="botao-entrar">Criar</button>

                <div class="link-rodape">
                    Já tens conta criada? <a href="login.html">Faz login</a>
                </div>
            </form>
        </div>

        <div class="lado-direito">
            
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>