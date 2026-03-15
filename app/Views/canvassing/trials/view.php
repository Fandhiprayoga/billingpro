<?php
$licBadge = match($license->status) {
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
      <div class="card-header">
        <h4>Detail Trial Lisensi Customer</h4>
      </div>
      <div class="card-body">
        <div class="text-center mb-4">
          <span class="badge badge-warning badge-lg mr-2"><i class="fas fa-flask"></i> TRIAL</span>
          <br class="d-block mb-2">
          <h3 class="text-monospace d-inline-block mb-1" id="licenseKeyText"><?= esc($license->license_key) ?></h3>
          <button type="button" class="btn btn-outline-primary btn-sm ml-2" onclick="copyLicenseKey()" title="Salin License Key">
            <i class="fas fa-copy"></i> Salin
          </button>
          <br>
          <span class="badge <?= $licBadge ?> badge-lg"><?= ucfirst($license->status) ?></span>
        </div>

        <table class="table table-sm table-borderless">
          <tr>
            <td width="180"><strong>Customer</strong></td>
            <td><?= esc($license->username) ?> (<?= esc($license->email ?? '-') ?>)</td>
          </tr>
          <tr>
            <td><strong>Tipe</strong></td>
            <td><span class="badge badge-warning"><i class="fas fa-flask"></i> Trial</span></td>
          </tr>
          <tr>
            <td><strong>Durasi Trial</strong></td>
            <td><?= $license->trial_duration_days ?> hari</td>
          </tr>
          <tr>
            <td><strong>Device ID</strong></td>
            <td>
              <?php if (!empty($license->device_id)): ?>
                <code><?= esc($license->device_id) ?></code>
              <?php else: ?>
                <span class="text-muted">Belum diaktivasi di device</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td><strong>Dibuat Oleh</strong></td>
            <td><?= esc($license->created_by_name ?? '-') ?></td>
          </tr>
          <tr>
            <td><strong>Dibuat</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->created_at)) ?></td>
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
              <?php
                $now = time();
                $exp = strtotime($license->expires_at);
                if ($exp > $now) {
                    $days = (int) ceil(($exp - $now) / 86400);
                    echo "<small class='text-success'>({$days} hari lagi)</small>";
                } else {
                    echo "<small class='text-danger'>(Sudah expired)</small>";
                }
              ?>
            </td>
          </tr>
          <?php if (!empty($license->trial_notes)): ?>
          <tr>
            <td><strong>Catatan</strong></td>
            <td><?= nl2br(esc((string) $license->trial_notes)) ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('canvassing/customer-trials') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
        <a href="<?= base_url('canvassing/my-customers/' . $license->user_id) ?>" class="btn btn-info btn-block">
          <i class="fas fa-user"></i> Lihat Profil Customer
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function copyLicenseKey() {
  var key = document.getElementById('licenseKeyText').innerText;
  navigator.clipboard.writeText(key).then(function() {
    var btn = event.currentTarget;
    var origHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
    btn.classList.add('btn-primary');
    btn.classList.remove('btn-outline-primary');
    setTimeout(function() {
      btn.innerHTML = origHtml;
      btn.classList.remove('btn-primary');
      btn.classList.add('btn-outline-primary');
    }, 2000);
  });
}
</script>
