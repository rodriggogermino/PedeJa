<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nome_utilizador = $_SESSION['nome'];
$id_utilizador = $_SESSION['id_utilizador'];
$isAdmin = $_SESSION['isAdmin'];
$cargo = ($isAdmin == 1) ? "Administrador" : "Aluno";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_pedido = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, u.nome AS nome_cliente 
    FROM pedido p 
    JOIN utilizadores u ON p.utilizador_id = u.id_utilizador 
    WHERE p.id_pedido = ? AND p.utilizador_id = ?
");
$stmt->bind_param("ii", $id_pedido, $id_utilizador);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
if (!$pedido) {
    echo "<p style='text-align:center; padding:50px;'>Pedido não encontrado ou sem permissão para visualizar.</p>";
    echo "<div style='text-align:center;'><a href='index.php'>Voltar</a></div>";
    exit;
}
$stmt_artigos = $conn->prepare("
    SELECT pa.*, a.nome, a.imagem, a.desc_artigo 
    FROM pedido_artigos pa 
    JOIN artigos a ON pa.artigo_id = a.id_artigos 
    WHERE pa.pedido_id = ?
");
$stmt_artigos->bind_param("i", $id_pedido);
$stmt_artigos->execute();
$result_artigos = $stmt_artigos->get_result();

$total_pedido = 0;
$itens = [];
while ($row = $result_artigos->fetch_assoc()) {
    $total_pedido += ($row['preco_unitario_historico'] * $row['quantidade']);
    $itens[] = $row;
}
$estado = $pedido['estado'];
$passo1_class = "ativo";
$linha1_class = "";
$passo2_class = "";
$linha2_class = "";
$passo3_class = "";
$cor_texto = "";

if ($estado == 'Em Preparação') {
    $linha1_class = "ativo";
    $passo2_class = "ativo";
} elseif ($estado == 'Entregue' || $estado == 'Concluido') {
    $linha1_class = "ativo";
    $passo2_class = "ativo";
    $linha2_class = "ativo";
    $passo3_class = "ativo";
    $cor_texto = "color: #e85d04;";
}
$total_carrinho = isset($_SESSION['carrinho']) ? array_sum($_SESSION['carrinho']) : 0;
$display_badge = ($total_carrinho > 0) ? 'inline-block' : 'none';
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido - PedeJá</title>
    <link rel="stylesheet" href="css/ver_pedidoAdmin.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-atualizar { display: none !important; }
        .seccao-estado {
            justify-content: center;
            padding-bottom: 20px;
        }
    </style>
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
                    <strong><?php echo htmlspecialchars($nome_utilizador); ?></strong>
                    <span><?php echo $cargo; ?></span>
                </div>
            </div>

            <nav class="menu-navegacao">
                <div class="etiqueta-menu">Menu</div>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="artigos.php">Encomendar</a></li>
                    <li><a href="historicoAluno.html" class="ativo">Histórico</a></li>
                    <li>
                        <a href="carrinho.php">
                            Carrinho 
                            <span id="badge-carrinho" style="background:red; color:white; padding:2px 6px; border-radius:10px; font-size:12px; display: <?php echo $display_badge; ?>;">
                                <?php echo $total_carrinho; ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="area-sair">
                <a href="logout.php">Sair <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>


        <main class="conteudo-principal">

            <header class="cabecalho-pagina">
                <h2>Olá, <br><strong><?php echo htmlspecialchars($nome_utilizador); ?>!</strong></h2>
            </header>

            <div class="caixa-conteudo">

                <div class="grelha-detalhes">

                    <div class="coluna-esquerda">

                        <h3 class="titulo-seccao">Estado do<br>pedido</h3>

                        <div class="seccao-estado">
                            <span class="texto-estado" style="<?php echo $cor_texto; ?>"><?php echo htmlspecialchars($estado); ?></span>

                            <div class="barra-progresso">
                                <div class="passo <?php echo $passo1_class; ?>" title="Pendente"></div>
                                <div class="linha <?php echo $linha1_class; ?>"></div>
                                <div class="passo <?php echo $passo2_class; ?>" title="Em Preparação"></div>
                                <div class="linha <?php echo $linha2_class; ?>"></div>
                                <div class="passo <?php echo $passo3_class; ?>" title="Entregue"></div>
                            </div>
                        </div>

                        <div class="info-pedido">
                            <p class="linha-info">Total: <strong><?php echo number_format($total_pedido, 2); ?>€</strong></p>
                            <p class="linha-info">ID Pedido: <strong><?php echo $pedido['id_pedido']; ?></strong></p>
                            <p class="linha-info">Nome: <strong><?php echo htmlspecialchars($pedido['nome_cliente']); ?></strong></p>
                            <p class="linha-info">Hora agendada: <strong><?php echo date('H:i', strtotime($pedido['hora_Agendada'])); ?></strong></p>
                            <p class="linha-info">Pagamento: <strong><?php echo htmlspecialchars($pedido['metodo_Pagamento']); ?></strong></p>
                        </div>

                    </div>

                    <div class="coluna-direita">
                        <h3 class="titulo-seccao">Artigos pedidos</h3>

                        <div class="lista-artigos">
                            <?php foreach ($itens as $item): ?>
                                <div class="cartao-artigo">
                                    <div class="img-artigo">
                                        <img src="<?php echo !empty($item['imagem']) ? $item['imagem'] : 'images/default.png'; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                                    </div>
                                    <div class="detalhes-artigo">
                                        <h4><?php echo htmlspecialchars($item['nome']); ?></h4>
                                        <span class="desc-artigo"><?php echo htmlspecialchars($item['desc_artigo']); ?></span>
                                    </div>
                                    <div class="preco-qty-artigo">
                                        <span class="preco"><?php echo number_format($item['preco_unitario_historico'], 2); ?> €</span>
                                        <span class="distintivo-qty"><?php echo $item['quantidade']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

</body>
</html>