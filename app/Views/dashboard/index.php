<?php
$currentUser = auth()->user();
$groups = $currentUser->getGroups();
$groupLabel = activeGroupTitle();
$isAdmin = activeGroupCan('admin.access');
?>

<h2 class="section-title">Selamat Datang, <?= esc($currentUser->username) ?>!</h2>
<p class="section-lead">Anda login sebagai <strong><?= $groupLabel ?></strong>.
<?php if (count($groups) > 1): ?>
  <small class="text-muted">(Memiliki <?= count($groups) ?> role. Gunakan switcher di navbar untuk beralih.)</small>
<?php endif; ?>
</p>

<!-- ============================================ -->
<!-- RENEWAL REMINDER (tampil di atas, mencolok) -->
<!-- ============================================ -->
<?php if (!empty($expiringLicenses)): ?>
<div class="row">
  <div class="col-12">
    <div class="alert alert-warning alert-has-icon">
      <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="alert-body">
        <div class="alert-title">Reminder Perpanjangan Lisensi</div>
        <?php if ($isAdmin): ?>
          <p class="mb-2">Ada <strong><?= count($expiringLicenses) ?></strong> lisensi yang akan expired dalam 7 hari ke depan:</p>
        <?php else: ?>
          <p class="mb-2">Lisensi berikut akan segera berakhir. Segera perpanjang agar layanan tidak terganggu:</p>
        <?php endif; ?>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" style="background: rgba(255,255,255,0.7);">
            <thead>
              <tr>
                <th>License Key</th>
                <th>Paket</th>
                <?php if ($isAdmin): ?><th>User</th><?php endif; ?>
                <th>Expired</th>
                <th>Sisa Hari</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($expiringLicenses as $lic): ?>
              <?php
                $daysLeft = max(0, (int) ceil((strtotime($lic->expires_at) - time()) / 86400));
                $urgency  = $daysLeft <= 3 ? 'text-danger font-weight-bold' : 'text-warning';
              ?>
              <tr>
                <td><code><?= esc($lic->license_key) ?></code></td>
                <td><?= esc($lic->plan_name ?? '-') ?></td>
                <?php if ($isAdmin): ?><td><?= esc($lic->username ?? '-') ?></td><?php endif; ?>
                <td><?= date('d/m/Y', strtotime($lic->expires_at)) ?></td>
                <td><span class="<?= $urgency ?>"><?= $daysLeft ?> hari</span></td>
                <td>
                  <?php if ($isAdmin): ?>
                    <a href="<?= base_url('admin/licenses/view/' . $lic->id) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                  <?php else: ?>
                    <a href="<?= base_url('my-orders/create?plan=' . $lic->plan_id) ?>" class="btn btn-sm btn-warning"><i class="fas fa-redo"></i> Perpanjang</a>
                  <?php endif; ?>
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

<!-- ============================================ -->
<!-- STATISTIK CARDS -->
<!-- ============================================ -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning">
        <i class="fas fa-clock"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Order Pending</h4>
        </div>
        <div class="card-body">
          <?= $orderStats['pending'] ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info">
        <i class="fas fa-hourglass-half"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Menunggu Review</h4>
        </div>
        <div class="card-body">
          <?= $orderStats['awaiting_confirmation'] ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Order Lunas</h4>
        </div>
        <div class="card-body">
          <?= $orderStats['paid'] ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary">
        <i class="fas fa-key"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Lisensi Aktif</h4>
        </div>
        <div class="card-body">
          <?= $activeLicenses ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- ============================================ -->
<!-- ADMIN: Stat tambahan + Order Menunggu -->
<!-- ============================================ -->
<div class="row">
  <!-- Statistik ringkas admin -->
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary">
        <i class="far fa-user"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Users</h4>
        </div>
        <div class="card-body">
          <?php
            $userModel = new \CodeIgniter\Shield\Models\UserModel();
            echo $userModel->countAllResults();
          ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Order</h4>
        </div>
        <div class="card-body">
          <?= $orderStats['total'] ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-secondary">
        <i class="fas fa-times-circle"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Dibatalkan</h4>
        </div>
        <div class="card-body">
          <?= $orderStats['cancelled'] ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Roles</h4>
        </div>
        <div class="card-body">
          <?= count(config('AuthGroups')->groups) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Order Menunggu Review (Admin) -->
<?php if (!empty($pendingOrders)): ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-bell text-warning"></i> Order Menunggu Tindakan</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/orders') ?>" class="btn btn-primary btn-sm">Lihat Semua <i class="fas fa-chevron-right"></i></a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>No. Order</th>
                <th>User</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingOrders as $order): ?>
              <?php
                $statusBadge = match($order->status) {
                  'pending'               => 'badge-warning',
                  'awaiting_confirmation' => 'badge-info',
                  default                 => 'badge-light',
                };
                $statusLabel = match($order->status) {
                  'pending'               => 'Pending',
                  'awaiting_confirmation' => 'Menunggu Review',
                  default                 => $order->status,
                };
              ?>
              <tr>
                <td><code><?= esc($order->order_number) ?></code></td>
                <td><?= esc($order->username ?? '-') ?></td>
                <td><?= esc($order->plan_name ?? '-') ?></td>
                <td>Rp <?= number_format($order->amount, 0, ',', '.') ?></td>
                <td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
                <td><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></td>
                <td>
                  <a href="<?= base_url('admin/orders/view/' . $order->order_number) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Review</a>
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
<?php endif; ?>

<!-- ============================================ -->
<!-- INFORMASI REKENING TUJUAN TRANSFER -->
<!-- ============================================ -->
<?php if (!empty($bankInfo['bank_name'])): ?>
<div class="row">
  <div class="col-12 col-md-6">
    <div class="card card-primary">
      <div class="card-header">
        <h4><i class="fas fa-university"></i> Rekening Tujuan Transfer</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-light border mb-0" style="background: #f8f9ff;">
          <div class="row align-items-center">
            <div class="col-md-8">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <td width="110"><strong>Bank</strong></td>
                  <td><span class="font-weight-bold text-primary" style="font-size: 1.1em;"><?= esc($bankInfo['bank_name']) ?></span></td>
                </tr>
                <tr>
                  <td><strong>No. Rekening</strong></td>
                  <td>
                    <code class="h5 mb-0" id="dashboardBankAccount"><?= esc($bankInfo['account_number']) ?></code>
                    <button type="button" class="btn btn-sm btn-outline-primary ml-1" onclick="copyBankAccount()" title="Salin No. Rekening">
                      <i class="fas fa-copy"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td><strong>Atas Nama</strong></td>
                  <td><strong><?= esc($bankInfo['account_name']) ?></strong></td>
                </tr>
              </table>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
              <i class="fas fa-money-check-alt fa-3x text-primary" style="opacity: 0.3;"></i>
            </div>
          </div>
        </div>
        <?php if (!$isAdmin && $orderStats['pending'] > 0): ?>
        <div class="alert alert-warning mt-3 mb-0">
          <i class="fas fa-exclamation-circle"></i>
          Anda memiliki <strong><?= $orderStats['pending'] ?></strong> order menunggu pembayaran.
          <a href="<?= base_url('my-orders') ?>" class="alert-link">Lihat order →</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============================================ -->
<!-- INFO AKUN + AKSES CEPAT -->
<!-- ============================================ -->
<div class="row">
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">
        <h4>Informasi Akun</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <tr>
              <th>Username</th>
              <td><?= esc($currentUser->username) ?></td>
            </tr>
            <tr>
              <th>Email</th>
              <td><?= esc($currentUser->email) ?></td>
            </tr>
            <tr>
              <th>Role</th>
              <td>
                <?php foreach ($groups as $group): ?>
                  <?php if ($group === activeGroup()): ?>
                    <span class="badge badge-success" title="Role aktif"><?= ucfirst($group) ?> ✓</span>
                  <?php else: ?>
                    <span class="badge badge-secondary"><?= ucfirst($group) ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
            </tr>
            <tr>
              <th>Status</th>
              <td><span class="badge badge-success">Aktif</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">
        <h4>Akses Cepat</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <?php if (activeGroupCan('users.list')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/users') ?>" class="btn btn-primary btn-block">
              <i class="fas fa-users"></i><br>Manajemen User
            </a>
          </div>
          <?php endif; ?>

          <?php if (activeGroupIs('superadmin')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/roles') ?>" class="btn btn-danger btn-block">
              <i class="fas fa-user-shield"></i><br>Role & Permission
            </a>
          </div>
          <?php endif; ?>

          <div class="col-6 mb-3">
            <a href="<?= base_url('profile') ?>" class="btn btn-info btn-block">
              <i class="far fa-user"></i><br>Profil Saya
            </a>
          </div>

          <?php if (activeGroupCan('admin.settings')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/settings') ?>" class="btn btn-warning btn-block">
              <i class="fas fa-cog"></i><br>Pengaturan
            </a>
          </div>
          <?php endif; ?>

          <?php if (!$isAdmin): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('my-orders') ?>" class="btn btn-success btn-block">
              <i class="fas fa-shopping-cart"></i><br>Order Saya
            </a>
          </div>
          <div class="col-6 mb-3">
            <a href="<?= base_url('my-licenses') ?>" class="btn btn-dark btn-block">
              <i class="fas fa-key"></i><br>Lisensi Saya
            </a>
          </div>
          <?php endif; ?>

          <?php if ($isAdmin): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/orders') ?>" class="btn btn-success btn-block">
              <i class="fas fa-file-invoice-dollar"></i><br>Kelola Order
            </a>
          </div>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/licenses') ?>" class="btn btn-dark btn-block">
              <i class="fas fa-key"></i><br>Kelola Lisensi
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- PILIH PAKET (User biasa) -->
<!-- ============================================ -->
<?php if (!$isAdmin && !empty($plans)): ?>
<div class="row">
  <div class="col-12">
    <div class="section-title mt-0">Pilih Paket Lisensi</div>
    <p class="section-lead">Pilih paket yang sesuai dengan kebutuhan bisnis Anda.</p>
  </div>
</div>
<div class="row">
  <?php foreach ($plans as $plan): ?>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card card-primary">
      <div class="card-header">
        <h4><?= esc($plan->name) ?></h4>
      </div>
      <div class="card-body text-center">
        <h3 class="text-primary mb-1">Rp <?= number_format($plan->price, 0, ',', '.') ?></h3>
        <p class="text-muted"><?= $plan->duration_days ?> hari</p>

        <?php if (!empty($plan->description)): ?>
          <p class="text-muted small"><?= esc($plan->description) ?></p>
        <?php endif; ?>

        <?php
          $features = json_decode($plan->features ?? '[]', true);
          if (!empty($features)):
        ?>
        <ul class="text-left small mt-3 mb-4" style="list-style: none; padding-left: 0;">
          <?php foreach ($features as $feature): ?>
            <li class="mb-1"><i class="fas fa-check text-success mr-1"></i> <?= esc($feature) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <a href="<?= base_url('my-orders/create?plan=' . $plan->id) ?>" class="btn btn-primary btn-block">
          <i class="fas fa-shopping-cart"></i> Pilih Paket
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function copyBankAccount() {
  var text = document.getElementById('dashboardBankAccount').innerText;
  navigator.clipboard.writeText(text).then(function() {
    iziToast.success({ title: 'Berhasil', message: 'No. rekening berhasil disalin!', position: 'topRight' });
  }).catch(function() {
    var el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    alert('No. rekening berhasil disalin: ' + text);
  });
}
</script>
