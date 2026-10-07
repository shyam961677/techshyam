    </div><!-- /.main-inner -->
</div><!-- /.main-wrap -->

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
// Auto-close flash after 4s
setTimeout(function(){
    const f = document.querySelector('.flash');
    if(f) { f.style.transition='opacity 0.5s'; f.style.opacity=0; setTimeout(()=>f.remove(),500); }
}, 4000);
// Confirm before delete
document.querySelectorAll('[data-confirm]').forEach(function(el){
    el.addEventListener('click', function(e){
        if(!confirm(this.dataset.confirm)) e.preventDefault();
    });
});
</script>
</body>
</html>
