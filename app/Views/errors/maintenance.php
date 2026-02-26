<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Maintenance — <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?></title>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">

  <style>
    .maintenance-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .maintenance-card {
      max-width: 550px;
      width: 100%;
      text-align: center;
    }
    .maintenance-icon {
      font-size: 5rem;
      color: #fc544b;
      margin-bottom: 1.5rem;
    }
  </style>
</head>

<body>
  <div class="maintenance-page">
    <div class="maintenance-card">
      <div class="card shadow-lg">
        <div class="card-body py-5 px-4">
          <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
          </div>

          <h2 class="mb-3 text-dark font-weight-bold">Sedang Dalam Pemeliharaan</h2>

          <p class="text-muted lead mb-4">
            <?= esc(setting('App.maintenanceMsg') ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.') ?>
          </p>

          <hr>

          <div class="text-muted small mb-3">
            <i class="fas fa-clock mr-1"></i> Kami akan segera kembali. Terima kasih atas kesabaran Anda.
          </div>

          <?php if (auth()->loggedIn()): ?>
            <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          <?php else: ?>
            <a href="<?= base_url('login') ?>" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-sign-in-alt"></i> Login sebagai Admin
            </a>
          <?php endif; ?>
        </div>

        <div class="card-footer text-muted small">
          <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?> — v<?= esc(setting('App.siteVersion') ?? '1.0.0') ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
