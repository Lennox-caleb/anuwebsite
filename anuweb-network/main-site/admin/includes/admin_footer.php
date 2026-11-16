  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){var b=document.getElementById('adminMenuBtn'); if(!b) return;
  function set(o){document.body.classList.toggle('admin-menu-open',o); b.setAttribute('aria-expanded',o?'true':'false');}
  b.addEventListener('click',function(){set(!document.body.classList.contains('admin-menu-open'));});
  document.addEventListener('click',function(e){ if(document.body.classList.contains('admin-menu-open') && !e.target.closest('#adminSidebar') && !e.target.closest('#adminMenuBtn')) set(false); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') set(false); });
})();
</script>
</body>
</html>
