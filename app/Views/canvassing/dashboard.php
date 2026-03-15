<h2 class="section-title">Dashboard Canvassing</h2>
<p class="section-lead">Ringkasan data customer yang Anda kelola.</p>

<!-- Expiring Licenses Warning -->
<?php if (!empty($expiringLicenses)): ?>
<div class="row">
  <div class="col-12">
    <div class="alert alert-warning alert-has-icon">
      <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="alert-body">
        <div class="alert-title">Lisensi Customer Akan Expired</div>
        <p class="mb-2">Ada <strong><?= count($expiringLicenses) ?></strong> lisensi customer yang akan expired dalam 14 hari:</p>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" style="background: rgba(255,255,255,0.7);">
            <thead>
              <tr><th>Customer</th><th>License Key</th><th>Paket</th><th>Expired</th><th>Sisa Hari</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($expiringLicenses as $lic): ?>
              <?php $daysLeft = max(0, (int) ceil((strtotime($lic->expires_at) - time()) / 86400)); ?>
              <tr>
                <td><?= esc($lic->username) ?></td>
                <td><code><?= esc($lic->license_key) ?></code></td>
                <td><?= esc($lic->plan_name ?? '-') ?></td>
                <td><?= date('d/m/Y', strtotime($lic->expires_at)) ?></td>
                <td><span class="<?= $daysLeft <= 3 ? 'text-danger font-weight-bold' : 'text-warning' ?>"><?= $daysLeft ?> hari</span></td>
                <td>
                  <a href="<?= base_url('canvassing/customer-licenses/renew/' . $lic->uuid) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-redo"></i> Perpanjang
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Customer</h4></div>
        <div class="card-body"><?= $totalCustomers ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Order Pending</h4></div>
        <div class="card-body"><?= $orderStats['pending'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Menunggu Review</h4></div>
        <div class="card-body"><?= $orderStats['awaiting_confirmation'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-key"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Lisensi Aktif</h4></div>
        <div class="card-body"><?= $activeLicenses ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Links -->
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h4>Aksi Cepat</h4></div>
      <div class="card-body">
        <a href="<?= base_url('canvassing/my-customers') ?>" class="btn btn-primary mr-2 mb-2"><i class="fas fa-users"></i> Lihat Customer</a>
        <a href="<?= base_url('canvassing/customer-orders') ?>" class="btn btn-info mr-2 mb-2"><i class="fas fa-shopping-cart"></i> Lihat Order</a>
        <a href="<?= base_url('canvassing/customer-licenses') ?>" class="btn btn-success mr-2 mb-2"><i class="fas fa-key"></i> Lihat Lisensi</a>
        <a href="<?= base_url('canvassing/activity-log') ?>" class="btn btn-secondary mb-2"><i class="fas fa-history"></i> Log Aktivitas</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h4>Aktivitas Terakhir</h4></div>
      <div class="card-body">
        <?php if (empty($recentActivity)): ?>
          <p class="text-muted">Belum ada aktivitas.</p>
        <?php else: ?>
          <ul class="list-unstyled">
            <?php foreach ($recentActivity as $act): ?>
            <li class="mb-2">
              <i class="fas fa-circle text-primary mr-1" style="font-size: 8px;"></i>
              <small class="text-muted"><?= date('d/m H:i', strtotime($act->created_at)) ?></small>
              — <?= esc($act->description ?? $act->action_type) ?>
              <span class="badge badge-light"><?= esc($act->customer_username) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
