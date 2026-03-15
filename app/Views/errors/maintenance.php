<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Maintenance &mdash; <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?></title>

  <!-- Favicon -->
  <?php
    $faviconSetting = setting('App.favicon');
    $faviconHref    = $faviconSetting ? base_url('uploads/' . $faviconSetting) : base_url('assets/img/stisla-fill.svg');
    $faviconType    = 'image/x-icon';
    if ($faviconSetting) {
        $ext = pathinfo($faviconSetting, PATHINFO_EXTENSION);
        $faviconType = match($ext) {
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            default => 'image/x-icon',
        };
    } else {
        $faviconType = 'image/svg+xml';
    }
  ?>
  <link rel="icon" type="<?= $faviconType ?>" href="<?= $faviconHref ?>">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- Template CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>

<body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">

            <div class="login-brand">
              <?php
                $loginLogo = setting('App.loginLogo');
                $logoSrc   = $loginLogo ? base_url('uploads/' . $loginLogo) : base_url('assets/img/stisla-fill.svg');
              ?>
              <img src="<?= $logoSrc ?>" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            <div class="card card-danger">
              <div class="card-header"><h4><i class="fas fa-tools"></i> Pemeliharaan Sistem</h4></div>
              <div class="card-body text-center">
                <div class="mb-4">
                  <i class="fas fa-tools text-danger" style="font-size: 4rem;"></i>
                </div>

                <h5 class="text-dark font-weight-bold mb-3">Sedang Dalam Pemeliharaan</h5>

                <p class="text-muted mb-4">
                  <?= esc(setting('App.maintenanceMsg') ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.') ?>
                </p>

                <div class="text-muted small mb-4">
                  <i class="fas fa-clock mr-1"></i> Kami akan segera kembali. Terima kasih atas kesabaran Anda.
                </div>

                <?php if (auth()->loggedIn()): ?>
                  <a href="<?= base_url('logout') ?>" class="btn btn-danger btn-lg btn-block">
                    <i class="fas fa-sign-out-alt"></i> Logout
                  </a>
                <?php else: ?>
                  <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login sebagai Admin
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <div class="simple-footer">
              Copyright &copy; <?= date('Y') ?> <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script src="<?= base_url('assets/js/stisla.js') ?>"></script>
  <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
  <script src="<?= base_url('assets/js/custom.js') ?>"></script>
</body>
</html>
