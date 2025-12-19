function entrar() {
    let email = document.getElementById("email").value
    let pass = document.getElementById("password").value

    if(email == "admin@pedeja.com" && pass == "admin123")  {
        window.location.href = 'dashboardAdmin.html';
    } else if(email == "aluno@pedeja.com" && pass == "aluno123") {
        window.location.href = 'dashboardAluno.html';
    } else {
        alert("Dados incorretos!")
    }
}

function mudarEstado() {
    let elem = document.getElementsByClassName("texto-estado")[0]
    let linhas = document.querySelectorAll(".barra-progresso .linha");
    let passos = document.querySelectorAll(".barra-progresso .passo");
    let estadoAtual = elem.innerText
    
    if(estadoAtual !== "Entregue") {
        if(estadoAtual === "Em Preparação") {
            elem.innerText = "Preparado"
            passos[1].classList.add("ativo");
            linhas[0].classList.add("ativo");
        } else {
            elem.innerText = "Entregue"
            elem.style.color = "#e85d04"   
            linhas[1].classList.add("ativo");
            passos[2].classList.add("ativo");
        }
    }
}

function toggleFavorito(botao, id) {
    fetch('artigos.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ acao: 'toggle_favorito', id: id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'added') {
            botao.classList.remove('favorito-inativo');
            botao.classList.add('favorito-ativo');
        } else {
            botao.classList.remove('favorito-ativo');
            botao.classList.add('favorito-inativo');
        }
    })
    .catch(err => console.error(err));
}

function adicionarCarrinho(id) {
    fetch('artigos.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ acao: 'adicionar_carrinho', id: id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            alert("Artigo adicionado ao carrinho!");
            let badge = document.getElementById('badge-carrinho');
            if(badge) {
                badge.innerText = data.total;
                badge.style.display = 'inline-block';
            }
        }
    })
    .catch(err => console.error(err));
}

function filtrarCategoria(cat, elemento) {
    document.querySelectorAll('.lista-cat li').forEach(li => li.classList.remove('ativo'));
    elemento.classList.add('ativo');
    document.querySelectorAll('.cartao-produto').forEach(item => {
        item.style.display = (cat === 'todos' || item.getAttribute('data-item') === cat) ? 'flex' : 'none';
    });
}

function filtrarPesquisa() {
    let input = document.getElementById('barraPesquisa').value.toLowerCase();
    document.querySelectorAll('.cartao-produto').forEach(item => {
        let nome = item.querySelector('.titulo-produto').innerText.toLowerCase();
        item.style.display = nome.includes(input) ? 'flex' : 'none';
    });
}