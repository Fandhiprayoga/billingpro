<footer class="main-footer">
  <div class="footer-left">
    Copyright &copy; <?= date('Y') ?> <div class="bullet"></div> <?= esc(setting('App.siteFooter') ?? 'CI4 Shield RBAC Boilerplate') ?>
  </div>
  <div class="footer-right">
    v<?= esc(setting('App.siteVersion') ?? '1.0.0') ?>
  </div>
</footer>
