(() => {
  const EYE_OPEN_SVG = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  const EYE_OFF_SVG = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  function revealPasswords(){
    document.querySelectorAll('input[type="password"]').forEach(input=>{
      if(input.dataset.eyeReady) return;
      input.dataset.eyeReady='1';
      const wrap=document.createElement('div');
      wrap.className='password-input-wrap';
      input.parentNode.insertBefore(wrap,input);
      wrap.appendChild(input);

      const button=document.createElement('button');
      button.type='button';
      button.className='password-eye-btn';
      button.innerHTML=EYE_OPEN_SVG;
      button.setAttribute('aria-label','Afficher le mot de passe');
      button.setAttribute('title','Afficher / masquer le mot de passe');
      wrap.appendChild(button);

      button.addEventListener('click',()=>{
        const visible=input.type==='text';
        input.type=visible?'password':'text';
        button.innerHTML=visible?EYE_OPEN_SVG:EYE_OFF_SVG;
        button.classList.toggle('is-visible',!visible);
        button.setAttribute('aria-label',visible?'Afficher le mot de passe':'Masquer le mot de passe');
      });
    });
  }

  function showSaveToast(text = 'Modifications enregistrées avec succès'){
    let container=document.getElementById('bloodlink-toast-container');
    if(!container){
      container=document.createElement('div');
      container.id='bloodlink-toast-container';
      container.className='toast-container';
      document.body.appendChild(container);
    }
    const toast=document.createElement('div');
    toast.className='save-toast';
    toast.setAttribute('role','status');
    toast.innerHTML='<span class="save-toast-icon">✓</span><span>'+text+'</span>';
    container.appendChild(toast);
    requestAnimationFrame(()=>toast.classList.add('show'));
    setTimeout(()=>{
      toast.classList.remove('show');
      setTimeout(()=>toast.remove(),260);
    },3200);
  }

  window.bloodlinkShowToast=showSaveToast;
  document.addEventListener('DOMContentLoaded',revealPasswords);

  document.addEventListener('submit',async event=>{
    const form=event.target;
    if(!(form instanceof HTMLFormElement)||form.method.toLowerCase()!=='post'||event.defaultPrevented) return;
    event.preventDefault();
    const submitter=event.submitter;
    const previousUrl=location.href;
    const previousScroll=window.scrollY;
    const activeTabBtn=document.querySelector('[data-workspace-tabs] .wtab.active');
    const activeTabTarget=activeTabBtn?.dataset.tabTarget||null;
    if(submitter) submitter.disabled=true;
    try{
      const response=await fetch(form.action,{method:'POST',body:new FormData(form),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});
      const markup=await response.text();
      const next=new DOMParser().parseFromString(markup,'text/html');
      const sameUrl=new URL(response.url).search===new URL(form.action).search;
      if(!response.ok||(!response.redirected&&sameUrl&&next.querySelector('.notice'))){
        const feedbacks=[...next.querySelectorAll('.notice,.empty-state p')].map(n=>n.textContent?.trim()).filter(Boolean);
        const notice=document.createElement('p');
        notice.className='notice';notice.setAttribute('role','alert');
        notice.textContent=feedbacks.length?feedbacks.join(' '):'Vérifiez les informations saisies, puis réessayez.';
        form.querySelectorAll('.notice').forEach(n=>n.remove());
        form.prepend(notice);
        notice.scrollIntoView({block:'nearest'});
        return;
      }
      document.title=next.title;
      document.body.className=next.body.className;
      document.body.innerHTML=next.body.innerHTML;
      history.pushState({},'',response.url);

      window.bloodlinkInitValidation?.();
      window.bloodlinkInitExperience?.();
      revealPasswords();

      // Restaure l'onglet actif dans le poste responsable (tout en laissant la fenêtre modale fermée après sauvegarde)
      if(activeTabTarget){
        const restoredTabBtn=document.querySelector(`[data-workspace-tabs] .wtab[data-tab-target="${activeTabTarget}"]`);
        restoredTabBtn?.click();
      }

      const actionUrl=form.getAttribute('action')||'';
      if(!actionUrl.includes('deconnexion')&&!actionUrl.includes('connexion')){
        showSaveToast('Enregistré avec succès');
      }

      window.scrollTo({top:new URL(previousUrl).search===new URL(response.url).search?previousScroll:0,behavior:'instant'});
    }catch(error){
      const notice=document.createElement('p');notice.className='notice';notice.setAttribute('role','alert');
      notice.textContent='Connexion interrompue. Vérifiez le réseau et réessayez.';form.prepend(notice);
    }finally{if(submitter&&submitter.isConnected) submitter.disabled=false;}
  });
})();
