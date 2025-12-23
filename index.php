<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nome_utilizador = $_SESSION['nome'];
$id_utilizador = isset($_SESSION['id_utilizador']) ? $_SESSION['id_utilizador'] : 0;
$isAdmin = $_SESSION['isAdmin'];
$cargo = ($isAdmin == 1) ? "Administrador" : "Aluno";

$total_carrinho = 0;
if(isset($_SESSION['carrinho'])) {
    $total_carrinho = array_sum($_SESSION['carrinho']);
}
$display_badge = ($total_carrinho > 0) ? 'inline-block' : 'none';

function getClasseEstado($estado) {
    $estado = strtolower($estado);
    if ($estado == 'entregue' || $estado == 'concluido') return 'estado-verde';
    if ($estado == 'pendente' || $estado == 'em preparação') return 'estado-amarelo';
    if ($estado == 'cancelado') return 'estado-vermelho';
    return '';
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PedeJá</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .barra-progresso-mini {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 200px;
            margin-top: 10px;
            position: relative;
        }
        .passo-mini {
            width: 12px;
            height: 12px;
            background-color: #e0e0e0;
            border-radius: 50%;
            z-index: 2;
            transition: background-color 0.3s;
            border: 2px solid #fff; 
        }
        .linha-mini {
            flex: 1;
            height: 3px;
            background-color: #e0e0e0;
            margin: 0 -2px;
            z-index: 1;
            transition: background-color 0.3s;
        }
        .passo-mini.ativo {
            background-color: #fff;
            border: 3px solid #e85d04;
            width: 14px;
            height: 14px;
        }
        .linha-mini.ativo {
            background-color: #e85d04;
        }
        .texto-estado-visual {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }
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
                    <li><a href="index.php" class="ativo">Início</a></li>
                    
                    <?php if ($isAdmin == 1): ?>
                        <li><a href="artigos.php">Artigos</a></li>
                        <li><a href="stockAdmin.php">Stock</a></li>
                        <li><a href="historicoAdmin.php">Histórico</a></li>
                        <li><a href="pedidosAdmin.php">Pedidos</a></li>
                    <?php else: ?>
                        <li><a href="artigos.php">Encomendar</a></li>
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
                        <div class="cartao cartao-amarelo" onclick="location.href='artigos.php'">
                            <div class="icone-topo"><i class="fa-solid fa-arrow-right"></i></div>
                            <div class="texto-cartao">Ver Artigos</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 20px; width: 100%;">
                        <div class="texto-destaque" style="display: flex; flex-direction: column; justify-content: center;">
                            <?php
                            $sql_ultimo = "SELECT id_pedido, estado, data FROM pedido WHERE utilizador_id = ? ORDER BY data DESC LIMIT 1";
                            if ($stmt = $conn->prepare($sql_ultimo)) {
                                $stmt->bind_param("i", $id_utilizador);
                                $stmt->execute();
                                $result_ultimo = $stmt->get_result();

                                if ($result_ultimo->num_rows > 0) {
                                    $row_ultimo = $result_ultimo->fetch_assoc();
                                    $estado = $row_ultimo['estado'];
                                    
                                    // Lógica da Barra de Progresso
                                    $p1 = "ativo"; $l1 = ""; $p2 = ""; $l2 = ""; $p3 = "";
                                    
                                    if ($estado == 'Em Preparação') {
                                        $l1 = "ativo";
                                        $p2 = "ativo";
                                    } elseif ($estado == 'Entregue') {
                                        $l1 = "ativo"; $p2 = "ativo";
                                        $l2 = "ativo"; $p3 = "ativo";
                                    }

                                    echo '<span class="texto-leve">Último pedido: #' . $row_ultimo['id_pedido'] . '</span>';
                                    echo '<span class="texto-estado-visual">' . strtoupper($estado) . '</span>';
                                    
                                    // HTML da Barra
                                    echo '<div class="barra-progresso-mini">';
                                    echo '<div class="passo-mini ' . $p1 . '" title="Pendente"></div>';
                                    echo '<div class="linha-mini ' . $l1 . '"></div>';
                                    echo '<div class="passo-mini ' . $p2 . '" title="Em Preparação"></div>';
                                    echo '<div class="linha-mini ' . $l2 . '"></div>';
                                    echo '<div class="passo-mini ' . $p3 . '" title="Entregue"></div>';
                                    echo '</div>';

                                } else {
                                    echo '<span class="texto-leve">Sem</span> <strong>pedidos<br>recentes</strong>';
                                }
                                $stmt->close();
                            } else {
                                echo '<span class="texto-leve">Bem-vindo</span>';
                            }
                            ?>
                        </div>
                        <div class="cartao cartao-amarelo" onclick="location.href='artigos.php'">
                            <div class="icone-topo"><i class="fa-solid fa-arrow-right"></i></div>
                            <div class="texto-cartao">Fazer Pedido</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="seccao-historico">
                <div class="cabecalho-historico">
                    <h3>Histórico de Pedidos</h3> 
                    <div class="controlos">
                        <div class="caixa-pesquisa">
                            <input type="text" id="pesquisaHistorico" placeholder="Pesquisar...">
                        </div>
                        <button class="botao-icone" id="btnSortId" title="Ordenar por ID">
                            <i class="fa-solid fa-arrow-down-1-9"></i>
                        </button>
                        <button class="botao-icone" id="btnSortData" title="Ordenar por Data">
                            <i class="fa-regular fa-calendar"></i>
                        </button>
                    </div>
                </div>

                <div class="lista-pedidos">
                    <?php if ($isAdmin == 1): ?>
                        <?php
                        $sql = "SELECT p.id_pedido, p.data, p.estado, u.nome AS nome_cliente 
                                FROM pedido p 
                                JOIN utilizadores u ON p.utilizador_id = u.id_utilizador 
                                WHERE p.estado = 'Entregue'
                                ORDER BY p.data DESC LIMIT 10";
                        
                        if($result = $conn->query($sql)){
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $data = date('Y/m/d', strtotime($row['data']));
                                    echo '<div class="linha-pedido">';
                                    echo '<div class="coluna"><strong>PEDIDO:</strong> ' . $row['id_pedido'] . '</div>';
                                    echo '<div class="coluna"><strong>DATA:</strong> ' . $data . '</div>';
                                    echo '<div class="coluna"><strong>CLIENTE:</strong> ' . htmlspecialchars($row['nome_cliente']) . '</div>';
                                    echo '<div class="coluna estado-verde">' . strtoupper($row['estado']) . ' <i class="fa-solid fa-check"></i></div>';
                                    echo '<div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="linha-pedido">Sem pedidos entregues.</div>';
                            }
                        }
                        ?>
                    <?php else: ?>
                        <?php
                        $sql_aluno = "SELECT id_pedido, data, estado FROM pedido WHERE utilizador_id = ? ORDER BY data DESC LIMIT 10";
                        if ($stmt = $conn->prepare($sql_aluno)) {
                            $stmt->bind_param("i", $id_utilizador);
                            $stmt->execute();
                            $result_aluno = $stmt->get_result();

                            if ($result_aluno->num_rows > 0) {
                                while($row = $result_aluno->fetch_assoc()) {
                                    $data = date('Y/m/d', strtotime($row['data']));
                                    $classe_css = getClasseEstado($row['estado']);
                                    
                                    echo '<div class="linha-pedido">';
                                    echo '<div class="coluna"><strong>PEDIDO:</strong> ' . $row['id_pedido'] . '</div>';
                                    echo '<div class="coluna"><strong>DATA:</strong> ' . $data . '</div>';
                                    echo '<div class="coluna ' . $classe_css . '">' . strtoupper($row['estado']) . '</div>';
                                    echo '<div class="coluna seta"><i class="fa-solid fa-arrow-right"></i></div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="linha-pedido" style="justify-content: center;">Ainda não efetuaste encomendas.</div>';
                            }
                            $stmt->close();
                        }
                        ?>
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
                    <?php
                    $sql_recentes = "SELECT p.id_pedido, p.data, p.hora_Agendada, u.nome AS nome_cliente 
                                     FROM pedido p 
                                     JOIN utilizadores u ON p.utilizador_id = u.id_utilizador 
                                     WHERE p.estado != 'Entregue'
                                     ORDER BY p.data DESC LIMIT 5";
                    
                    if ($stmt_recentes = $conn->prepare($sql_recentes)) {
                        $stmt_recentes->execute();
                        $result_recentes = $stmt_recentes->get_result();

                        if ($result_recentes->num_rows > 0) {
                            while($row = $result_recentes->fetch_assoc()) {
                                $hora = !empty($row['hora_Agendada']) ? date('H:i', strtotime($row['hora_Agendada'])) : date('H:i', strtotime($row['data']));
                                
                                $partes_nome = explode(' ', $row['nome_cliente']);
                                $nome_curto = $partes_nome[0];
                                if (count($partes_nome) > 1) {
                                    $nome_curto .= ' ' . substr(end($partes_nome), 0, 1) . '.';
                                }

                                echo '<div class="cartao-recente">';
                                echo '<div class="cabecalho-rc">';
                                echo '<span class="titulo-rc">Pedido nº ' . $row['id_pedido'] . '</span>';
                                echo '<span class="nome-rc">' . htmlspecialchars($nome_curto) . '</span>';
                                echo '</div>';
                                echo '<div class="rodape-rc">';
                                echo '<span class="hora-rc">' . $hora . '</span>';
                                echo '<button class="botao-ver" onclick="location.href=\'ver_pedidoAdmin.php?id=' . $row['id_pedido'] . '\'">Ver</button>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p style="text-align:center; color:#666; font-size: 0.9em; margin-top:20px;">Sem pedidos pendentes.</p>';
                        }
                        $stmt_recentes->close();
                    }
                    ?>
                </div>

            <?php else: ?>
                <div class="cabecalho-painel">
                    <h3>Favoritos</h3>
                    <i class="fa-regular fa-star icone-cabecalho"></i>
                </div>

                <div class="lista-favoritos">
                    <?php
                    $sql_fav = "SELECT a.id_artigos, a.nome, a.imagem, a.preco 
                                FROM favoritos f 
                                JOIN artigos a ON f.artigo_id = a.id_artigos 
                                WHERE f.utilizador_id = ? 
                                LIMIT 3";
                    
                    if ($stmt = $conn->prepare($sql_fav)) {
                        $stmt->bind_param("i", $id_utilizador);
                        $stmt->execute();
                        $result_fav = $stmt->get_result();

                        if ($result_fav->num_rows > 0) {
                            while($fav = $result_fav->fetch_assoc()) {
                                echo '<div class="cartao-favorito">';
                                echo '<i class="fa-solid fa-star distintivo-estrela"></i>';
                                echo '<div class="imagem-favorito">';
                                $imgSrc = !empty($fav['imagem']) ? $fav['imagem'] : "images/default.png"; 
                                echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="' . htmlspecialchars($fav['nome']) . '">';
                                echo '</div>';
                                echo '<h4>' . htmlspecialchars($fav['nome']) . '</h4>';
                                echo '<button class="botao-encomendar" onclick="adicionarCarrinho(' . $fav['id_artigos'] . ')">';
                                echo 'Adicionar <i class="fa-solid fa-cart-plus"></i>';
                                echo '</button>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p style="text-align:center; color:#666; font-size: 0.9em; margin-top:20px;">Ainda não tens favoritos.</p>';
                        }
                        $stmt->close();
                    }
                    ?>
                </div>
            <?php endif; ?>

        </aside>

    </div>
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputPesquisa = document.getElementById('pesquisaHistorico');
            const btnSortId = document.getElementById('btnSortId');
            const btnSortData = document.getElementById('btnSortData');
            const contentorPedidos = document.querySelector('.lista-pedidos');
            
            let ordemIdAsc = true;
            let ordemDataRecente = true;

            if (inputPesquisa) {
                inputPesquisa.addEventListener('keyup', function() {
                    const termo = this.value.toLowerCase();
                    const linhas = document.querySelectorAll('.linha-pedido');

                    linhas.forEach(linha => {
                        if(linha.innerText.includes('Sem pedidos') || linha.innerText.includes('Ainda não')) return;

                        const textoLinha = linha.innerText.toLowerCase();
                        if (textoLinha.includes(termo)) {
                            linha.style.display = 'flex';
                        } else {
                            linha.style.display = 'none';
                        }
                    });
                });
            }

            if (btnSortId && contentorPedidos) {
                btnSortId.addEventListener('click', function() {
                    ordenarPedidos('id');
                    const icon = this.querySelector('i');
                    if(ordemIdAsc) {
                        icon.className = 'fa-solid fa-arrow-down-1-9';
                    } else {
                        icon.className = 'fa-solid fa-arrow-up-1-9';
                    }
                });
            }

            if (btnSortData && contentorPedidos) {
                btnSortData.addEventListener('click', function() {
                    ordenarPedidos('data');
                });
            }

            function ordenarPedidos(tipo) {
                let linhas = Array.from(document.querySelectorAll('.linha-pedido'));
                linhas = linhas.filter(linha => !linha.innerText.includes('Sem pedidos') && !linha.innerText.includes('Ainda não'));

                linhas.sort((a, b) => {
                    let valA, valB;

                    if (tipo === 'id') {
                        const textoA = a.querySelector('.coluna:nth-child(1)').innerText;
                        const textoB = b.querySelector('.coluna:nth-child(1)').innerText;
                        
                        valA = parseInt(textoA.replace(/\D/g, ''));
                        valB = parseInt(textoB.replace(/\D/g, ''));
                        
                        return ordemIdAsc ? (valA - valB) : (valB - valA);
                    } 
                    else if (tipo === 'data') {
                        const textoA = a.querySelector('.coluna:nth-child(2)').innerText.replace('DATA:', '').trim();
                        const textoB = b.querySelector('.coluna:nth-child(2)').innerText.replace('DATA:', '').trim();
                        
                        valA = new Date(textoA);
                        valB = new Date(textoB);

                        return ordemDataRecente ? (valB - valA) : (valA - valB);
                    }
                });

                if (tipo === 'id') ordemIdAsc = !ordemIdAsc;
                if (tipo === 'data') ordemDataRecente = !ordemDataRecente;

                linhas.forEach(linha => contentorPedidos.appendChild(linha));
            }
        });
    </script>
</body>
</html>