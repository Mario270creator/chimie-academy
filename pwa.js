(function(){
  if(!('serviceWorker' in navigator)) return;
  if(!/^https?:$/i.test(window.location.protocol || '')) return;
  window.addEventListener('load', function(){
    /* service worker dezactivat pe hosting simplu */
  });
})();
