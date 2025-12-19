<?php
session_start();
require_once 'config.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['isAdmin'] != 1) {
    header("Location: login.php");
    exit;
}

$nome_utilizador = $_SESSION['nome']; 

$sql = "SELECT p.id_pedido, p.data, p.estado, p.hora_Agendada, u.nome AS cliente,
               GROUP_CONCAT(CONCAT(pa.quantidade, 'x ', a.nome) SEPARATOR ', ') AS itens_resumo,
               SUM(pa.quantidade * pa.preco_unitario_historico) AS total_pago
        FROM pedido p
        JOIN utilizadores u ON p.utilizador_id = u.id_utilizador
        JOIN pedido_artigos pa ON p.id_pedido = pa.pedido_id
        JOIN artigos a ON pa.artigo_id = a.id_artigos
        WHERE p.estado != 'Entregue'
        GROUP BY p.id_pedido
        ORDER BY p.data DESC, p.hora_Agendada DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Recentes - PedeJá</title>
    <link rel="stylesheet" href="css/historico.css">
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
                    <strong><?php echo htmlspecialchars($nome_utilizador); ?></strong>
                    <span>Administrador</span>
                </div>
            </div>

            <nav class="menu-navegacao">
                <div class="etiqueta-menu">Menu</div>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="artigosAdmin.php">Artigos</a></li>
                    <li><a href="stockAdmin.php">Stock</a></li>
                    <li><a href="historicoAdmin.php">Histórico</a></li>
                    <li><a href="pedidosAdmin.php" class="ativo">Pedidos</a></li>
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
                <div class="cabecalho-caixa">
                    <h3>Pedidos em Aberto:</h3>
                </div>

                <div class="tabela-contentor">
                    <table>
                        <thead>
                            <tr>
                                <th>Data & Horas</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Pedido</th>
                                <th>Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="celula-data">
                                                <span class="data"><?php echo date('d/m/Y', strtotime($row['data'])); ?></span>
                                                <span class="hora"><?php echo date('H:i', strtotime($row['hora_Agendada'])); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                $classe_estado = ($row['estado'] == 'Preparado') ? 'distintivo-preparado' : 'distintivo-preparacao';
                                            ?>
                                            <span class="<?php echo $classe_estado; ?>"><?php echo $row['estado']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                        <td class="desc-pedido"><?php echo htmlspecialchars($row['itens_resumo']); ?></td>
                                        <td class="preco"><?php echo number_format($row['total_pago'], 2, ',', ''); ?>€</td>
                                        <td>
                                            <a href="ver_pedidoAdmin.php?id=<?php echo $row['id_pedido']; ?>" class="link-ver">
                                                Ver <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Não há pedidos pendentes no momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>