document.getElementById('userCpf').addEventListener('input', function (e) {
    let cpf = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito

    if (cpf.length > 11) {
        cpf = cpf.substring(0, 11); // Limita o tamanho máximo a 11 dígitos
    }

    // Formatação do CPF, começando apenas após o 6º dígito
    if (cpf.length > 6) {
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Insere o primeiro ponto
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Insere o segundo ponto
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Insere o traço antes dos dois últimos dígitos
    }

    e.target.value = cpf; // Atualiza o valor do input
});

document.getElementById('userTel').addEventListener('input', function (e) {
    let tel = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    if (tel.length > 11) {
        tel = tel.substring(0, 11); // Limita o tamanho a 11 dígitos para telefones com 9 dígitos
    }

    // Formatação do telefone
    tel = tel.replace(/^(\d{2})(\d)/g, '($1) $2'); // Adiciona parênteses ao redor do DDD
    tel = tel.replace(/(\d{4})(\d)/, '$1-$2'); // Adiciona um traço após os quatro primeiros dígitos


    e.target.value = tel; // Atualiza o valor do input
});

// Seleciona todos os elementos com a classe 'toggle-password'
const togglePassword = document.querySelectorAll('.toggle-password');

togglePassword.forEach(function (toggle) {
    toggle.addEventListener('click', function () {
        // Obtém o seletor do campo de senha associado
        const input = document.querySelector(this.getAttribute('toggle'));
        if (input.getAttribute('type') === 'password') {
            input.setAttribute('type', 'text');
            this.innerHTML = '<i class="fas fa-eye-slash"></i>'; // Altera o ícone para o de 'olho fechado'
        } else {
            input.setAttribute('type', 'password');
            this.innerHTML = '<i class="fas fa-eye"></i>'; // Altera o ícone para o de 'olho aberto'
        }
    });
});


// Função para verificar se as senhas coincidem
function checkPasswordMatch() {
    const password = document.getElementById('userPassword').value;
    const passwordRepeat = document.getElementById('userPasswordRepeat').value;
    const messageDiv = document.getElementById('passwordMatchMessage');

    // Se ambos os campos de senha estiverem preenchidos, mostramos a mensagem
    if (password.length > 0 || passwordRepeat.length > 0) {
        if (password === passwordRepeat) {
            messageDiv.textContent = 'As senhas coincidem.';
            messageDiv.classList.remove('error-message');
            messageDiv.classList.add('success-message');
            messageDiv.style.display = 'block';  // Exibe a mensagem
        } else {
            messageDiv.textContent = 'As senhas não coincidem.';
            messageDiv.classList.remove('success-message');
            messageDiv.classList.add('error-message');
            messageDiv.style.display = 'block';  // Exibe a mensagem
        }
    } else {
        messageDiv.style.display = 'none';  // Esconde a mensagem se os campos estiverem vazios
    }
}
    

// Adicionar event listeners aos campos de senha
document.getElementById('userPassword').addEventListener('input', checkPasswordMatch);
document.getElementById('userPasswordRepeat').addEventListener('input', checkPasswordMatch);

