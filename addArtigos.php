<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $categoria_id = $_POST["categoria"];
    $preco = $_POST["preco"];

    if (!empty($nome) && !empty($descricao) && !empty($categoria_id) && !empty($preco) && isset($_FILES['imagem'])) {

        $res = $conn->query("SELECT nome FROM categorias WHERE id_categoria = $categoria_id");
        $row = $res->fetch_assoc();
        $categoria_nome = strtolower($row['nome']);

        $pasta = "images/" . $categoria_nome . "/";
        if (!file_exists($pasta)) mkdir($pasta, 0777, true);

        $imagem = $_FILES["imagem"]["name"];
        $tmp = $_FILES["imagem"]["tmp_name"];
        $destino = $pasta . $imagem;
        move_uploaded_file($tmp, $destino);

        $sql = "INSERT INTO artigos (nome, desc_artigo, categoria_id, preco, imagem)
                VALUES ('$nome', '$descricao', $categoria_id, '$preco', '$destino')";
        $conn->query($sql);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Artigo - PedeJá</title>
    <link rel="stylesheet" href="css/artigos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

<div class="contentor-dashboard">

    <aside class="barra-lateral">
        <div class="marca">
            <h1><a href="index.php">PedeJá</a></h1>
        </div>

        <div class="perfil-utilizador">
            <div class="circulo-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="info-utilizador">
                <strong>Rodrigo Germino</strong>
                <span>Administrador</span>
            </div>
        </div>

        <nav class="menu-navegacao">
            <div class="etiqueta-menu">Menu</div>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="artigosAdmin.php">Artigos</a></li>
                <li><a href="#" class="ativo">Stock</a></li>
                <li><a href="historicoAdmin.php">Histórico</a></li>
                <li><a href="pedidosAdmin.php">Pedidos</a></li>
            </ul>
        </nav>

        <div class="area-sair">
            <a href="login.php">Sair <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="conteudo-principal">

        <header class="cabecalho-pagina">
            <h2>Olá, <br><strong>Rodrigo!</strong></h2>
        </header>

        <div class="caixa-conteudo">

            <h3 class="titulo-seccao">Adicionar Artigo</h3>

            <form id="form-artigo" method="POST" enctype="multipart/form-data">
                <div class="layout-formulario">

                    <div class="zona-upload-imagem">
                        <input type="file" id="file-upload" name="imagem" hidden required>
                        <label for="file-upload" class="placeholder-upload">
                            <i class="fa-solid fa-cloud-arrow-up icone-upload"></i>
                            <p>Selecione uma imagem através <br> do seu computador</p>
                        </label>
                        <img id="preview" style="display:none;">
                    </div>

                    <div class="zona-campos">

                        <div class="erro" id="erro"></div>

                        <div class="grupo-input">
                            <label>Nome do Artigo</label>
                            <input type="text" name="nome" class="input-estilizado" required>
                        </div>

                        <div class="grupo-input">
                            <label>Descrição do Artigo</label>
                            <textarea name="descricao" class="input-estilizado input-textarea" required></textarea>
                        </div>

                        <div class="grupo-linha">
                            <div class="grupo-input metade-largura">
                                <label>Categoria</label>
                                <div class="envoltorio-select">
                                    <select name="categoria" class="input-estilizado" required>
                                        <option value="" disabled selected>-- Selecione a categoria --</option>
                                        <?php
                                            $res = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
                                             while ($row = $res->fetch_assoc()) {
                                                echo "<option value='" . $row["id_categoria"] . "'>" . $row["nome"] . "</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="grupo-input metade-largura">
                                <label>Preço</label>
                                <input type="number" name="preco" step="0.01" class="input-estilizado texto-centrado" required>
                            </div>
                        </div>

                        <div class="linha-botoes">
                            <button class="botao botao-adicionar-form" type="submit">Adicionar</button>
                            <button class="botao botao-cancelar" type="button" onclick="window.location.href='stockAdmin.php'">Cancelar</button>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </main>
</div>

<script>
    const fileInput = document.getElementById('file-upload');
    const preview = document.getElementById('preview');
    const form = document.getElementById('form-artigo');
    const erro = document.getElementById('erro');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(e){
                preview.setAttribute('src', e.target.result);
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', function(e){
        erro.innerText = '';
        const nome = form.nome.value.trim();
        const descricao = form.descricao.value.trim();
        const categoria = form.categoria.value;
        const preco = form.preco.value;
        const imagem = fileInput.files.length;

        if(!nome || !descricao || !categoria || !preco || !imagem){
            e.preventDefault();
            erro.innerText = 'Por favor, preencha todos os campos e selecione uma imagem.';
        }
    });
</script>

</body>
</html>
