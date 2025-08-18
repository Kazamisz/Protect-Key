(function(){
  function getToastContainer(){
    let cont = document.querySelector('.toast-container');
    if(!cont){
      cont = document.createElement('div');
      cont.className = 'toast-container';
      document.body.appendChild(cont);
    }
    return cont;
  }
  function showToast(message, type='info', duration=2500){
    const cont = getToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    cont.appendChild(toast);
    let closed=false;
    const close=()=>{ if(closed) return; closed=true; toast.style.animation='toastOut .18s ease forwards'; setTimeout(()=>toast.remove(),180); };
    const t=setTimeout(close,duration);
    toast.addEventListener('click', ()=>{ clearTimeout(t); close(); });
  }
  window.showToast = showToast;
})();
