/* ANU admin theme: user badge, "Find a page" box, collapsible menu groups, remembered scroll. Pure enhancement; safe if markup differs. */
(function(){
  var me=document.currentScript||document.querySelector('script[src*="admin-theme.js"]');
  var LOGO=me&&me.src?me.src.replace(/js\/admin-theme\.js.*$/,'images/anu-logo.png'):'';
  function ready(f){document.readyState!=='loading'?f():document.addEventListener('DOMContentLoaded',f)}
  function ls(k,v){try{if(v===undefined)return localStorage.getItem(k);localStorage.setItem(k,v)}catch(e){return null}}
  ready(function(){
    var page=(location.pathname.split('/').pop()||'').replace(/\.(php|html?)$/,'').replace(/[^a-z0-9_-]/gi,'');
    if(page)document.body.classList.add('ad-page-'+page);

    /* login pages: university logo, centred above the form */
    var side=document.querySelector('.admin-sidebar');
    if(!side){
      var pw=document.querySelector('input[type=password]');
      if(pw&&LOGO){var f=pw.closest('form');
        if(f&&!document.querySelector('img[src*="logo"]')&&!document.querySelector('.ad-loginlogo')){
          var li=document.createElement('img');li.src=LOGO;li.alt='Africa Nazarene University';li.className='ad-loginlogo';
          f.parentNode.insertBefore(li,f);}}
      return;
    }

    /* logo in the top bar */
    var tb=document.querySelector('.admin-topbar');
    if(tb&&LOGO&&!tb.querySelector('.ad-toplogo')){
      var tl=document.createElement('img');tl.src=LOGO;tl.alt='Africa Nazarene University';tl.className='ad-toplogo';
      var first=tb.firstElementChild,target=(first&&first.classList.contains('topbar-left'))?first:tb;
      var mb=target.querySelector('.admin-menu-btn,.sidebar-toggle');
      if(mb&&mb.nextSibling)mb.parentNode.insertBefore(tl,mb.nextSibling);else target.insertBefore(tl,target.firstChild);
    }

    /* sidebar logo if the layout has none */
    var br=side.querySelector('.brand,.sidebar-brand');
    if(br&&LOGO&&!br.querySelector('img')){var bl=document.createElement('img');bl.src=LOGO;bl.alt='Africa Nazarene University';br.insertBefore(bl,br.firstChild)}

    /* Site Admin Accounts: filter box */
    if(document.body.classList.contains('ad-page-site_admins')){
      var tbl=document.querySelector('.admin-main table tbody');
      if(tbl){var card=tbl.closest('.card-anu')||tbl.closest('table'),fi=document.createElement('input');
        fi.type='search';fi.className='form-control ad-filter';fi.placeholder='Filter sites...';fi.setAttribute('aria-label','Filter sites');
        card.parentNode.insertBefore(fi,card);
        fi.addEventListener('input',function(){var q=fi.value.trim().toLowerCase();[].forEach.call(tbl.rows,function(r){r.style.display=(!q||r.textContent.toLowerCase().indexOf(q)>-1)?'':'none'})});}
    }

    var nav=side.querySelector('nav')||side;

    /* user badge */
    var pill=document.querySelector('.admin-user-pill');
    if(pill&&!pill.querySelector('.ad-av')){
      var strong=pill.querySelector('strong'),name=strong?strong.textContent.trim():'',role=(pill.textContent.split('·')[1]||'').trim();
      var ini=name.split(/\s+/).filter(Boolean).slice(0,2).map(function(w){return w[0]}).join('').toUpperCase()||'A';
      pill.innerHTML='<span class="ad-av"></span><span class="ad-who"><b></b><span></span></span>';
      pill.querySelector('.ad-av').textContent=ini; pill.querySelector('b').textContent=name||'Administrator';
      pill.querySelector('.ad-who span').textContent=role.replace(/_/g,' ');
      pill.title=(name?name+' · ':'')+role.replace(/_/g,' ');
    }

    /* groups */
    var labels=[].slice.call(nav.querySelectorAll('.nav-group-label'));
    function members(l){var out=[],n=l.nextElementSibling;while(n&&!n.classList.contains('nav-group-label')&&!n.classList.contains('logout-block')){out.push(n);n=n.nextElementSibling}return out}
    var saved={};try{saved=JSON.parse(ls('anu_admin_groups')||'{}')}catch(e){}
    labels.forEach(function(l){
      var key=l.textContent.trim(),m=members(l),hasActive=m.some(function(x){return x.classList&&x.classList.contains('active')});
      l.setAttribute('role','button');l.setAttribute('tabindex','0');
      function apply(c){l.classList.toggle('is-collapsed',c);l.setAttribute('aria-expanded',c?'false':'true');m.forEach(function(x){x.classList.toggle('is-hidden',c)})}
      apply(!hasActive&&saved[key]===1);
      function tog(){var c=!l.classList.contains('is-collapsed');apply(c);saved[key]=c?1:0;ls('anu_admin_groups',JSON.stringify(saved))}
      l.addEventListener('click',tog);l.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();tog()}});
    });

    /* find a page */
    var box=document.createElement('div');box.className='ad-find';
    box.innerHTML='<input type="search" id="adFind" placeholder="Find a page  ( / )" aria-label="Find an admin page" autocomplete="off">';
    var brand=side.querySelector('.brand');
    if(brand&&brand.nextSibling)brand.parentNode.insertBefore(box,brand.nextSibling);else nav.insertBefore(box,nav.firstChild);
    var input=box.querySelector('input'),links=[].slice.call(nav.querySelectorAll('a'));
    input.addEventListener('input',function(){
      var q=input.value.trim().toLowerCase();
      if(!q){labels.forEach(function(l){l.classList.remove('is-hidden');var c=l.classList.contains('is-collapsed');members(l).forEach(function(x){x.classList.toggle('is-hidden',c)})});return}
      labels.forEach(function(l){var any=false;members(l).forEach(function(x){var hit=(x.textContent||'').toLowerCase().indexOf(q)>-1;x.classList.toggle('is-hidden',!hit);if(hit)any=true});l.classList.toggle('is-hidden',!any)});
    });
    input.addEventListener('keydown',function(e){if(e.key==='Enter'){var vis=links.filter(function(a){return a.offsetParent&&!a.closest('.logout-block')})[0];if(vis)location.href=vis.href}if(e.key==='Escape'){input.value='';input.dispatchEvent(new Event('input'));input.blur()}});
    document.addEventListener('keydown',function(e){if(e.key==='/'&&!/input|textarea|select/i.test((e.target.tagName||''))&&!e.target.isContentEditable){e.preventDefault();input.focus()}});

    /* keep the menu where it was between pages */
    try{var y=sessionStorage.getItem('anu_admin_scroll');if(y)side.scrollTop=+y;window.addEventListener('beforeunload',function(){sessionStorage.setItem('anu_admin_scroll',side.scrollTop)});
      var act=nav.querySelector('a.active');if(act&&!y){act.scrollIntoView({block:'center'})}}catch(e){}
  });
})();
