<?php
/**
 * HubSpot chat and forms, loaded ONLY after the visitor accepts cookies (Kenya Data Protection Act, 2019).
 * The Hub ID is stored in the site settings as "hubspot_portal_id" (empty = nothing is loaded, nothing is shown).
 * One choice is remembered per browser (localStorage "anu_cookie_consent": accepted / declined) and a "Cookie settings"
 * link is added next to the Data Protection link in the footer so visitors can change their mind at any time.
 *
 *   anu_hubspot_render($hubId, ['privacy' => '/privacy', 'dp' => '/data-protection']);
 */
if (!function_exists('anu_hubspot_render')) {
    /** Clean Hub ID (digits only) or ''. */
    function anu_hubspot_id($raw): string {
        $raw = trim((string)$raw);
        if (preg_match('~hs-scripts\.com/(\d{4,12})\.js~', $raw, $m)) return $m[1];   // a pasted script link also works
        return preg_match('~^\d{4,12}$~', $raw) ? $raw : '';
    }

    /** Network sites (shared database): read the setting, creating the empty row once so it appears in Admin > Site Settings. */
    function anu_hubspot_network_id(): string {
        static $id = null; if ($id !== null) return $id;
        try {
            $st = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = 'hubspot_portal_id'"); $st->execute();
            $v = $st->fetchColumn();
            if ($v === false) { db()->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('hubspot_portal_id', '')")->execute(); $v = ''; }
            return $id = anu_hubspot_id($v);
        } catch (Throwable $e) { return $id = ''; }
    }

    function anu_hubspot_render(string $hubId, array $o = []): void {
        $hubId = anu_hubspot_id($hubId); if ($hubId === '') return;
        $privacy = $o['privacy'] ?? '/privacy'; $dp = $o['dp'] ?? '/data-protection';
        $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        ?>
<div id="anuCookieBar" role="dialog" aria-live="polite" aria-label="Cookie choices" hidden style="position:fixed;left:0;right:0;bottom:0;z-index:2147483000;background:#111;color:#fff;padding:14px 18px;box-shadow:0 -6px 24px rgba(0,0,0,.3);border-top:3px solid #FAC823;font:14px/1.5 Roboto,Arial,sans-serif">
  <div style="max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;gap:12px 20px">
    <p style="margin:0;flex:1 1 420px;color:#fff">We use essential cookies to keep this site working. With your permission we also use HubSpot for our chat and enquiry tools, which sets cookies and records how you use the chat. Your data is handled under Kenya's Data Protection Act, 2019. See our <a href="<?= $e($privacy) ?>" style="color:#FAC823">Privacy Policy</a> and <a href="<?= $e($dp) ?>" style="color:#FAC823">Data Protection Notice</a>.</p>
    <div style="display:flex;gap:10px;flex:0 0 auto">
      <button type="button" id="anuCookieNo" style="background:transparent;color:#fff;border:2px solid #fff;border-radius:6px;padding:.45rem 1.1rem;font-weight:700;cursor:pointer">Decline</button>
      <button type="button" id="anuCookieYes" style="background:#FAC823;color:#111;border:2px solid #FAC823;border-radius:6px;padding:.45rem 1.2rem;font-weight:700;cursor:pointer">Accept</button>
    </div>
  </div>
</div>
<script>
(function(){
  var KEY='anu_cookie_consent', HUB='<?= $hubId ?>', bar=document.getElementById('anuCookieBar');
  function get(){ try{ return localStorage.getItem(KEY); }catch(e){ return null; } }
  function set(v){ try{ localStorage.setItem(KEY,v); }catch(e){} }
  function loadHubSpot(){
    if (document.getElementById('hs-script-loader')) return;
    var s=document.createElement('script'); s.id='hs-script-loader'; s.async=true; s.defer=true;
    s.src='https://js.hs-scripts.com/'+HUB+'.js'; document.body.appendChild(s);
  }
  function removeHubSpot(){
    // Stop HubSpot: remove its script, widget and cookies, then reload so nothing keeps running.
    var names=['__hstc','__hssc','__hssrc','hubspotutk','messagesUtk','__hs_opt_out','__hs_do_not_track','__hs_initial_opt_in'];
    var host=location.hostname.split('.'), doms=[location.hostname];
    for (var i=1;i<host.length-1;i++) doms.push('.'+host.slice(i).join('.'));
    names.forEach(function(n){ doms.forEach(function(d){ document.cookie=n+'=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain='+d; }); document.cookie=n+'=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'; });
  }
  function apply(){
    var v=get();
    if (v==='accepted') { bar.hidden=true; loadHubSpot(); }
    else if (v==='declined') { bar.hidden=true; }
    else { bar.hidden=false; }
  }
  document.getElementById('anuCookieYes').addEventListener('click',function(){ set('accepted'); apply(); });
  document.getElementById('anuCookieNo').addEventListener('click',function(){ var was=get(); set('declined'); removeHubSpot(); bar.hidden=true; if (was==='accepted') location.reload(); });
  window.anuCookieSettings=function(){ bar.hidden=false; return false; };
  // "Cookie settings" link next to the Data Protection link in the footer
  function addLink(){
    var a=document.querySelector('a[href*="data-protection"]:not(#anuCookieBar a)'); if(!a||document.getElementById('anuCookieLink')) return;
    var c=document.createElement('a'); c.id='anuCookieLink'; c.href='#'; c.textContent='Cookie settings'; c.onclick=window.anuCookieSettings;
    var sep=a.nextSibling && a.nextSibling.nodeType===3 && a.nextSibling.nodeValue.indexOf('·')>-1;
    a.parentNode.insertBefore(c,a.nextSibling); c.parentNode.insertBefore(document.createTextNode(' · '),c);
  }
  addLink(); apply();
})();
</script>
<?php
    }
}
