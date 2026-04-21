        </main>
    </div>
</div>

<!-- Scripts comuns -->
<script>
    // Menu mobile
    const btn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleMenu() {
        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    if (btn) {
        btn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
    }
</script>

<?php if (isset($pageScript)): ?>
    <script src="<?php echo htmlspecialchars($pageScript); ?>"></script>
<?php endif; ?>

<?php if (isset($inlineScript)): ?>
    <script><?php echo $inlineScript; ?></script>
<?php endif; ?>

</body>
</html>
