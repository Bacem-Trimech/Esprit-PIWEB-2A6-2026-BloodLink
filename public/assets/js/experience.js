document.addEventListener('DOMContentLoaded',()=>{
  function drawQr(element,value){
    if(typeof qrcode!=='function') return;
    const qr=qrcode(0,'M'); qr.addData(value); qr.make();
    element.innerHTML=qr.createSvgTag({cellSize:5,margin:4});
    element.querySelector('svg')?.setAttribute('aria-label','QR de BloodLink');
  }
  const donor=document.getElementById('donor-qr');
  if(donor) drawQr(donor,donor.dataset.code);
  document.querySelectorAll('.invitation-qr').forEach(el=>drawQr(el,new URL(el.dataset.link,location.href).href));
  function tick(){document.querySelectorAll('.sos-countdown').forEach(el=>{
    const remaining=Math.max(0,Math.floor((new Date(el.dataset.expires).getTime()-Date.now())/1000));
    if(!remaining){el.closest('.campaign-card')?.remove();return;}
    el.textContent='Temps restant : '+String(Math.floor(remaining/3600)).padStart(2,'0')+':'+String(Math.floor(remaining%3600/60)).padStart(2,'0')+':'+String(remaining%60).padStart(2,'0');
  });}
  tick();setInterval(tick,1000);
  const button=document.getElementById('scan-start');
  if(button) button.addEventListener('click',async()=>{
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
        if(value&&/^[a-f0-9]{48}$/.test(value)){
          stream.getTracks().forEach(t=>t.stop());
          location.href='index.php?page=scan-donneur&code='+encodeURIComponent(value);
          return;
        }
        setTimeout(scan,250);
      };scan();
    }catch(error){if(stream)stream.getTracks().forEach(t=>t.stop());message.textContent='Caméra indisponible. Saisissez le code ci-dessous.';}
  });
});
