<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nome_utilizador = $_SESSION['nome'];
$user_id = $_SESSION['id_utilizador'];
$isAdmin = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0; 
$cargo = ($isAdmin == 1) ? "Administrador" : "Aluno";
$menu_ativo = ($isAdmin == 1) ? "Artigos" : "Encomendar"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin == 0) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['acao'])) {
        
        if ($input['acao'] === 'toggle_favorito') {
            $artigo_id = $input['id'];
            
            $check = $conn->prepare("SELECT id_favorito FROM favoritos WHERE utilizador_id = ? AND artigo_id = ?");
            $check->bind_param("ii", $user_id, $artigo_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $del = $conn->prepare("DELETE FROM favoritos WHERE utilizador_id = ? AND artigo_id = ?");
                $del->bind_param("ii", $user_id, $artigo_id);
                $del->execute();
                echo json_encode(['status' => 'removed']);
            } else {
                $ins = $conn->prepare("INSERT INTO favoritos (utilizador_id, artigo_id) VALUES (?, ?)");
                $ins->bind_param("ii", $user_id, $artigo_id);
                $ins->execute();
                echo json_encode(['status' => 'added']);
            }
            exit;
        } 
       
        elseif ($input['acao'] === 'adicionar_carrinho') {
            $artigo_id = $input['id'];
            if (!isset($_SESSION['carrinho'])) { $_SESSION['carrinho'] = []; }

            if (isset($_SESSION['carrinho'][$artigo_id])) {
                $_SESSION['carrinho'][$artigo_id]++;
            } else {
                $_SESSION['carrinho'][$artigo_id] = 1;
            }
            echo json_encode(['status' => 'success', 'total' => array_sum($_SESSION['carrinho'])]);
            exit;
        }
    }
}

if ($isAdmin == 0) {
    $sql = "SELECT a.*, c.nome AS nome_categoria, 
            (SELECT COUNT(*) FROM favoritos f WHERE f.artigo_id = a.id_artigos AND f.utilizador_id = $user_id) as is_favorito
            FROM artigos a 
            JOIN categorias c ON a.categoria_id = c.id_categoria
            WHERE a.stock > 0
            ORDER BY a.nome ASC";
} else {
    $sql = "SELECT a.*, c.nome AS nome_categoria 
            FROM artigos a 
            JOIN categorias c ON a.categoria_id = c.id_categoria
            ORDER BY a.nome ASC";
}

$result = $conn->query($sql);

$total_carrinho = isset($_SESSION['carrinho']) ? array_sum($_SESSION['carrinho']) : 0;
$display_badge = ($total_carrinho > 0) ? 'inline-block' : 'none';
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - PedeJá</title>
    <link rel="stylesheet" href="css/artigos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .favorito-ativo { color: #ffbf00; }
        .favorito-inativo { color: #ccc; }
        .hidden { display: none !important; }
    </style>
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
                    <li><a href="index.php">Início</a></li>
                    
                    <?php if ($isAdmin == 1): ?>
                        <li><a href="artigos.php" class="ativo">Artigos</a></li>
                        <li><a href="stockAdmin.php">Stock</a></li> 
                        <li><a href="historicoAdmin.php">Histórico</a></li>
                        <li><a href="pedidosAdmin.php">Pedidos</a></li>
                    <?php else: ?>
                        <li><a href="artigos.php" class="ativo">Encomendar</a></li>
                        <li><a href="historicoAluno.php">Histórico</a></li>
                        <li>
                            <a href="carrinho.php">
                                Carrinho 
                                <span id="badge-carrinho" style="background:red; color:white; padding:2px 6px; border-radius:10px; font-size:12px; display: <?php echo $display_badge; ?>;">
                                    <?php echo $total_carrinho; ?>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>
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
                    <h3>Ver artigos:</h3>
                    <div class="pesquisa-pill">
                        <input type="text" id="barraPesquisa" placeholder="Pesquisar" onkeyup="filtrarPesquisa()">
                    </div>
                </div>

                <div class="filtro-categoria">
                    <span class="etiqueta-cat">Categorias</span>
                    <ul class="lista-cat">
                        <li onclick="filtrarCategoria('frutas', this)">Frutas</li>
                        <li onclick="filtrarCategoria('salgados', this)">Salgados</li>
                        <li onclick="filtrarCategoria('doces', this)">Doces</li>
                        <li onclick="filtrarCategoria('bebidas', this)">Bebidas</li>
                        <li onclick="filtrarCategoria('sandes', this)">Sandes</li>
                        <li onclick="filtrarCategoria('outros', this)">Outros</li>
                    </ul>
                </div>

                <div class="grelha-produtos">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php 
                                $catSlug = strtolower($row['nome_categoria']); 
                                $isFav = isset($row['is_favorito']) ? $row['is_favorito'] : 0;
                                $favClass = ($isFav > 0) ? 'favorito-ativo' : 'favorito-inativo';
                            ?>
                            
                            <div class="cartao-produto" data-item="<?php echo $catSlug; ?>">
                                
                                <?php if ($isAdmin == 0): ?>
                                    <button class="btn-favorito <?php echo $favClass; ?>" onclick="toggleFavorito(this, <?php echo $row['id_artigos']; ?>)">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                <?php endif; ?>

                                <div class="contentor-img">
                                    <img src="<?php echo !empty($row['imagem']) ? $row['imagem'] : 'images/default.png'; ?>" alt="<?php echo htmlspecialchars($row['nome']); ?>">
                                </div>
                                
                                <div class="linha-laranja"></div>
                                
                                <div class="info-produto">
                                    <div class="cabecalho-produto">
                                        <span class="titulo-produto"><?php echo htmlspecialchars($row['nome']); ?></span>
                                        <span class="preco-produto"><?php echo number_format($row['preco'], 2); ?> €</span>
                                    </div>
                                    <?php if (!empty($row['desc_artigo'])): ?>
                                        <p class="desc-produto"><?php echo htmlspecialchars($row['desc_artigo']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isAdmin == 0): ?>
                                    <button class="btn-encomendar" onclick="adicionarCarrinho(<?php echo $row['id_artigos']; ?>)">Encomendar</button>
                                <?php endif; ?>

                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="padding:20px;">Não há artigos disponíveis de momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
    </body>
</html>