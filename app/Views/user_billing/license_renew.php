<?php
$isExpired     = strtotime($license->expires_at) < time();
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

<style>
  .plan-card { transition: all 0.2s; cursor: pointer; }
  .plan-card:hover { border-color: #6777ef !important; }
  .plan-radio:checked + .plan-card { border-color: #6777ef !important; box-shadow: 0 0 0 2px #6777ef; background: #f8f9ff; }
</style>

<div class="row">
  <!-- Sidebar -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-key"></i> Informasi Lisensi</h4>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <small class="d-block text-muted mb-1">License Key</small>
          <code class="h5"><?= esc($license->license_key) ?></code>
        </div>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td width="110"><strong>Paket</strong></td>
            <td><?= esc($license->plan_name ?? '-') ?></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td><span class="badge <?= $licBadge ?>"><?= $licLabel ?></span></td>
          </tr>
          <tr>
            <td><strong>Berlaku Sampai</strong></td>
            <td>
              <?= date('d/m/Y H:i', strtotime($license->expires_at)) ?>
              <?php if (!$isExpired && $license->status === 'active'): ?>
                <br><small class="text-success">(<?= $daysRemaining ?> hari lagi)</small>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <?php if (!$isExpired && $license->status === 'active'): ?>
    <div class="alert alert-success small">
      <i class="fas fa-info-circle mr-1"></i>
      Durasi paket akan <strong>ditambahkan</strong> ke sisa masa aktif Anda (<?= $daysRemaining ?> hari).
      <br>Total baru = <?= $daysRemaining ?> hari + durasi paket yang dipilih.
    </div>
    <?php else: ?>
    <div class="alert alert-info small">
      <i class="fas fa-info-circle mr-1"></i>
      Lisensi sudah <strong>expired</strong>. Durasi paket akan dihitung mulai hari ini.
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('my-licenses/view/' . $license->uuid) ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-sync-alt"></i> Pilih Paket Perpanjangan</h4>
      </div>
      <div class="card-body">
        <?php if (session()->has('error')): ?>
          <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('errors')): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach (session('errors') as $err): ?>
                <li><?= esc($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('my-licenses/store-renewal/' . $license->uuid) ?>" method="POST">
          <?= csrf_field() ?>

          <p class="text-muted mb-3">Pilih paket untuk menambah masa aktif lisensi Anda:</p>

          <div class="row">
            <?php foreach ($plans as $plan): ?>
            <div class="col-md-6 mb-3">
              <label class="d-block mb-0 h-100">
                <input type="radio" name="plan_id" value="<?= $plan->id ?>" class="d-none plan-radio" required>
                <div class="card plan-card border h-100 mb-0">
                  <div class="card-body text-center py-4">
                    <h5 class="mb-1"><?= esc($plan->name) ?></h5>
                    <h3 class="text-primary font-weight-bold mb-1">Rp <?= number_format($plan->price, 0, ',', '.') ?></h3>
                    <span class="badge badge-light"><i class="fas fa-calendar-alt mr-1"></i><?= $plan->duration_days ?> hari</span>
                    <?php if (!empty($plan->description)): ?>
                      <p class="text-muted small mt-2 mb-0"><?= esc($plan->description) ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <?php if (empty($plans)): ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
            <p>Tidak ada paket yang tersedia saat ini.</p>
          </div>
          <?php else: ?>

          <div class="form-group mt-2">
            <label>Catatan <small class="text-muted">(opsional)</small></label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan untuk admin..."><?= old('notes') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-submit">
            <i class="fas fa-shopping-cart mr-1"></i> Buat Order Perpanjangan
          </button>

          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>
