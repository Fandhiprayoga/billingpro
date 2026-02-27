<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Lisensi Saya</h4>
        <div class="card-header-action">
          <a href="<?= base_url('plans') ?>" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i> Beli Lisensi Baru
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>License Key</th>
                <th>Paket</th>
                <th>No. Order</th>
                <th>Device</th>
                <th>Status</th>
                <th>Berlaku Sampai</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($licenses)): ?>
                <?php $no = 1; foreach ($licenses as $lic): ?>
                <?php
                  $isExpired = strtotime($lic->expires_at) < time();
                  $licBadge = match($lic->status) {
                    'active'    => $isExpired ? 'badge-secondary' : 'badge-success',
                    'expired'   => 'badge-secondary',
                    'revoked'   => 'badge-danger',
                    'suspended' => 'badge-warning',
                    default     => 'badge-light',
                  };
                  $licLabel = match($lic->status) {
                    'active'    => $isExpired ? 'Expired' : 'Aktif',
                    'expired'   => 'Expired',
                    'revoked'   => 'Dicabut',
                    'suspended' => 'Ditangguhkan',
                    default     => $lic->status,
                  };
                  $daysRemaining = $isExpired ? 0 : (int) ceil((strtotime($lic->expires_at) - time()) / 86400);
                ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><code><?= esc($lic->license_key) ?></code></td>
                  <td><?= esc($lic->plan_name ?? '-') ?></td>
                  <td><small><?= esc($lic->order_number ?? '-') ?></small></td>
                  <td>
                    <?php if ($lic->device_id): ?>
                      <span class="badge badge-light" title="<?= esc($lic->device_id) ?>">
                        <i class="fas fa-desktop"></i> Terkunci
                      </span>
                    <?php else: ?>
                      <span class="text-muted">Belum aktif</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge <?= $licBadge ?>"><?= $licLabel ?></span></td>
                  <td>
                    <?= date('d/m/Y', strtotime($lic->expires_at)) ?>
                    <?php if (!$isExpired && $lic->status === 'active'): ?>
                      <br><small class="text-muted">(<?= $daysRemaining ?> hari lagi)</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('my-licenses/view/' . $lic->id) ?>" class="btn btn-sm btn-info" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center">
                    Belum ada lisensi. <a href="<?= base_url('plans') ?>">Beli paket</a> untuk mendapatkan lisensi.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
