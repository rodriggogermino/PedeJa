<?php
require_once "config.php";

$sql = "
SELECT 
    a.nome,
    a.desc_artigo,
    a.preco,
    a.imagem,
    c.nome AS categoria
FROM artigos a
JOIN categorias c ON a.categoria_id = c.id_categoria
ORDER BY c.nome, a.nome
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - PedeJá</title>
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
                <li><a href="#" class="ativo">Artigos</a></li>
                <li><a href="stockAdmin.php">Stock</a></li>
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
            <h2>Olá,<br><strong>Rodrigo!</strong></h2>
        </header>

        <div class="caixa-conteudo">

            <div class="cabecalho-caixa">
                <h3>Ver artigos:</h3>
                <div class="pesquisa-pill">
                    <input type="text" placeholder="Pesquisar">
                </div>
            </div>

            <div class="filtro-categoria">
                <span class="etiqueta-cat">Categorias</span>
                <ul class="lista-cat">
                    <li data-category="frutas">Frutas</li>
                    <li data-category="salgados">Salgados</li>
                    <li data-category="doces">Doces</li>
                    <li data-category="bebidas">Bebidas</li>
                    <li data-category="sandes">Sandes</li>
                    <li data-category="outros">Outros</li>
                </ul>
            </div>

            <div class="grelha-produtos">

                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="cartao-produto" data-item="<?= strtolower($row['categoria']) ?>">
                        <div class="contentor-img">
                            <img src="<?= $row['imagem'] ?>" alt="<?= $row['nome'] ?>">
                        </div>

                        <div class="linha-laranja"></div>

                        <div class="info-produto">
                            <div class="cabecalho-produto">
                                <span class="titulo-produto"><?= $row['nome'] ?></span>
                                <span class="preco-produto">
                                    <?= number_format($row['preco'], 2, ',', '') ?> €
                                </span>
                            </div>
                            <p class="desc-produto"><?= $row['desc_artigo'] ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>

            </div>

        </div>
    </main>
</div>

<script src="js/catProduto.js"></script>
</body>
</html>
