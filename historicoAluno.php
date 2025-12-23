<?php
session_start();
require_once 'config.php';

// Verifica se o utilizador está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Obtém os dados do utilizador da sessão
$id_utilizador = $_SESSION['id_utilizador'];
$nome_utilizador = $_SESSION['nome'];

// Query para buscar apenas os pedidos ENTREGUES do aluno logado
// Agrupamos os artigos para mostrar um resumo numa única linha
$sql = "SELECT p.id_pedido, p.data, p.hora_Agendada, p.estado,
               GROUP_CONCAT(CONCAT(pa.quantidade, 'x ', a.nome) SEPARATOR ', ') AS resumo_pedido,
               SUM(pa.quantidade * pa.preco_unitario_historico) AS total_pago
        FROM pedido p
        JOIN pedido_artigos pa ON p.id_pedido = pa.pedido_id
        JOIN artigos a ON pa.artigo_id = a.id_artigos
        WHERE p.utilizador_id = ? AND p.estado = 'Entregue'
        GROUP BY p.id_pedido
        ORDER BY p.data DESC, p.hora_Agendada DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - PedeJá</title>
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
                    <span>Aluno</span>
                </div>
            </div>

            <nav class="menu-navegacao">
                <div class="etiqueta-menu">Menu</div>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="artigos.php">Encomendar</a></li>
                    <li><a href="historicoAluno.php" class="ativo">Histórico</a></li>
                    <li><a href="carrinhoAluno.php">Carrinho</a></li>
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
                    <h3>O teu histórico de pedidos:</h3>
                </div>

                <div class="tabela-contentor">
                    <table>
                        <thead>
                            <tr>
                                <th>Data & Hora</th>
                                <th>Estado</th>
                                <th>Pedido</th>
                                <th>Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="celula-data">
                                                <span class="data"><?php echo date('d/m/Y', strtotime($row['data'])); ?></span>
                                                <span class="hora"><?php echo date('H:i', strtotime($row['hora_Agendada'])); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="distintivo-entregue"><?php echo $row['estado']; ?></span>
                                        </td>
                                        <td class="desc-pedido"><?php echo htmlspecialchars($row['resumo_pedido']); ?></td>
                                        <td class="preco"><?php echo number_format($row['total_pago'], 2, ',', ''); ?>€</td>
                                        <td>
                                            <a href="ver_pedidoAluno.php?id=<?php echo $row['id_pedido']; ?>" class="link-ver">Ver <i class="fa-regular fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 20px;">Ainda não tens pedidos entregues no teu histórico.</td>
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