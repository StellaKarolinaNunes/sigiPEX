const container = document.querySelector('.container-form');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');
const loginLinkToggle = document.querySelector('.login-link-toggle');

registerBtn.addEventListener('click', () => {
    container.classList.add('active');
})

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
})

if (loginLinkToggle) {
    loginLinkToggle.addEventListener('click', (e) => {
        e.preventDefault();
        container.classList.remove('active');
    })
}

// Lógica para Mostrar/Ocultar Senha
const togglePasswords = document.querySelectorAll('.toggle-password');
togglePasswords.forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.replace('bx-hide', 'bx-show');
        } else {
            input.type = 'password';
            this.classList.replace('bx-show', 'bx-hide');
        }
    });
});