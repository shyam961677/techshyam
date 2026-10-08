    </div><!-- /.main-inner -->
</div><!-- /.main-wrap -->

<script>
(function() {
    const root = document.documentElement;
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    function applyTheme(theme) {
        const light = theme === 'light';
        root.dataset.theme = light ? 'light' : 'dark';
        toggles.forEach(button => {
            button.setAttribute('aria-pressed', String(light));
            button.setAttribute('aria-label', light ? 'Switch to dark mode' : 'Switch to bright mode');
            button.title = light ? 'Switch to dark mode' : 'Switch to bright mode';
            button.innerHTML = `<i class="bi ${light ? 'bi-moon-stars-fill' : 'bi-sun-fill'}" aria-hidden="true"></i><span>${light ? 'Dark mode' : 'Bright mode'}</span>`;
        });
    }
    let savedTheme = 'dark';
    try { savedTheme = localStorage.getItem('techshyam-theme') || 'dark'; } catch (error) {}
    applyTheme(savedTheme);
    toggles.forEach(button => button.addEventListener('click', () => {
        const nextTheme = root.dataset.theme === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme);
        try { localStorage.setItem('techshyam-theme', nextTheme); } catch (error) {}
    }));
})();
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
