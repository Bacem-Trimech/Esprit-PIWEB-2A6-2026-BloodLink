(() => {
  function revealPasswords(){
    document.querySelectorAll('input[type="password"]').forEach(input=>{
      if(input.dataset.eyeReady) return;
      input.dataset.eyeReady='1';
      const button=document.createElement('button');
      button.type='button'; button.className='password-eye';
      button.textContent='Voir'; button.setAttribute('aria-label','Afficher le mot de passe');
      input.insertAdjacentElement('afterend',button);
      button.addEventListener('click',()=>{
        const visible=input.type==='text';
        input.type=visible?'password':'text';
        button.textContent=visible?'Voir':'Masquer';
        button.setAttribute('aria-label',visible?'Afficher le mot de passe':'Masquer le mot de passe');
      });
    });
  }
  document.addEventListener('DOMContentLoaded',revealPasswords);
  document.addEventListener('submit',async event=>{
    const form=event.target;
    if(!(form instanceof HTMLFormElement)||form.method.toLowerCase()!=='post'||event.defaultPrevented) return;
    event.preventDefault();
    const submitter=event.submitter;
    const previousUrl=location.href;
    const previousScroll=window.scrollY;
    const openParticipations=[...document.querySelectorAll('details[open] form input[name="participation_id"]')].map(i=>i.value);
    if(submitter) submitter.disabled=true;
    try{
      const response=await fetch(form.action,{method:'POST',body:new FormData(form),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});
      const markup=await response.text();
      const next=new DOMParser().parseFromString(markup,'text/html');
      const sameUrl=new URL(response.url).search===new URL(form.action).search;
      if(!response.ok||(!response.redirected&&sameUrl&&next.querySelector('.notice'))){
        const feedback=next.querySelector('.notice,.empty-state p');
        const notice=document.createElement('p');
        notice.className='notice';notice.setAttribute('role','alert');
        notice.textContent=feedback?.textContent?.trim()||'Vérifiez les informations saisies, puis réessayez.';
        form.querySelectorAll('.notice').forEach(n=>n.remove());
        form.prepend(notice);
        notice.scrollIntoView({block:'nearest'});
        return;
      }
      document.title=next.title;
      document.body.className=next.body.className;
      document.body.innerHTML=next.body.innerHTML;
      history.pushState({},'',response.url);
      for(const id of openParticipations){
        const input=[...document.querySelectorAll('details form input[name="participation_id"]')].find(i=>i.value===id);
        if(input) input.closest('details').open=true;
      }
      window.bloodlinkInitValidation?.();
      window.bloodlinkInitExperience?.();
      revealPasswords();
      window.scrollTo({top:new URL(previousUrl).search===new URL(response.url).search?previousScroll:0,behavior:'instant'});
    }catch(error){
      const notice=document.createElement('p');notice.className='notice';notice.setAttribute('role','alert');
      notice.textContent='Connexion interrompue. Vérifiez le réseau et réessayez.';form.prepend(notice);
    }finally{if(submitter&&submitter.isConnected) submitter.disabled=false;}
  });
})();
