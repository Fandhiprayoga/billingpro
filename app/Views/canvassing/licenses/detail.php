<?php
$badge = match($license->status) {
  'active'    => 'badge-success',
  'expired'   => 'badge-secondary',
  'revoked'   => 'badge-danger',
  'suspended' => 'badge-warning',
  default     => 'badge-light',
};
?>
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h4>Detail Lisensi</h4></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td width="180"><strong>Customer</strong></td><td><?= esc($license->username) ?></td></tr>
          <tr><td><strong>License Key</strong></td><td><code class="h5"><?= esc($license->license_key) ?></code></td></tr>
          <tr><td><strong>UUID</strong></td><td><small class="text-muted"><?= esc($license->uuid) ?></small></td></tr>
          <tr><td><strong>Paket</strong></td><td><?= esc($license->plan_name ?? '-') ?> <?= !empty($license->duration_days) ? '(' . $license->duration_days . ' hari)' : '' ?></td></tr>
          <tr><td><strong>Order</strong></td><td><?= !empty($license->order_number) ? '<code>' . esc($license->order_number) . '</code>' : '-' ?></td></tr>
          <tr><td><strong>Status</strong></td><td><span class="badge <?= $badge ?>"><?= ucfirst($license->status) ?></span></td></tr>
          <tr><td><strong>Device ID</strong></td><td><?= esc($license->device_id ?? 'Belum diaktivasi') ?></td></tr>
          <tr><td><strong>Aktif Sejak</strong></td><td><?= $license->activated_at ? date('d/m/Y H:i', strtotime($license->activated_at)) : 'Belum diaktivasi' ?></td></tr>
          <tr><td><strong>Expired</strong></td><td><?= date('d/m/Y H:i', strtotime($license->expires_at)) ?></td></tr>
          <tr>
            <td><strong>Trial</strong></td>
            <td><?= $license->is_trial ? '<span class="badge badge-info">Ya (' . $license->trial_duration_days . ' hari)</span>' : 'Tidak' ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h4>Aksi</h4></div>
      <div class="card-body">
        <a href="<?= base_url('canvassing/customer-licenses/history/' . $license->uuid) ?>" class="btn btn-primary btn-block mb-2">
          <i class="fas fa-history"></i> History Transaksi
        </a>
        <?php if ($license->status === 'active' && !$license->is_trial): ?>
        <a href="<?= base_url('canvassing/customer-licenses/renew/' . $license->uuid) ?>" class="btn btn-warning btn-block mb-2">
          <i class="fas fa-redo"></i> Perpanjang Lisensi
        </a>
        <?php endif; ?>
        <a href="<?= base_url('canvassing/customer-licenses') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
