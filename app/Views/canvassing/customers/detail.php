<?php
use CodeIgniter\Shield\Models\UserModel;
$identity = $customer->getEmailIdentity();
?>
<div class="row">
  <!-- Customer Profile -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h4>Profil Customer</h4></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td><strong>Username</strong></td><td><?= esc($customer->username) ?></td></tr>
          <tr><td><strong>Email</strong></td><td><?= esc($identity->secret ?? '-') ?></td></tr>
          <tr><td><strong>Nama Usaha</strong></td><td><?= esc($profile->nama_usaha ?? $profile['nama_usaha'] ?? '-') ?></td></tr>
          <tr><td><strong>No. Telp</strong></td><td><?= esc($profile->no_telp ?? $profile['no_telp'] ?? '-') ?></td></tr>
          <tr><td><strong>Propinsi</strong></td><td><?= esc($profile->propinsi ?? $profile['propinsi'] ?? '-') ?></td></tr>
          <tr><td><strong>Kabupaten</strong></td><td><?= esc($profile->kabupaten ?? $profile['kabupaten'] ?? '-') ?></td></tr>
        </table>
        <a href="<?= base_url('canvassing/customer-orders/create/' . $customer->id) ?>" class="btn btn-primary btn-block mt-3">
          <i class="fas fa-cart-plus"></i> Buatkan Order Baru
        </a>
        <?php if (activeGroupCan('canvassing.trials.create')): ?>
        <a href="<?= base_url('canvassing/customer-trials/create/' . $customer->id) ?>" class="btn btn-warning btn-block mt-2">
          <i class="fas fa-flask"></i> Buatkan Trial Lisensi
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Licenses -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-key text-warning"></i> Lisensi</h4></div>
      <div class="card-body">
        <?php if (empty($licenses)): ?>
          <p class="text-muted">Customer belum memiliki lisensi.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead>
                <tr><th>License Key</th><th>Paket</th><th>Status</th><th>Expired</th><th>Aksi</th></tr>
              </thead>
              <tbody>
                <?php foreach ($licenses as $lic): ?>
                <?php
                  $badge = match($lic->status) {
                    'active'    => 'badge-success',
                    'expired'   => 'badge-secondary',
                    'revoked'   => 'badge-danger',
                    'suspended' => 'badge-warning',
                    default     => 'badge-light',
                  };
                  $daysLeft = (strtotime($lic->expires_at) - time()) / 86400;
                ?>
                <tr>
                  <td><code><?= esc($lic->license_key) ?></code></td>
                  <td><?= esc($lic->plan_name ?? '-') ?></td>
                  <td><span class="badge <?= $badge ?>"><?= ucfirst($lic->status) ?></span></td>
                  <td>
                    <?= date('d/m/Y', strtotime($lic->expires_at)) ?>
                    <?php if ($lic->status === 'active' && $daysLeft <= 14): ?>
                      <small class="text-danger">(<?= max(0, (int) ceil($daysLeft)) ?> hari)</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('canvassing/customer-licenses/' . $lic->uuid) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    <?php if ($lic->status === 'active' && !$lic->is_trial): ?>
                    <a href="<?= base_url('canvassing/customer-licenses/renew/' . $lic->uuid) ?>" class="btn btn-sm btn-warning"><i class="fas fa-redo"></i></a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-shopping-cart text-info"></i> Order Terbaru</h4></div>
      <div class="card-body">
        <?php if (empty($recentOrders)): ?>
          <p class="text-muted">Belum ada order.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead>
                <tr><th>No. Order</th><th>Paket</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
              </thead>
              <tbody>
                <?php foreach ($recentOrders as $ord): ?>
                <?php
                  $sBadge = match($ord->status) {
                    'pending'                => 'badge-warning',
                    'awaiting_confirmation'  => 'badge-info',
                    'paid'                   => 'badge-success',
                    'cancelled'              => 'badge-danger',
                    'expired'                => 'badge-secondary',
                    default                  => 'badge-light',
                  };
                  $sLabel = match($ord->status) {
                    'pending'                => 'Pending',
                    'awaiting_confirmation'  => 'Menunggu Review',
                    'paid'                   => 'Lunas',
                    'cancelled'              => 'Dibatalkan',
                    'expired'                => 'Expired',
                    default                  => $ord->status,
                  };
                ?>
                <tr>
                  <td><code><?= esc($ord->order_number) ?></code></td>
                  <td><?= esc($ord->plan_name ?? '-') ?></td>
                  <td>Rp <?= number_format($ord->amount, 0, ',', '.') ?></td>
                  <td><span class="badge <?= $sBadge ?>"><?= $sLabel ?></span></td>
                  <td><?= date('d/m/Y', strtotime($ord->created_at)) ?></td>
                  <td>
                    <a href="<?= base_url('canvassing/customer-orders/view/' . $ord->order_number) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    <?php if ($ord->status === 'pending'): ?>
                    <a href="<?= base_url('canvassing/customer-orders/upload-proof/' . $ord->order_number) ?>" class="btn btn-sm btn-success"><i class="fas fa-upload"></i></a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
