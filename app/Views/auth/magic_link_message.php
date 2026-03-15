<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="login-brand">
  <?php
    $loginLogo = setting('App.loginLogo');
    $logoSrc   = $loginLogo ? base_url('uploads/' . $loginLogo) : base_url('assets/img/stisla-fill.svg');
  ?>
  <img src="<?= $logoSrc ?>" alt="logo" width="100" class="shadow-light rounded-circle">
</div>

<div class="card card-primary">
  <div class="card-header"><h4><?= lang('Auth.useMagicLink') ?></h4></div>

  <div class="card-body">
    <div class="text-center">
      <div class="mb-4">
        <i class="fas fa-envelope-open-text fa-3x text-primary"></i>
      </div>
      <h6 class="mb-3"><?= lang('Auth.checkYourEmail') ?></h6>
      <p class="text-muted"><?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?></p>
    </div>
  </div>
</div>
<div class="mt-5 text-muted text-center">
  <a href="<?= url_to('login') ?>"><?= lang('Auth.backToLogin') ?></a>
</div>
<?= $this->endSection() ?>
