document.addEventListener('DOMContentLoaded',()=>{
  for(const form of document.querySelectorAll('form[data-validate]')){
    form.addEventListener('submit',event=>{
      for(const old of form.querySelectorAll('.field-error')) old.remove();
      const errors=[];
      const field=(name)=>form.elements.namedItem(name);
      const value=(name)=>String(field(name)?.value??'').trim();
      const add=(name,message)=>{const input=field(name);if(input) errors.push([input,message]);};
      const kind=form.dataset.validate;
      if(kind==='auth'||kind==='user'){
        if((kind==='user'||field('nom'))&&value('nom').length<2) add('nom','Saisissez au moins 2 caractères.');
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))) add('email','Saisissez une adresse e-mail valide.');
        if((kind==='auth'&&field('confirmation'))||(kind==='user'&&!value('id'))){
          if(value('mot_de_passe').length<12) add('mot_de_passe','Utilisez au moins 12 caractères.');
        }
        if(field('confirmation')&&value('confirmation')!==value('mot_de_passe')) add('confirmation','Les mots de passe ne correspondent pas.');
      }
      if(kind==='activation'){if(value('mot_de_passe').length<12)add('mot_de_passe','Utilisez au moins 12 caractères.');if(value('confirmation')!==value('mot_de_passe'))add('confirmation','Les mots de passe ne correspondent pas.');}
      if(['activation','profil','invitation','sos'].includes(kind)){
        if(field('telephone')&&!/^[+0-9 ()-]{8,30}$/.test(value('telephone')))add('telephone','Téléphone invalide.');
        if(field('ville')&&value('ville').length<2)add('ville','Saisissez une ville.');
        if(field('date_naissance')&&(!value('date_naissance')||value('date_naissance')>new Date().toISOString().slice(0,10)))add('date_naissance','Date de naissance invalide.');
      }
      if(['invitation','invitation-responsable'].includes(kind)){if(value('nom').length<2)add('nom','Saisissez un nom.');if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email')))add('email','Saisissez une adresse e-mail valide.');if(kind==='invitation'&&!field('consentement')?.checked)add('consentement','Le consentement est nécessaire.');}
      if(kind==='profil'&&value('nom').length<2)add('nom','Saisissez votre nom.');
      if(kind==='password'){if(value('nouveau').length<12)add('nouveau','Utilisez au moins 12 caractères.');if(value('nouveau')!==value('confirmation'))add('confirmation','Les mots de passe ne correspondent pas.');}
      if(kind==='sos'){if(value('lieu').length<5)add('lieu','Précisez le lieu.');if(!value('groupe_receveur'))add('groupe_receveur','Sélectionnez le groupe du receveur.');if(!field('consentement')?.checked)add('consentement','Le consentement est nécessaire.');}
      if(kind==='don'&&(Number(value('volume_ml'))<420||Number(value('volume_ml'))>480))add('volume_ml','Indiquez un volume de 420 à 480 ml.');
      if(kind==='scan'&&!/^[a-f0-9]{48}$/.test(value('code')))add('code','Code QR invalide.');
      if(kind==='collecte'){
        for(const name of ['titre','ville','adresse']) if(!value(name)) add(name,'Ce champ est nécessaire.');
        if(!value('debut')||!value('fin')||value('fin')<=value('debut')) add('fin','Vérifiez les dates de la collecte.');
      }
      if(kind==='creneau'){
        if(!value('debut')||!value('fin')||value('fin')<=value('debut')) add('fin','Vérifiez les horaires du créneau.');
        if(Number(value('capacite'))<1) add('capacite','Indiquez une capacité positive.');
      }
      if(kind==='sur-place'&&Number(value('donneur_id'))<1) add('donneur_id','Indiquez un identifiant valide.');
      if(errors.length){event.preventDefault();for(const [input,message] of errors){const p=document.createElement('span');p.className='field-error';p.textContent=message;input.after(p);}errors[0][0].focus();}
    });
  }
  for(const form of document.querySelectorAll('form[data-confirm]')) form.addEventListener('submit',e=>{if(!confirm(form.dataset.confirm))e.preventDefault()});
});
