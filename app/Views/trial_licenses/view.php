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
        <h4>Detail Lisensi Trial</h4>
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
            <td width="180"><strong>User</strong></td>
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
                    $days = (int)ceil(($exp - $now) / 86400);
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
            <td><?= nl2br(esc($license->trial_notes)) ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <?php if ($license->status === 'active' && activeGroupCan('trial-licenses.revoke')): ?>
    <div class="card">
      <div class="card-body">
        <form action="<?= base_url('admin/trial-licenses/revoke/' . $license->uuid) ?>" method="post"
              onsubmit="return confirm('Yakin ingin mencabut lisensi trial ini? Aksi ini tidak bisa dibatalkan.')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger btn-block">
            <i class="fas fa-ban"></i> Cabut Lisensi Trial
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('admin/trial-licenses') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function copyLicenseKey() {
  var key = document.getElementById('licenseKeyText').innerText;
  navigator.clipboard.writeText(key).then(function() {
    if (typeof iziToast !== 'undefined') {
      iziToast.success({ title: 'Berhasil', message: 'License key berhasil disalin!', position: 'topRight' });
    } else {
      alert('License key berhasil disalin!');
    }
  }).catch(function() {
    var el = document.createElement('textarea');
    el.value = key;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    alert('License key berhasil disalin: ' + key);
  });
}
</script>
