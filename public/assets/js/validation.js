window.bloodlinkInitValidation=()=>{
  for(const form of document.querySelectorAll('form[data-validate]')){
    if(form.dataset.validationBound) continue;
    form.dataset.validationBound='1';
    form.addEventListener('submit',event=>{
      for(const old of form.querySelectorAll('.field-error')) old.remove();
      const errors=[];
      const field=(name)=>form.elements.namedItem(name);
      const value=(name)=>String(field(name)?.value??'').trim();
      const add=(name,message)=>{const input=field(name);if(input) errors.push([input,message]);};
      const kind=form.dataset.validate;
      if(kind==='auth'||kind==='user'){
        if((kind==='user'||field('nom'))&&(value('nom').length<2||value('nom').length>100)) add('nom','Saisissez un nom de 2 à 100 caractères.');
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))||value('email').length>190) add('email','Saisissez une adresse e-mail valide.');
        if(kind==='auth'){
          if(field('confirmation')){
            if(value('mot_de_passe').length<12) add('mot_de_passe','Utilisez au moins 12 caractères.');
            if(value('confirmation')!==value('mot_de_passe')) add('confirmation','Les mots de passe ne correspondent pas.');
          }else if(!value('mot_de_passe')){
            add('mot_de_passe','Saisissez votre mot de passe.');
          }
        }
        if(kind==='user'){
          const isNew=!value('id')||Number(value('id'))===0;
          if((isNew||value('mot_de_passe').length>0)&&value('mot_de_passe').length<12){
            add('mot_de_passe','Utilisez au moins 12 caractères.');
          }
        }
      }
      if(kind==='activation'){
        if(value('mot_de_passe').length<12) add('mot_de_passe','Utilisez au moins 12 caractères.');
        if(value('confirmation')!==value('mot_de_passe')) add('confirmation','Les mots de passe ne correspondent pas.');
      }
      if(['activation','profil','invitation','sos'].includes(kind)){
        if(field('telephone')&&!/^[+0-9 ()-]{8,30}$/.test(value('telephone'))) add('telephone','Téléphone invalide.');
        if(field('ville')&&(value('ville').length<2||value('ville').length>100)) add('ville','Saisissez une ville valide.');
        if(field('date_naissance')){
          const dob=value('date_naissance');
          const now=new Date();
          const maxDob=new Date(now.getFullYear()-18,now.getMonth(),now.getDate()).toISOString().slice(0,10);
          const minDob=new Date(now.getFullYear()-100,now.getMonth(),now.getDate()).toISOString().slice(0,10);
          if(!dob||dob>maxDob||dob<minDob) add('date_naissance','Date de naissance invalide (18 ans minimum).');
        }
      }
      if(['invitation','invitation-responsable'].includes(kind)){
        if(value('nom').length<2||value('nom').length>100) add('nom','Saisissez un nom valide.');
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value('email'))||value('email').length>190) add('email','Saisissez une adresse e-mail valide.');
        if(kind==='invitation'&&!field('consentement')?.checked) add('consentement','Le consentement est nécessaire.');
      }
      if(kind==='profil'&&(value('nom').length<2||value('nom').length>100)) add('nom','Saisissez votre nom (2 à 100 caractères).');
      if(kind==='password'){
        if(!value('ancien')) add('ancien','Saisissez votre mot de passe actuel.');
        if(value('nouveau').length<12) add('nouveau','Utilisez au moins 12 caractères.');
        if(value('nouveau')!==value('confirmation')) add('confirmation','Les mots de passe ne correspondent pas.');
      }
      if(kind==='sos'){
        if(value('lieu').length<5||value('lieu').length>200) add('lieu','Précisez le lieu (5 à 200 caractères).');
        if(!value('groupe_receveur')) add('groupe_receveur','Sélectionnez le groupe du receveur.');
        if(value('message').length>500) add('message','Message trop long (500 caractères maximum).');
        if(!field('consentement')?.checked) add('consentement','Le consentement est nécessaire.');
      }
      if(kind==='parametres'){
        if(value('nom_organisation').length<2||value('nom_organisation').length>100) add('nom_organisation','Nom du centre invalide (2 à 100 caractères).');
        const cap=Number(value('capacite_defaut'));
        if(!/^[0-9]+$/.test(value('capacite_defaut'))||cap<1||cap>100) add('capacite_defaut','Indiquez un nombre de places entre 1 et 100.');
      }
      if(kind==='don'&&(Number(value('volume_ml'))<420||Number(value('volume_ml'))>480)) add('volume_ml','Indiquez un volume de 420 à 480 ml.');
      if(kind==='scan'&&!/^[A-Fa-f0-9]{48}$/.test(value('code'))) add('code','Code QR invalide.');
      if(kind==='collecte'){
        if(value('titre').length<3||value('titre').length>160) add('titre','Titre invalide (3 à 160 caractères).');
        if(value('ville').length<2||value('ville').length>100) add('ville','Ville invalide (2 à 100 caractères).');
        if(value('adresse').length<5||value('adresse').length>255) add('adresse','Adresse invalide (5 à 255 caractères).');
        if(!value('debut')||!value('fin')||value('fin')<=value('debut')) add('fin','Vérifiez les dates de la collecte.');
      }
      if(kind==='creneau'){
        if(!value('debut')||!value('fin')||value('fin')<=value('debut')) add('fin','Vérifiez les horaires du créneau.');
        if(Number(value('capacite'))<1||Number(value('capacite'))>100) add('capacite','Indiquez une capacité entre 1 et 100.');
      }
      if(kind==='sur-place'&&!/^(?:[1-9][0-9]*|[A-Fa-f0-9]{12}|[A-Fa-f0-9]{48})$/.test(value('donneur_id'))) add('donneur_id','Saisissez un numéro de compte, un code de réservation ou un QR valide.');
      if(kind==='examen'){
        const ranges={
          tension_systolique:[70,250,'Tension systolique entre 70 et 250.'],
          tension_diastolique:[40,150,'Tension diastolique entre 40 et 150.'],
          pouls:[30,200,'Pouls entre 30 et 200.'],
          temperature_c:[34,42,'Température entre 34 et 42 °C.'],
          hemoglobine_g_dl:[5,25,'Hémoglobine entre 5 et 25 g/dL.'],
          poids_kg:[25,250,'Poids entre 25 et 250 kg.']
        };
        for(const [name,[min,max,msg]] of Object.entries(ranges)){
          const v=value(name);
          if(!v){
            if(value('decision')!=='EN_ATTENTE') add(name,'Remplissez cette mesure avant de choisir Apte ou Ajourné.');
          }else if(Number.isNaN(Number(v))||Number(v)<min||Number(v)>max){
            add(name,msg);
          }
        }
        if(value('tension_systolique')&&value('tension_diastolique')&&Number(value('tension_diastolique'))>=Number(value('tension_systolique'))){
          add('tension_diastolique','La tension diastolique doit être inférieure à la tension systolique.');
        }
      }
      if(kind==='resultat'){
        const statut=value('statut');
        const suivi=value('statut_prochain_don');
        const tests=['vih','vhb','vhc','syphilis'].map(k=>value(k));
        if(statut==='VALIDE'){
          if(!value('groupe_confirme')) add('groupe_confirme','Sélectionnez le groupe sanguin confirmé.');
          if(tests.some(t=>t!=='NON_REACTIF')) add('statut','La validation publique exige quatre dépistages non réactifs (aucune infection).');
        }
        if(suivi==='AUTORISE'){
          if(statut!=='VALIDE') add('statut_prochain_don','Un avis favorable exige des résultats validés.');
          if(!value('prochain_don_le')) add('prochain_don_le','Indiquez la prochaine date possible.');
        }
      }
      if(errors.length){
        event.preventDefault();
        for(const [input,message] of errors){
          const p=document.createElement('span');
          p.className='field-error';
          p.textContent=message;
          const wrap=input.closest('.password-input-wrap')||input;
          wrap.after(p);
        }
        errors[0][0].focus();
      }
    });
  }
  for(const form of document.querySelectorAll('form[data-confirm]')){
    if(form.dataset.confirmBound) continue;
    form.dataset.confirmBound='1';
    form.addEventListener('submit',e=>{if(!confirm(form.dataset.confirm))e.preventDefault()});
  }
};
document.addEventListener('DOMContentLoaded',window.bloodlinkInitValidation);
