window.bloodlinkInitExperience=()=>{
  function drawQr(element,value){
    if(typeof qrcode!=='function'||!value) return;
    const qr=qrcode(0,'M'); qr.addData(value); qr.make();
    element.innerHTML=qr.createSvgTag({cellSize:5,margin:4});
    element.querySelector('svg')?.setAttribute('aria-label','QR de BloodLink');
  }
  const donor=document.getElementById('donor-qr');
  if(donor) drawQr(donor,donor.dataset.code);
  document.querySelectorAll('.invitation-qr').forEach(el=>drawQr(el,new URL(el.dataset.link,location.href).href));

  function tick(){
    document.querySelectorAll('.sos-countdown').forEach(el=>{
      const remaining=Math.max(0,Math.floor((new Date(el.dataset.expires).getTime()-Date.now())/1000));
      if(!remaining){el.closest('.sos-alert-card,.campaign-card')?.remove();return;}
      el.textContent='⏱ '+String(Math.floor(remaining/3600)).padStart(2,'0')+':'+String(Math.floor(remaining%3600/60)).padStart(2,'0')+':'+String(remaining%60).padStart(2,'0');
    });
    document.querySelectorAll('.cooldown-timer[data-cooldown-until]').forEach(el=>{
      const target=new Date(el.dataset.cooldownUntil).getTime();
      if(Number.isNaN(target)) return;
      const diff=Math.max(0,Math.floor((target-Date.now())/1000));
      if(diff<=0){
        el.textContent='Éligible dès maintenant';
        return;
      }
      const days=Math.floor(diff/86400);
      const hours=Math.floor((diff%86400)/3600);
      const mins=Math.floor((diff%3600)/60);
      const secs=diff%60;
      el.textContent=`${days}j ${String(hours).padStart(2,'0')}h ${String(mins).padStart(2,'0')}m ${String(secs).padStart(2,'0')}s`;
    });
  }
  tick();
  if(!window.bloodlinkTicker) window.bloodlinkTicker=setInterval(tick,1000);

  // Convertit tous les champs datetime-local en sélecteur Date + Horloge 24h (00:00 à 23:59, sans AM/PM)
  document.querySelectorAll('input[type="datetime-local"]').forEach(input=>{
    if(input.dataset.clock24Ready) return;
    input.dataset.clock24Ready='1';
    const raw=(input.value||'').replace(' ','T');
    const [initDate='',initTime='09:00']=raw.split('T');
    const [initH='09',initM='00']=initTime.slice(0,5).split(':');

    const wrap=document.createElement('div');
    wrap.className='datetime-24h-widget';

    const dateInput=document.createElement('input');
    dateInput.type='date';
    dateInput.className='dt24-date';
    dateInput.value=initDate;
    dateInput.setAttribute('aria-label','Date');

    const timeGroup=document.createElement('div');
    timeGroup.className='dt24-time-group';

    const hourSelect=document.createElement('select');
    hourSelect.className='dt24-hour';
    hourSelect.setAttribute('aria-label','Heure (24h)');
    for(let h=0;h<24;h++){
      const hv=String(h).padStart(2,'0');
      const opt=document.createElement('option');
      opt.value=hv;
      opt.textContent=hv+' h';
      if(hv===initH) opt.selected=true;
      hourSelect.appendChild(opt);
    }

    const sep=document.createElement('span');
    sep.className='dt24-sep';
    sep.textContent=':';

    const minSelect=document.createElement('select');
    minSelect.className='dt24-min';
    minSelect.setAttribute('aria-label','Minutes');
    const curMinNum=Number(initM)||0;
    const minSteps=new Set([0,5,10,15,20,25,30,35,40,45,50,55,curMinNum]);
    [...minSteps].sort((a,b)=>a-b).forEach(m=>{
      const mv=String(m).padStart(2,'0');
      const opt=document.createElement('option');
      opt.value=mv;
      opt.textContent=mv;
      if(mv===String(curMinNum).padStart(2,'0')) opt.selected=true;
      minSelect.appendChild(opt);
    });

    timeGroup.append(hourSelect,sep,minSelect);
    wrap.append(dateInput,timeGroup);

    input.type='hidden';
    input.after(wrap);

    const sync=()=>{
      if(dateInput.value){
        input.value=`${dateInput.value}T${hourSelect.value}:${minSelect.value}`;
      }else{
        input.value='';
      }
    };
    sync();
    dateInput.addEventListener('change',sync);
    hourSelect.addEventListener('change',sync);
    minSelect.addEventListener('change',sync);
  });

  // Recherche instantanée simple dans les tableaux
  document.querySelectorAll('input[data-table-search]').forEach(input=>{
    if(input.dataset.searchBound) return;
    input.dataset.searchBound='1';
    input.addEventListener('input',()=>{
      const table=document.querySelector(input.dataset.tableSearch);
      if(!table) return;
      const query=input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr[data-search-row]').forEach(row=>{
        const text=row.textContent?.toLowerCase()||'';
        row.hidden=query!=='' && !text.includes(query);
      });
    });
  });

  // Filtrage avancé par Dates (Du / Au), Horaires 24h (ex. entre 10:00 et 12:00) et Mot-clé
  document.querySelectorAll('[data-collecte-filter-root]').forEach(panel=>{
    if(panel.dataset.filterBound) return;
    panel.dataset.filterBound='1';
    const selector=panel.dataset.collecteFilterRoot;
    const qInput=panel.querySelector('[data-filter-query]');
    const dFromInput=panel.querySelector('[data-filter-date-from]');
    const dToInput=panel.querySelector('[data-filter-date-to]');
    const tFromInput=panel.querySelector('[data-filter-time-from]');
    const tToInput=panel.querySelector('[data-filter-time-to]');
    const resetBtn=panel.querySelector('[data-filter-reset]');
    const countBadge=panel.querySelector('[data-filter-count]');

    const applyFilter=()=>{
      const items=document.querySelectorAll(selector);
      const q=(qInput?.value||'').trim().toLowerCase();
      const dFrom=dFromInput?.value||'';
      const dTo=dToInput?.value||'';
      const tFrom=tFromInput?.value||'';
      const tTo=tToInput?.value||'';
      let visibleCount=0;

      items.forEach(item=>{
        let match=true;
        if(q){
          const txt=(item.textContent||'').toLowerCase();
          if(!txt.includes(q)) match=false;
        }
        const itemStartD=item.dataset.startDate||'';
        const itemEndD=item.dataset.endDate||itemStartD;
        if(match && dFrom && itemEndD && itemEndD<dFrom) match=false;
        if(match && dTo && itemStartD && itemStartD>dTo) match=false;

        if(match && (tFrom || tTo)){
          const winStart=tFrom||'00:00';
          const winEnd=tTo||'23:59';
          const slotTimes=(item.dataset.slotTimes||'').split(',').filter(Boolean);
          if(slotTimes.length>0){
            const slotMatches=slotTimes.some(range=>{
              const [s,e]=range.split('-');
              return s && e && s<winEnd && e>winStart;
            });
            if(!slotMatches) match=false;
          }else{
            const itemStartT=item.dataset.startTime||'00:00';
            const itemEndT=item.dataset.endTime||'23:59';
            if(!(itemStartT<winEnd && itemEndT>winStart)) match=false;
          }
        }

        item.hidden=!match;
        if(match) visibleCount++;
      });

      if(countBadge){
        const isSlot=selector.includes('slot');
        countBadge.textContent=`${visibleCount} ${isSlot?'créneau(x)':'collecte(s)'}`;
      }
    };

    [qInput,dFromInput,dToInput,tFromInput,tToInput].forEach(ctrl=>{
      ctrl?.addEventListener('input',applyFilter);
      ctrl?.addEventListener('change',applyFilter);
    });
    resetBtn?.addEventListener('click',()=>{
      [qInput,dFromInput,dToInput,tFromInput,tToInput].forEach(ctrl=>{if(ctrl) ctrl.value='';});
      applyFilter();
    });
  });

  // Aperçu 1 : Photo principale de la carte « Collectes à venir »
  document.querySelectorAll('input[data-cover-file]').forEach(fileInput=>{
    if(fileInput.dataset.coverBound) return;
    fileInput.dataset.coverBound='1';
    fileInput.addEventListener('change',()=>{
      const img=document.querySelector(fileInput.dataset.coverFile);
      const file=fileInput.files?.[0];
      if(img && file){
        img.src=URL.createObjectURL(file);
      }
    });
  });
  document.querySelectorAll('select[data-cover-preset]').forEach(select=>{
    if(select.dataset.presetBound) return;
    select.dataset.presetBound='1';
    select.addEventListener('change',()=>{
      const img=document.querySelector(select.dataset.coverPreset);
      const opt=select.selectedOptions?.[0];
      const src=opt?.dataset.src||select.value;
      if(img && src && src!=='CONSERVER'){
        img.src=src;
      }
    });
  });

  // Aperçu 2 : Galerie de la page détail (jusqu'à 4 images max) située à gauche
  document.querySelectorAll('input[data-gallery-files]').forEach(fileInput=>{
    if(fileInput.dataset.galleryBound) return;
    fileInput.dataset.galleryBound='1';
    fileInput.addEventListener('change',()=>{
      const container=document.querySelector(fileInput.dataset.galleryFiles);
      const maxFiles=Number(fileInput.dataset.maxFiles||4);
      const files=[...(fileInput.files||[])].slice(0,maxFiles);
      if(!container||!files.length) return;
      container.innerHTML='';
      files.forEach((file,idx)=>{
        const item=document.createElement('div');
        item.className='gallery-thumb-item';
        const img=document.createElement('img');
        img.src=URL.createObjectURL(file);
        img.alt='Photo détail '+(idx+1);
        const label=document.createElement('small');
        label.textContent=`Photo ${idx+1}/${maxFiles}`;
        item.append(img,label);
        container.appendChild(item);
      });
    });
  });

  // Changement de photo active dans la galerie à gauche sur la page détail d'une collecte
  document.querySelectorAll('[data-gallery-thumb]').forEach(btn=>{
    if(btn.dataset.thumbBound) return;
    btn.dataset.thumbBound='1';
    btn.addEventListener('click',()=>{
      const target=document.querySelector(btn.dataset.galleryTarget);
      if(target){
        target.src=btn.dataset.galleryThumb;
      }
      btn.closest('.hero-thumbs-strip')?.querySelectorAll('[data-gallery-thumb]').forEach(b=>{
        b.classList.toggle('active',b===btn);
      });
    });
  });

  // Onglets rapides du poste de suivi responsable
  document.querySelectorAll('[data-workspace-tabs]').forEach(tabBar=>{
    if(tabBar.dataset.tabsBound) return;
    tabBar.dataset.tabsBound='1';
    const buttons=tabBar.querySelectorAll('.wtab[data-tab-target]');
    buttons.forEach(btn=>{
      btn.addEventListener('click',()=>{
        const targetId=btn.dataset.tabTarget;
        buttons.forEach(b=>{
          const active=b===btn;
          b.classList.toggle('active',active);
          b.setAttribute('aria-selected',active?'true':'false');
        });
        document.querySelectorAll('.wtab-panel').forEach(panel=>{
          const match=panel.id===targetId;
          panel.classList.toggle('active',match);
          panel.hidden=!match;
        });
      });
    });
  });

  // Bouton de repli / affichage de la barre latérale gauche (Admin & Responsable)
  if(localStorage.getItem('bloodlink_sidebar_collapsed')==='1'){
    document.body.classList.add('sidebar-collapsed');
  }
  document.querySelectorAll('[data-toggle-sidebar]').forEach(btn=>{
    if(btn.dataset.sidebarBound) return;
    btn.dataset.sidebarBound='1';
    btn.addEventListener('click',()=>{
      const collapsed=document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem('bloodlink_sidebar_collapsed',collapsed?'1':'0');
    });
  });

  // Réinitialise les formulaires non sauvegardés d'une fenêtre modale lorsqu'on la ferme sans enregistrer
  function resetModalForms(modal){
    if(!modal) return;
    modal.querySelectorAll('form').forEach(form=>{
      form.reset();
      form.querySelectorAll('.field-error,.notice').forEach(el=>el.remove());
      form.querySelectorAll('input[data-clock24-ready]').forEach(input=>{
        input.value=input.defaultValue;
        const widget=input.nextElementSibling;
        if(widget&&widget.classList.contains('datetime-24h-widget')){
          const raw=(input.defaultValue||'').replace(' ','T');
          const [initDate='',initTime='09:00']=raw.split('T');
          const [initH='09',initM='00']=initTime.slice(0,5).split(':');
          const dateEl=widget.querySelector('.dt24-date');
          const hourEl=widget.querySelector('.dt24-hour');
          const minEl=widget.querySelector('.dt24-min');
          if(dateEl) dateEl.value=initDate;
          if(hourEl) hourEl.value=initH;
          if(minEl) minEl.value=String(Number(initM)||0).padStart(2,'0');
        }
      });
    });
  }
  window.bloodlinkResetModal=resetModalForms;

  // Synchronisation automatique du statut labo si un dépistage est marqué Positif / Anomalie (A_CONFIRMER)
  document.querySelectorAll('form[data-validate="resultat"]').forEach(form=>{
    if(form.dataset.labSyncBound) return;
    form.dataset.labSyncBound='1';
    const testNames=['vih','vhb','vhc','syphilis'];
    const statutSelect=form.elements.namedItem('statut');
    const suiviSelect=form.elements.namedItem('statut_prochain_don');
    testNames.forEach(name=>{
      const sel=form.elements.namedItem(name);
      sel?.addEventListener('change',()=>{
        const vals=testNames.map(k=>form.elements.namedItem(k)?.value||'EN_ATTENTE');
        if(vals.includes('A_CONFIRMER')){
          if(statutSelect) statutSelect.value='CONTACT_CENTRE';
          if(suiviSelect && suiviSelect.value==='AUTORISE') suiviSelect.value='NON_AUTORISE';
        }
      });
    });
  });

  // Ouverture / fermeture des fenêtres modales cliniques et détails de collecte
  document.querySelectorAll('[data-open-modal]').forEach(btn=>{
    if(btn.dataset.modalBound) return;
    btn.dataset.modalBound='1';
    btn.addEventListener('click',()=>{
      const modal=document.getElementById(btn.dataset.openModal);
      if(modal){
        resetModalForms(modal);
        modal.hidden=false;
        modal.querySelector('input:not([type="hidden"]),select,textarea,button')?.focus();
      }
    });
  });

  document.querySelectorAll('.clickable-collecte-row').forEach(row=>{
    if(row.dataset.rowClickBound) return;
    row.dataset.rowClickBound='1';
    row.addEventListener('click',e=>{
      if(e.target.closest('button,a,input,select,form')) return;
      row.querySelector('[data-open-modal]')?.click();
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn=>{
    if(btn.dataset.closeBound) return;
    btn.dataset.closeBound='1';
    btn.addEventListener('click',()=>{
      const modal=btn.closest('.modal-backdrop');
      if(modal){
        resetModalForms(modal);
        modal.hidden=true;
      }
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach(backdrop=>{
    if(backdrop.dataset.backdropBound) return;
    backdrop.dataset.backdropBound='1';
    backdrop.addEventListener('click',e=>{
      if(e.target===backdrop){
        resetModalForms(backdrop);
        backdrop.hidden=true;
      }
    });
  });

  const button=document.getElementById('scan-start');
  if(button && !button.dataset.scanBound){
    button.dataset.scanBound='1';
    button.addEventListener('click',async()=>{
      const message=document.getElementById('scan-message');
      if(!('BarcodeDetector' in window)||!navigator.mediaDevices?.getUserMedia){message.textContent='Caméra QR non disponible dans ce navigateur. Saisissez le code ci-dessous.';return;}
      let stream;
      try{
        const detector=new BarcodeDetector({formats:['qr_code']});
        stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}});
        const video=document.getElementById('scan-video'); video.srcObject=stream;video.hidden=false;
        message.textContent='Présentez le QR devant la caméra.';
        const scan=async()=>{
          if(!video.isConnected){stream.getTracks().forEach(t=>t.stop());return;}
          const codes=await detector.detect(video);
          const value=codes[0]?.rawValue;
          if(value&&/^[A-Fa-f0-9]{48}$/.test(value)){
            stream.getTracks().forEach(t=>t.stop());
            location.href='index.php?page=scan-donneur&code='+encodeURIComponent(value.toLowerCase());
            return;
          }
          setTimeout(scan,250);
        };scan();
      }catch(error){if(stream)stream.getTracks().forEach(t=>t.stop());message.textContent='Caméra indisponible. Saisissez le code ci-dessous.';}
    });
  }
};
document.addEventListener('DOMContentLoaded',window.bloodlinkInitExperience);
document.addEventListener('keydown',e=>{
  if(e.key==='Escape'){
    document.querySelectorAll('.modal-backdrop:not([hidden])').forEach(m=>{
      window.bloodlinkResetModal?.(m);
      m.hidden=true;
    });
  }
});
