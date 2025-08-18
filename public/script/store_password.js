(function () {
  'use strict';

  // Referências de elementos
  const formContainer = document.getElementById('formContainer');
  const savedTableSection = document.getElementById('savedTable');
  const noItemsImage = document.getElementById('img-senha');
  const addButton = document.querySelector('.botao-adicionar');

  const actionTypeEl = document.getElementById('actionType');
  const passwordIdEl = document.getElementById('passwordId');
  const siteNameEl = document.getElementById('siteName');
  const urlEl = document.getElementById('url');
  const loginNameEl = document.getElementById('loginName');
  const emailEl = document.getElementById('email');
  const passwordEl = document.getElementById('password');

  // Estado atual: 'idle' | 'add' | 'edit'
  let mode = 'idle';

  // Sistema simples de Toasts
  function getToastContainer() {
    let cont = document.querySelector('.toast-container');
    if (!cont) {
      cont = document.createElement('div');
      cont.className = 'toast-container';
      document.body.appendChild(cont);
    }
    return cont;
  }

  function showToast(message, type = 'info', duration = 2500) {
    const cont = getToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    cont.appendChild(toast);

    let closed = false;
    const close = () => {
      if (closed) return; closed = true;
      toast.style.animation = 'toastOut .18s ease forwards';
      setTimeout(() => toast.remove(), 180);
    };
    const t = setTimeout(close, duration);
    toast.addEventListener('click', () => { clearTimeout(t); close(); });
  }

  // Helpers visuais
  function show(el) {
    if (!el) return;
  el.classList.remove('is-hidden');
  el.classList.remove('forced-hidden');
  // remove inline none se existir
  if (el.style) el.style.display = '';
  }
  function hide(el) {
    if (!el) return;
  el.classList.add('is-hidden');
  // aplica forced-hidden para prevenir scripts que alterem display
  el.classList.add('forced-hidden');
  if (el.style) el.style.display = 'none';
  }

  function hasSavedRows() {
  const table = savedTableSection ? savedTableSection.querySelector('table') : null;
  if (!table) return false;
  const rows = table.querySelectorAll('tbody tr');
  return rows.length > 0;
  }

  function updateBodyFormOpen(on) {
    const cls = 'form-open';
    if (on) document.body.classList.add(cls); else document.body.classList.remove(cls);
  }

    function syncUI() {
    const anyRows = hasSavedRows();

    if (mode === 'add') {
      // Mostrar formulário e esconder tabela/imagem/botão
      formContainer.classList.add('show');
      hide(savedTableSection);
      hide(noItemsImage);
      hide(addButton);
      updateBodyFormOpen(true);
    } else if (mode === 'edit') {
      // Mostrar formulário e esconder tabela
      formContainer.classList.add('show');
      hide(savedTableSection);
      hide(noItemsImage);
      hide(addButton);
      updateBodyFormOpen(true);
    } else {
      // Idle
      formContainer.classList.remove('show');
      // anima a tabela ao voltar
      if (savedTableSection) {
        savedTableSection.classList.remove('fade-in-soft');
        // força reflow para reiniciar a animação
        void savedTableSection.offsetWidth;
        savedTableSection.classList.add('fade-in-soft');
      }
      show(savedTableSection);
      if (addButton) show(addButton);
      if (noItemsImage) {
        if (anyRows) {
          hide(noItemsImage);
        } else {
          show(noItemsImage);
        }
      }
      updateBodyFormOpen(false);
    }
  }

  function prepareAddForm() {
    actionTypeEl.value = 'add';
    passwordIdEl.value = '';
    siteNameEl.value = '';
    urlEl.value = '';
    loginNameEl.value = '';
    emailEl.value = '';
    passwordEl.value = '';
  }

  function clearForm() {
    // Mantém CSRF; limpa campos e volta para ação 'add'
    actionTypeEl.value = 'add';
    passwordIdEl.value = '';
    siteNameEl.value = '';
    urlEl.value = '';
    loginNameEl.value = '';
    emailEl.value = '';
    passwordEl.value = '';
  }

  // Expostos globalmente para manter o HTML existente (onclick="...")
  window.toggleForm = function () {
    if (mode === 'add') {
      mode = 'idle';
      clearForm();
    } else {
      mode = 'add';
      prepareAddForm();
    }
    syncUI();
  };

  window.cancelForm = function () {
    mode = 'idle';
    clearForm();
    syncUI();
  };

  window.editPassword = function (id, siteName, url, loginName, email, password) {
    mode = 'edit';
    actionTypeEl.value = 'update';
    passwordIdEl.value = id;
    siteNameEl.value = siteName || '';
    urlEl.value = url || '';
    loginNameEl.value = loginName || '';
    emailEl.value = email || '';
    passwordEl.value = password || '';
    syncUI();
  };

  window.copyPassword = function (password) {
    if (!navigator.clipboard) {
      const ta = document.createElement('textarea');
      ta.value = password;
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); showToast('Senha copiada.', 'success'); }
      catch (e) { showToast('Não foi possível copiar.', 'error'); }
      document.body.removeChild(ta);
      return;
    }
    navigator.clipboard.writeText(password)
      .then(() => showToast('Senha copiada.', 'success'))
      .catch(() => showToast('Não foi possível copiar.', 'error'));
  };

  // Mostra a senha por 5s e volta a esconder automaticamente
  window.showPassword = function (btnEl, password) {
    try {
      const wrapper = btnEl.closest('.pw-wrapper');
      if (!wrapper) return;
      const valueEl = wrapper.querySelector('.pw-value');
      if (!valueEl) return;

      // Se já está mostrando, não empilhar timeouts: reseta
      const showing = wrapper.getAttribute('data-showing') === '1';
      if (showing) return;
      wrapper.setAttribute('data-showing', '1');

      // Captura e guarda a máscara inicial apenas uma vez
  // Sempre usa máscara padrão de 6 pontos
  valueEl.dataset.mask = '••••••';

      // Exibe a senha
      valueEl.textContent = password;

      // Após 5s, restaura sempre 6 pontos
      setTimeout(() => {
        valueEl.textContent = '••••••';
        wrapper.setAttribute('data-showing', '0');
      }, 3000);
    } catch (_) {
  // fallback simples
  showToast('Não foi possível exibir a senha.', 'error');
    }
  };

  window.gerarSenha = function (tamanho = 16) {
    if (!Number.isInteger(tamanho) || tamanho < 8) tamanho = 16;
    if (passwordEl.value) {
      // substitui confirm por toast informativo, e segue gerando
      showToast('Senha anterior substituída.', 'info');
    }
    const caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    const array = new Uint32Array(tamanho);
    if (window.crypto && window.crypto.getRandomValues) {
      window.crypto.getRandomValues(array);
    } else {
      for (let i = 0; i < tamanho; i++) array[i] = Math.floor(Math.random() * 4294967296);
    }
    let senha = '';
    for (let i = 0; i < tamanho; i++) {
      senha += caracteres[array[i] % caracteres.length];
    }
    passwordEl.value = senha;
  showToast('Senha gerada.', 'success');
  };

  window.verSenha = function () {
    const togglePasswordImage = document.getElementById('togglePasswordImage');
    if (passwordEl.type === 'password') {
      passwordEl.type = 'text';
      if (togglePasswordImage) {
        togglePasswordImage.classList.remove('fa-eye');
        togglePasswordImage.classList.add('fa-eye-slash');
      }
    } else {
      passwordEl.type = 'password';
      if (togglePasswordImage) {
        togglePasswordImage.classList.remove('fa-eye-slash');
        togglePasswordImage.classList.add('fa-eye');
      }
    }
  };

  // Estado inicial ao carregar
  document.addEventListener('DOMContentLoaded', function(){
    syncUI();
    // Garante que todas as máscaras estejam salvas para restauração futura
    document.querySelectorAll('.pw-value').forEach(el => {
      el.textContent = '••••••';
      el.dataset.mask = '••••••';
    });
    // efeito hover que segue o mouse no botão Adicionar
    const addBtn = document.querySelector('.botao-adicionar');
    if (addBtn) {
      addBtn.addEventListener('pointermove', (e) => {
        const rect = addBtn.getBoundingClientRect();
        const x = e.clientX - rect.left; const y = e.clientY - rect.top;
        addBtn.style.setProperty('--x', `${x}px`);
        addBtn.style.setProperty('--y', `${y}px`);
      });
    }
  });
})();
