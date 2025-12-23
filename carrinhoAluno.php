<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id_utilizador'];
$isAdmin = $_SESSION['isAdmin'];
$cargo = ($isAdmin == 1) ? "Administrador" : "Aluno";

$mensagem = "";
$tipo_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['acao']) && $_POST['acao'] === 'remover') {
        $id_remover = intval($_POST['id_artigo']);
        if (isset($_SESSION['carrinho'][$id_remover])) {
            unset($_SESSION['carrinho'][$id_remover]);
        }
    }
    if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar_qtd') {
        $id_artigo = intval($_POST['id_artigo']);
        $nova_qtd = intval($_POST['qtd']);
        
        if ($nova_qtd > 0) {
            $_SESSION['carrinho'][$id_artigo] = $nova_qtd;
        } else {
            unset($_SESSION['carrinho'][$id_artigo]);
        }
    }
    if (isset($_POST['acao']) && $_POST['acao'] === 'finalizar') {
        if (!empty($_SESSION['carrinho'])) {
            $metodo_pagamento = "Numerário";
            $hora_entrega = date('H:i', strtotime('+15 minutes'));
            
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO pedido (utilizador_id, data, estado, metodo_Pagamento, hora_Agendada) VALUES (?, NOW(), 'Pendente', ?, ?)");
                $stmt->bind_param("iss", $user_id, $metodo_pagamento, $hora_entrega);
                $stmt->execute();
                $pedido_id = $conn->insert_id;
                $stmt->close();
                foreach ($_SESSION['carrinho'] as $id_artigo => $qtd) {
                    $sql_preco = "SELECT preco, stock FROM artigos WHERE id_artigos = $id_artigo";
                    $res_preco = $conn->query($sql_preco);
                    $dados_artigo = $res_preco->fetch_assoc();
                    
                    if($dados_artigo['stock'] < $qtd) throw new Exception("Stock insuficiente.");

                    $stmt_item = $conn->prepare("INSERT INTO pedido_artigos (pedido_id, artigo_id, quantidade, preco_unitario_historico) VALUES (?, ?, ?, ?)");
                    $stmt_item->bind_param("iiid", $pedido_id, $id_artigo, $qtd, $dados_artigo['preco']);
                    $stmt_item->execute();
                    $stmt_item->close();
                    $conn->query("UPDATE artigos SET stock = stock - $qtd WHERE id_artigos = $id_artigo");
                }
                $conn->commit();
                $_SESSION['carrinho'] = [];
                $mensagem = "Pedido realizado com sucesso!";
                $tipo_msg = "sucesso";
            } catch (Exception $e) {
                $conn->rollback();
                $mensagem = "Erro: " . $e->getMessage();
                $tipo_msg = "erro";
            }
        }
    }
}
$produtos_carrinho = [];
$total_pedido = 0;

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    $sql = "SELECT * FROM artigos WHERE id_artigos IN ($ids)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_artigos'];
        $qtd = $_SESSION['carrinho'][$id];
        $subtotal = $row['preco'] * $qtd;
        $total_pedido += $subtotal;
        
        $row['qtd_carrinho'] = $qtd;
        $row['subtotal'] = $subtotal;
        $produtos_carrinho[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - PedeJá</title>
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
                    <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
                    <span><?php echo $cargo; ?></span>
                </div>
            </div>

            <nav class="menu-navegacao">
                <div class="etiqueta-menu">Menu</div>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="artigos.php">Encomendar</a></li>
                    <li><a href="historicoAluno.html">Histórico</a></li>
                    <li><a href="carrinho.php" class="ativo">Carrinho</a></li>
                </ul>
            </nav>

            <div class="area-sair">
                <a href="logout.php">Sair <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="conteudo-principal">
            
            <header class="cabecalho-topo">
                <div class="saudacao">
                    <h2>Olá, <br><strong><?php echo htmlspecialchars($_SESSION['nome']); ?>!</strong></h2>
                </div>
            </header>

            <div class="contentor-carrinho">
                
                <div class="cabecalho-carrinho">
                    <h3>Meu pedido:</h3>
                    <hr class="divisor">
                </div>

                <?php if ($mensagem != ""): ?>
                    <div class="alert alert-<?php echo $tipo_msg; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($produtos_carrinho)): ?>
                    
                    <div class="estado-vazio">
                        Sem artigos no carrinho.
                    </div>
                    
                    <div class="widget-total">
                        <div class="cartao-branco">
                            <hr class="mini-divisor">
                            <div class="linha-total">
                                <span>Total</span>
                                <span>0,00 €</span>
                            </div>
                            <button class="botao-pedir" onclick="location.href='artigos.php'">Ir para Artigos</button>
                        </div>
                        <hr class="linha-fundo"> 
                    </div>

                <?php else: ?>

                    <div class="layout-carrinho-cheio">
                        
                        <div class="coluna-itens">
                            <?php foreach ($produtos_carrinho as $item): ?>
                                <div class="cartao-item">
                                    <div class="radio-select"></div>
                                    
                                    <img src="<?php echo !empty($item['imagem']) ? $item['imagem'] : 'images/default.png'; ?>" class="img-item">
                                    
                                    <div class="info-item">
                                        <h4><?php echo htmlspecialchars($item['nome']); ?></h4>
                                        <span><?php echo !empty($item['desc_artigo']) ? substr($item['desc_artigo'], 0, 30) . '...' : 'Delicioso e fresco'; ?></span>
                                    </div>

                                    <div class="preco-item">
                                        <?php echo number_format($item['preco'], 2); ?> €
                                    </div>

                                    <div class="seletor-qtd">
                                        <form method="POST" style="margin:0; display:inline;">
                                            <input type="hidden" name="acao" value="atualizar_qtd">
                                            <input type="hidden" name="id_artigo" value="<?php echo $item['id_artigos']; ?>">
                                            <input type="hidden" name="qtd" value="<?php echo $item['qtd_carrinho'] - 1; ?>">
                                            <button type="submit" class="btn-qtd">-</button>
                                        </form>

                                        <span><?php echo $item['qtd_carrinho']; ?></span>

                                        <form method="POST" style="margin:0; display:inline;">
                                            <input type="hidden" name="acao" value="atualizar_qtd">
                                            <input type="hidden" name="id_artigo" value="<?php echo $item['id_artigos']; ?>">
                                            <input type="hidden" name="qtd" value="<?php echo $item['qtd_carrinho'] + 1; ?>">
                                            <button type="submit" class="btn-qtd">+</button>
                                        </form>
                                    </div>

                                    <div class="caixa-lixo">
                                        <form method="POST" style="margin:0; display:flex;">
                                            <input type="hidden" name="acao" value="remover">
                                            <input type="hidden" name="id_artigo" value="<?php echo $item['id_artigos']; ?>">
                                            <button type="submit" class="btn-trash"><i class="fa-regular fa-trash-can"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="coluna-resumo"> <div class="lista-resumo">
                                <?php foreach ($produtos_carrinho as $item): ?>
                                    <div class="linha-resumo">
                                        <span><?php echo htmlspecialchars($item['nome']); ?></span>
                                        <span class="qtd-mini">x<?php echo $item['qtd_carrinho']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                             </div>

                             <hr class="mini-divisor">
                             
                             <div class="linha-total">
                                <span>Total</span>
                                <span><?php echo number_format($total_pedido, 2); ?> €</span>
                             </div>

                             <form method="POST">
                                <input type="hidden" name="acao" value="finalizar">
                                <button type="submit" class="botao-pedir">Pedir</button>
                             </form>
                        </div>

                    </div>

                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>