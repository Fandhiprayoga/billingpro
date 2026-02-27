<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Lisensi</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>License Key</th>
                <th>User</th>
                <th>Paket</th>
                <th>Order</th>
                <th>Device ID</th>
                <th>Berlaku Sampai</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($licenses)): ?>
                <?php $no = 1; foreach ($licenses as $lic): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><code><?= esc($lic->license_key) ?></code></td>
                  <td><?= esc($lic->username ?? '-') ?></td>
                  <td><?= esc($lic->plan_name ?? '-') ?></td>
                  <td><small><?= esc($lic->order_number ?? '-') ?></small></td>
                  <td>
                    <?php if (!empty($lic->device_id)): ?>
                      <small class="text-monospace"><?= esc(substr($lic->device_id, 0, 15)) ?><?= strlen($lic->device_id) > 15 ? '...' : '' ?></small>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y', strtotime($lic->expires_at)) ?></td>
                  <td>
                    <?php
                      $licBadge = match($lic->status) {
                        'active'    => 'badge-success',
                        'expired'   => 'badge-secondary',
                        'revoked'   => 'badge-danger',
                        'suspended' => 'badge-warning',
                        default     => 'badge-light',
                      };
                    ?>
                    <span class="badge <?= $licBadge ?>"><?= ucfirst($lic->status) ?></span>
                  </td>
                  <td>
                    <?php if (activeGroupCan('licenses.view')): ?>
                    <a href="<?= base_url('admin/licenses/view/' . $lic->id) ?>" class="btn btn-sm btn-info" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <?php endif; ?>

                    <?php if (activeGroupCan('licenses.revoke') && $lic->status === 'active'): ?>
                    <form action="<?= base_url('admin/licenses/revoke/' . $lic->id) ?>" method="post" class="d-inline"
                          onsubmit="return confirm('Yakin ingin mencabut lisensi ini?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Cabut Lisensi">
                        <i class="fas fa-ban"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center">Belum ada data lisensi.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
