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

