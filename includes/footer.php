<?php
?>
        <footer class="site-footer">
            <div class="links">
                <a href="/kullanim-kosullari.php">Kullanım Koşulları & Mesafeli Satış</a>
                <a href="/gizlilik-politikasi.php">Gizlilik ve Çerez Politikası</a>
                <a href="/kvkk.php">KVKK Aydınlatma Metni</a>
                <a href="/legal.php?p=kunye">Künye & İletişim</a>
            </div>
            <div class="copy">
                &copy; <?= date('Y') ?> <?= SITE_NAME ?>.com — Tüm hakları saklıdır.
            </div>
        </footer>
    </div><!-- .app-wrap -->

    <div id="map-tooltip" class="map-tooltip" style="display: none;"></div>

    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/map.js"></script>
    <?php if (isset($extraScript)) echo $extraScript; ?>
</body>
</html>
