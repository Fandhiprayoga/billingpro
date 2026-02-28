<?php
$currentUser = auth()->user();
$currentUrl  = uri_string();

/**
 * Helper untuk cek apakah menu aktif
 */
function isMenuActive(string $path): string {
    $currentUrl = uri_string();
    return (strpos($currentUrl, $path) !== false) ? 'active' : '';
}

function isDropdownActive(array $paths): string {
    $currentUrl = uri_string();
    foreach ($paths as $path) {
        if (strpos($currentUrl, $path) !== false) {
            return 'active';
        }
    }
    return '';
}
?>
<div class="main-sidebar sidebar-style-1">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="<?= base_url('dashboard') ?>"><?= esc(setting('App.siteName') ?? 'CI4 RBAC') ?></a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="<?= base_url('dashboard') ?>"><?= esc(setting('App.siteNameShort') ?? 'C4') ?></a>
    </div>
    <ul class="sidebar-menu">

      <!-- Dashboard -->
      <li class="menu-header">Dashboard</li>
      <li class="<?= isMenuActive('dashboard') && !str_contains($currentUrl, 'admin') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fas fa-fire"></i> <span>Dashboard</span></a>
      </li>

      <!-- Admin Menu (hanya untuk active group yang punya akses admin) -->
      <?php if (activeGroupCan('admin.access')): ?>
      <li class="menu-header">Administrasi</li>

      <!-- User Management -->
      <?php if (activeGroupCan('users.list')): ?>
      <li class="<?= isMenuActive('admin/users') ?>">
        <a class="nav-link" href="<?= base_url('admin/users') ?>"><i class="fas fa-users"></i> <span>Manajemen User</span></a>
      </li>
      <?php endif; ?>

      <!-- Role Management (superadmin only) -->
      <?php if (activeGroupIs('superadmin')): ?>
      <li class="nav-item dropdown <?= isDropdownActive(['admin/roles']) ?>">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-shield"></i> <span>Role & Permission</span></a>
        <ul class="dropdown-menu">
          <li class="<?= isMenuActive('admin/roles') && !str_contains($currentUrl, 'permissions') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/roles') ?>">Daftar Role</a>
          </li>
          <li class="<?= isMenuActive('admin/roles/permissions') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/roles/permissions') ?>">Permission Matrix</a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Settings -->
      <?php if (activeGroupCan('admin.settings')): ?>
      <li class="<?= isMenuActive('admin/settings') ?>">
        <a class="nav-link" href="<?= base_url('admin/settings') ?>"><i class="fas fa-cog"></i> <span>Pengaturan</span></a>
      </li>
      <?php endif; ?>

      <!-- Licensing & Billing -->
      <?php if (activeGroupCan('plans.list') || activeGroupCan('orders.list') || activeGroupCan('licenses.list')): ?>
      <li class="menu-header">Licensing & Billing</li>

      <?php if (activeGroupCan('plans.list')): ?>
      <li class="<?= isMenuActive('admin/plans') ?>">
        <a class="nav-link" href="<?= base_url('admin/plans') ?>"><i class="fas fa-box"></i> <span>Paket Lisensi</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('orders.list')): ?>
      <li class="<?= isMenuActive('admin/orders') ?>">
        <a class="nav-link" href="<?= base_url('admin/orders') ?>"><i class="fas fa-shopping-cart"></i> <span>Order</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('licenses.list')): ?>
      <li class="<?= isMenuActive('admin/licenses') && !str_contains($currentUrl, 'trial') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/licenses') ?>"><i class="fas fa-key"></i> <span>Lisensi</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('trial-licenses.list')): ?>
      <li class="<?= isMenuActive('admin/trial-licenses') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/trial-licenses') ?>"><i class="fas fa-flask"></i> <span>Lisensi Trial</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('api-docs.view')): ?>
      <li class="<?= isMenuActive('admin/api-docs') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/api-docs') ?>"><i class="fas fa-book"></i> <span>Dokumentasi API</span></a>
      </li>
      <?php endif; ?>
      <?php endif; ?>
      <?php endif; ?>

      <!-- User Billing Menu (untuk user biasa yang BUKAN admin) -->
      <?php if (!activeGroupCan('admin.access')): ?>
        <?php if (activeGroupCan('plans.list') || activeGroupCan('orders.list') || activeGroupCan('licenses.list')): ?>
        <li class="menu-header">Layanan</li>

        <?php if (activeGroupCan('plans.list')): ?>
        <li class="<?= isMenuActive('plans') && !str_contains($currentUrl, 'admin') ? 'active' : '' ?>">
          <a class="nav-link" href="<?= base_url('plans') ?>"><i class="fas fa-box"></i> <span>Paket Lisensi</span></a>
        </li>
        <?php endif; ?>

        <?php if (activeGroupCan('orders.list')): ?>
        <li class="<?= isMenuActive('my-orders') ? 'active' : '' ?>">
          <a class="nav-link" href="<?= base_url('my-orders') ?>"><i class="fas fa-shopping-cart"></i> <span>Order Saya</span></a>
        </li>
        <?php endif; ?>

        <?php if (activeGroupCan('licenses.list')): ?>
        <li class="<?= isMenuActive('my-licenses') ? 'active' : '' ?>">
          <a class="nav-link" href="<?= base_url('my-licenses') ?>"><i class="fas fa-key"></i> <span>Lisensi Saya</span></a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Profil -->
      <li class="menu-header">Akun</li>
      <li class="<?= isMenuActive('profile') ?>">
        <a class="nav-link" href="<?= base_url('profile') ?>"><i class="far fa-user"></i> <span>Profil Saya</span></a>
      </li>
      <li>
        <a class="nav-link text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
      </li>

    </ul>
  </aside>
</div>
