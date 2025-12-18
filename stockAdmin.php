<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['acao'])) {
        if ($data['acao'] === 'atualizar') {
            $id = $data['id'];
            $qtd = $data['quantidade'];
            $stmt = $conn->prepare("UPDATE artigos SET stock = GREATEST(0, stock + ?) WHERE id_artigos = ?");
            $stmt->bind_param("ii", $qtd, $id);
            if ($stmt->execute()) {
                $res = $conn->query("SELECT stock FROM artigos WHERE id_artigos = $id");
                $row = $res->fetch_assoc();
                echo json_encode(['sucesso' => true, 'novo_stock' => $row['stock']]);
            } else {
                echo json_encode(['sucesso' => false]);
            }
            exit;
        } elseif ($data['acao'] === 'eliminar') {
            $id = $data['id'];
            $stmt = $conn->prepare("DELETE FROM artigos WHERE id_artigos = ?");
            $stmt->bind_param("i", $id);
            echo json_encode(['sucesso' => $stmt->execute()]);
            exit;
        }
    }
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT a.*, c.nome AS nome_categoria FROM artigos a JOIN categorias c ON a.categoria_id = c.id_categoria ORDER BY a.nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - PedeJá</title>
    <link rel="stylesheet" href="css/artigos.css">
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
                    <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
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
                <a href="logout.php">Sair <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="conteudo-principal">
            <header class="cabecalho-pagina">
                <h2>Olá, <br><strong><?php echo htmlspecialchars($_SESSION['nome']); ?>!</strong></h2>
            </header>
            <div class="caixa-conteudo">
                <div class="cabecalho-caixa">
                    <div class="lado-esquerdo-cabecalho" style="display: flex; align-items: center; gap: 15px;">
                        <h3>Todos os artigos:</h3>
                        <button class="botao-adicionar" onclick="location.href='addArtigos.php'" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #e85d04; color: white; cursor: pointer;"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div class="pesquisa-pill">
                        <input type="text" id="barraPesquisa" placeholder="Pesquisar" onkeyup="filtrarPesquisa()">
                    </div>
                </div>

                <div class="filtro-categoria">
                    <span class="etiqueta-cat">Categorias</span>
                    <ul class="lista-cat">
                        <li class="ativo" onclick="filtrarCategoria('todos', this)">Todos</li>
                        <li onclick="filtrarCategoria('frutas', this)">Frutas</li>
                        <li onclick="filtrarCategoria('salgados', this)">Salgados</li>
                        <li onclick="filtrarCategoria('doces', this)">Doces</li>
                        <li onclick="filtrarCategoria('bebidas', this)">Bebidas</li>
                        <li onclick="filtrarCategoria('sandes', this)">Sandes</li>
                        <li onclick="filtrarCategoria('outros', this)">Outros</li>
                    </ul>
                </div>

                <div class="grelha-produtos">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php $catSlug = strtolower($row['nome_categoria']); ?>
                            <div class="cartao-stock" id="artigo-<?php echo $row['id_artigos']; ?>" data-item="<?php echo $catSlug; ?>">
                                <button class="botao-fechar" onclick="eliminarArtigo(<?php echo $row['id_artigos']; ?>)">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <div class="conteudo-cartao">
                                    <div class="envoltorio-imagem">
                                        <img src="<?php echo !empty($row['imagem']) ? $row['imagem'] : 'images/default.png'; ?>" alt="<?php echo htmlspecialchars($row['nome']); ?>">
                                    </div>
                                    <div class="envoltorio-info">
                                        <h4><?php echo htmlspecialchars($row['nome']); ?></h4>
                                        <span><?php echo htmlspecialchars($row['nome_categoria']); ?></span>
                                    </div>
                                </div>
                                <div class="envoltorio-contador">
                                    <button class="botao-contador" onclick="alterarStock(<?php echo $row['id_artigos']; ?>, -1)">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <div class="mostrador-contagem" id="stock-<?php echo $row['id_artigos']; ?>">
                                        <?php echo $row['stock']; ?>
                                    </div>
                                    <button class="botao-contador" onclick="alterarStock(<?php echo $row['id_artigos']; ?>, 1)">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="padding: 20px;">Sem artigos registados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function alterarStock(id, qtd) {
            fetch('stockAdmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'atualizar', id: id, quantidade: qtd })
            })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) document.getElementById('stock-' + id).innerText = data.novo_stock;
            });
        }

        function eliminarArtigo(id) {
            if (confirm("Eliminar artigo?")) {
                fetch('stockAdmin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ acao: 'eliminar', id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.sucesso) document.getElementById('artigo-' + id).remove();
                });
            }
        }

        function filtrarCategoria(cat, elemento) {
            document.querySelectorAll('.lista-cat li').forEach(li => li.classList.remove('ativo'));
            elemento.classList.add('ativo');
            document.querySelectorAll('.cartao-stock').forEach(item => {
                item.style.display = (cat === 'todos' || item.getAttribute('data-item') === cat) ? 'flex' : 'none';
            });
        }

        function filtrarPesquisa() {
            let input = document.getElementById('barraPesquisa').value.toLowerCase();
            document.querySelectorAll('.cartao-stock').forEach(item => {
                let nome = item.querySelector('h4').innerText.toLowerCase();
                item.style.display = nome.includes(input) ? 'flex' : 'none';
            });
        }
    </script>
</body>
</html>