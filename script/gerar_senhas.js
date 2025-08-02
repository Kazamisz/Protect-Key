// Adicione este event listener ao JavaScript existente
document.getElementById('word-separator').addEventListener('input', function (e) {
    // Garante que apenas 1 caractere seja permitido
    if (this.value.length > 1) {
        this.value = this.value.slice(0, 1);
    }
});

const palavras = [
    "casa", "carro", "livro", "mesa", "café", "água", "porta", "chave",
    "papel", "lápis", "vida", "amor", "tempo", "terra", "fogo", "vento",
    "noite", "tarde", "festa", "praia", "monte", "chuva", "ponte", "flor",
    "peixe", "pedra", "verde", "azul", "gato", "copo", "prato", "faca",
    "bola", "jogo", "arte", "foto", "lago", "rio", "mar", "sol",
    "lua", "céu", "nuvem", "pão", "leite", "fruta", "doce", "sal",
    "ouro", "prata", "vidro", "metal", "roupa", "meia", "cinto", "bolsa"
];

const lowercase = "abcdefghijklmnopqrstuvwxyz";
const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
const numbers = "0123456789";
const symbols = "!@#$%^&*()_+[]{}<>?|~";

function switchTab(tabName) {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.tab[onclick="switchTab('${tabName}')"]`).classList.add('active');

    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`${tabName}-tab`).classList.add('active');
}

function generatePassword() {
    const useLowercase = document.getElementById("use-lowercase").checked;
    const useUppercase = document.getElementById("use-uppercase").checked;
    const useNumbers = document.getElementById("use-numbers").checked;
    const useSymbols = document.getElementById("use-symbols").checked;
    const length = document.getElementById("length-slider").value;

    let charSet = "";
    if (useLowercase) charSet += lowercase;
    if (useUppercase) charSet += uppercase;
    if (useNumbers) charSet += numbers;
    if (useSymbols) charSet += symbols;

    if (!charSet) {
        alert("Selecione pelo menos uma opção de caracteres.");
        return;
    }

    let password = "";
    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charSet.length);
        password += charSet[randomIndex];
    }

    document.getElementById("generated-password").value = password;
    calculateBreakTime(password);
}

function generatePassphrase() {
    const wordCount = parseInt(document.getElementById("words-slider").value);
    const capitalizeFirst = document.getElementById("capitalize-first").checked;
    const capitalizeAll = document.getElementById("capitalize-all").checked;
    const includeNumber = document.getElementById("include-number").checked;
    const separator = document.getElementById("word-separator").value;

    let selectedWords = [];
    let usedIndexes = new Set();

    while (selectedWords.length < wordCount) {
        const index = Math.floor(Math.random() * palavras.length);
        if (!usedIndexes.has(index)) {
            let word = palavras[index];

            if (capitalizeAll || (capitalizeFirst && selectedWords.length === 0)) {
                word = word.charAt(0).toUpperCase() + word.slice(1);
            }

            selectedWords.push(word);
            usedIndexes.add(index);
        }
    }

    if (includeNumber) {
        const randomNumber = Math.floor(Math.random() * 100);
        selectedWords.push(randomNumber);
    }

    const passphrase = selectedWords.join(separator);
    document.getElementById("generated-passphrase").value = passphrase;
}

function calculateBreakTime(password) {
    const guessesPerSecond = 1e9;
    const charSetSize = getCharacterSetSize(password);
    const combinations = Math.pow(charSetSize, password.length);
    const seconds = combinations / guessesPerSecond;

    let estimation = "";
    if (seconds < 60) estimation = `Quebrável em ${seconds.toFixed(2)} segundos.`;
    else if (seconds < 3600) estimation = `Quebrável em ${(seconds / 60).toFixed(2)} minutos.`;
    else if (seconds < 86400) estimation = `Quebrável em ${(seconds / 3600).toFixed(2)} horas.`;
    else if (seconds < 31557600) estimation = `Quebrável em ${(seconds / 86400).toFixed(2)} dias.`;
    else estimation = `Quebrável em ${(seconds / 31557600).toFixed(2)} anos ou mais.`;

    document.getElementById("time-estimation").innerText = estimation;
}

function getCharacterSetSize(password) {
    let size = 0;
    if (/[a-z]/.test(password)) size += 26;
    if (/[A-Z]/.test(password)) size += 26;
    if (/[0-9]/.test(password)) size += 10;
    if (/[^a-zA-Z0-9]/.test(password)) size += 256;
    return size;
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand("copy");
    alert("Copiado para a área de transferência!");
}

document.getElementById("length-slider").addEventListener("input", function () {
    document.getElementById("length-value").innerText = this.value;
});

document.getElementById("words-slider").addEventListener("input", function () {
    document.getElementById("words-value").innerText = this.value;
});