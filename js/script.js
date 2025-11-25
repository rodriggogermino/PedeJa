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
            linhas[1].classList.add("ativo");
            passos[2].classList.add("ativo");
        }
    }
}