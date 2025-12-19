<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nome_utilizador = $_SESSION['nome'];
$isAdmin = $_SESSION['isAdmin'];
$cargo = ($isAdmin == 1) ? "Administrador" : "Aluno";
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PedeJá</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="contentor-dashboard">
        
        <aside class="barra-lateral">
            <div class="marca">
                <h1><a href="index.php" style="text-decoration: none; color: inherit;">PedeJá</a></h1>
            </div>

            <div class="perfil-utilizador">
                <div class="circulo-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="info-utilizador">
                    <strong><?php echo htmlspecialchars($nome_utilizador); ?></strong>
                    <span><?php echo $cargo; ?></span>
                </div>
            </div>

            <nav class="menu-navegacao">
                <div class="etiqueta-menu">Menu</div>
                <ul>
                    <li><a href="index.php" class="ativo">Início</a></li>
                    
                    <?php if ($isAdmin == 1): ?>
                        <li><a href="artigosAdmin.php">Artigos</a></li>
                        <li><a href="stockAdmin.php">Stock</a></li>
                        <li><a href="historicoAdmin.php">Histórico</a></li>
                        <li><a href="pedidosAdmin.php">Pedidos</a></li>
                    <?php else: ?>
                        <li><a href="artigosAluno.html">Encomendar</a></li>
                        <li><a href="historicoAluno.html">Histórico</a></li>
                        <li><a href="carrinhoAluno.html">Carrinho</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="area-sair">
                <a href="logout.php">Sair <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>


        <main class="conteudo-principal">
            <header class="cabecalho-topo">
                <div class="saudacao">
                    <h2>Olá, <br><strong><?php echo htmlspecialchars($nome_utilizador); ?>!</strong></h2>
                </div>
                <?php if ($isAdmin == 1): ?>
                    <div class="etiqueta-modo">Modo ADMIN</div>
                <?php endif; ?>
            </header>

            <div class="seccao-destaque">
                <?php if ($isAdmin == 1): ?>
                    <div class="cartoes-acao" style="display: flex; gap: 20px;">
                        <div class="cartao cartao-laranja" onclick="location.href='stockAdmin.php'">
                            <div class="icone-topo"><i class="fa-solid fa-arrow-right"></i></div>
                            <div class="texto-cartao">Editar Stock</div>
                        </div>
                        <div class="cartao cartao-amarelo" onclick="location.href='artigosAdmin.php'">
                            <div class="icone-topo"><i class="fa-solid fa-arrow-right"></i></div>
                            <div class="texto-cartao">Ver Artigos</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 20px; width: 100%;">
                        <div class="texto-destaque">
                            <span class="texto-leve">Sem</span> <strong>pedidos<br>recentes</strong>
                        </div>
                        <div class="cartao cartao-amarelo" onclick="location.href='artigosAluno.html'">
                            <div class="icone-topo"><i class="fa-solid fa-arrow-right"></i></div>
                            <div class="texto-cartao">Ver Artigos</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="seccao-historico">
                <div class="cabecalho-historico">
                    <h3>Histórico de Pedidos</h3> 
                    <div class="controlos">
                        <div class="caixa-pesquisa">
                            <input type="text" placeholder="Pesquisar">
                        </div>
                        <button class="botao-icone"><i class="fa-solid fa-bars"></i></button>
                        <button class="botao-icone"><i class="fa-solid fa-filter"></i></button>
                    </div>
                </div>

                <div class="lista-pedidos">
                    <?php if ($isAdmin == 1): ?>
                        <div class="linha-pedido">
                            <div class="coluna"><strong>PEDIDO:</strong> 4</div>
                            <div class="coluna"><strong>DATA:</strong> 2025/10/14</div>
                            <div class="coluna"><strong>CLIENTE:</strong> BERNARDO M.</div>
                            <div class="coluna estado-verde">ENTREGUE <i class="fa-solid fa-check"></i></div>
                            <div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>
                        </div>
                        <div class="linha-pedido">
                            <div class="coluna"><strong>PEDIDO:</strong> 3</div>
                            <div class="coluna"><strong>DATA:</strong> 2025/10/14</div>
                            <div class="coluna"><strong>CLIENTE:</strong> SANDRO F.</div>
                            <div class="coluna estado-verde">ENTREGUE <i class="fa-solid fa-check"></i></div>
                            <div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>
                        </div>
                    <?php else: ?>
                        <div class="linha-pedido">
                            <div class="coluna"><strong>PEDIDO:</strong> 3</div>
                            <div class="coluna"><strong>DATA:</strong> 2025/10/14</div>
                            <div class="coluna estado-verde">ENTREGUE <i class="fa-solid fa-check"></i></div>
                            <div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>
                        </div>
                        <div class="linha-pedido">
                            <div class="coluna"><strong>PEDIDO:</strong> 2</div>
                            <div class="coluna"><strong>DATA:</strong> 2025/09/24</div>
                            <div class="coluna estado-verde">ENTREGUE <i class="fa-solid fa-check"></i></div>
                            <div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>


        <aside class="painel-direito">
            
            <?php if ($isAdmin == 1): ?>
                <div class="cabecalho-painel">
                    <h3>Pedidos<br>Recentes</h3>
                    <i class="fa-regular fa-clock icone-cabecalho"></i>
                </div>

                <div class="lista-recentes">
                    <div class="cartao-recente">
                        <div class="cabecalho-rc">
                            <span class="titulo-rc">Pedido nº 6</span>
                            <span class="nome-rc">Guilherme H.</span>
                        </div>
                        <div class="rodape-rc">
                            <span class="hora-rc">12h30</span>
                            <button class="botao-ver" onclick="location.href='ver_pedidoAdmin.html'">Ver</button>
                        </div>
                    </div>
                    <div class="cartao-recente">
                        <div class="cabecalho-rc">
                            <span class="titulo-rc">Pedido nº 5</span>
                            <span class="nome-rc">Cristiano R.</span>
                        </div>
                        <div class="rodape-rc">
                            <span class="hora-rc">13h30</span>
                            <button class="botao-ver" onclick="location.href='ver_pedidoAdmin.html'">Ver</button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="cabecalho-painel">
                    <h3>Favoritos</h3>
                    <i class="fa-regular fa-star icone-cabecalho"></i>
                </div>

                <div class="lista-favoritos">
                    <div class="cartao-favorito">
                        <i class="fa-solid fa-star distintivo-estrela"></i>
                        <div class="imagem-favorito">
                            <img src="images/Tostas/TostaFrango.jpg" alt="Tosta">
                        </div>
                        <h4>Tosta de Frango</h4>
                        <button class="botao-encomendar">Encomendar</button>
                    </div>

                    <div class="cartao-favorito">
                        <i class="fa-solid fa-star distintivo-estrela"></i>
                        <div class="imagem-favorito">
                            <img src="images/Bebidas/cocaCola.png" alt="CocaCola">
                        </div>
                        <h4>CocaCola</h4>
                        <button class="botao-encomendar">Encomendar</button>
                    </div>
                </div>
            <?php endif; ?>

        </aside>

    </div>
    <script src="js/main.js"></script>
</body>
</html>