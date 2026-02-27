<?php
$isExpired = strtotime($license->expires_at) < time();
$daysRemaining = $isExpired ? 0 : (int) ceil((strtotime($license->expires_at) - time()) / 86400);
$licBadge = match($license->status) {
  'active'    => $isExpired ? 'badge-secondary' : 'badge-success',
  'expired'   => 'badge-secondary',
  'revoked'   => 'badge-danger',
  'suspended' => 'badge-warning',
  default     => 'badge-light',
};
$licLabel = match($license->status) {
  'active'    => $isExpired ? 'Expired' : 'Aktif',
  'expired'   => 'Expired',
  'revoked'   => 'Dicabut',
  'suspended' => 'Ditangguhkan',
  default     => $license->status,
};
?>
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h4>Detail Lisensi</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-<?= $license->status === 'active' && !$isExpired ? 'success' : 'secondary' ?> text-center">
          <small class="d-block mb-1">License Key</small>
          <code class="h4"><?= esc($license->license_key) ?></code>
        </div>

        <table class="table table-sm table-borderless">
          <tr>
            <td width="150"><strong>Paket</strong></td>
            <td><?= esc($license->plan_name) ?> (<?= $license->duration_days ?> hari)</td>
          </tr>
          <tr>
            <td><strong>No. Order</strong></td>
            <td><code><?= esc($license->order_number) ?></code></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td><span class="badge <?= $licBadge ?>"><?= $licLabel ?></span></td>
          </tr>
          <tr>
            <td><strong>Device ID</strong></td>
            <td>
              <?php if ($license->device_id): ?>
                <code><?= esc($license->device_id) ?></code>
              <?php else: ?>
                <span class="text-muted">Belum diaktivasi di perangkat manapun</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ($license->activated_at): ?>
          <tr>
            <td><strong>Diaktivasi</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->activated_at)) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td><strong>Berlaku Sampai</strong></td>
            <td>
              <?= date('d/m/Y H:i', strtotime($license->expires_at)) ?>
              <?php if (!$isExpired && $license->status === 'active'): ?>
                <span class="badge badge-light ml-1"><?= $daysRemaining ?> hari lagi</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td><strong>Dibuat</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->created_at)) ?></td>
          </tr>
        </table>
      </div>
    </div>

    <?php if ($license->status === 'active' && !$isExpired && empty($license->device_id)): ?>
    <div class="card">
      <div class="card-header">
        <h4>Cara Aktivasi</h4>
      </div>
      <div class="card-body">
        <p>Gunakan license key di atas pada aplikasi POS Anda. Aplikasi akan otomatis mengaktivasi lisensi dan mengunci ke perangkat Anda.</p>
        <p class="text-muted small mb-0">
          <strong>API Endpoint:</strong> <code>POST <?= base_url('api/license/activate') ?></code><br>
          <strong>Body:</strong> <code>{ "license_key": "<?= esc($license->license_key) ?>", "device_id": "YOUR_DEVICE_ID" }</code>
        </p>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <?php if ($license->status === 'active' && !$isExpired): ?>
    <div class="card bg-success text-white">
      <div class="card-body text-center">
        <i class="fas fa-check-circle fa-3x mb-3"></i>
        <h5>Lisensi Aktif</h5>
        <p class="mb-0"><?= $daysRemaining ?> hari tersisa</p>
      </div>
    </div>
    <?php elseif ($isExpired || $license->status === 'expired'): ?>
    <div class="card bg-secondary text-white">
      <div class="card-body text-center">
        <i class="fas fa-clock fa-3x mb-3"></i>
        <h5>Lisensi Expired</h5>
        <a href="<?= base_url('plans') ?>" class="btn btn-light mt-2">Perpanjang Lisensi</a>
      </div>
    </div>
    <?php elseif ($license->status === 'revoked'): ?>
    <div class="card bg-danger text-white">
      <div class="card-body text-center">
        <i class="fas fa-ban fa-3x mb-3"></i>
        <h5>Lisensi Dicabut</h5>
        <p class="small mb-0">Hubungi admin untuk informasi lebih lanjut.</p>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('my-licenses') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
