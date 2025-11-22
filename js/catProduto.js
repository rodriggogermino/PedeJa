const catBtns = document.querySelectorAll('.lista-cat li');
const produtos = document.querySelectorAll('.cartao-produto');

catBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
        catBtns.forEach(item => item.classList.remove('cat-ativa'));
        btn.classList.add('cat-ativa');
        const filterValue = btn.getAttribute('data-category');
        produtos.forEach((item) => {
            if (item.getAttribute('data-item') === filterValue) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

