<?php $currentUser = auth()->user(); ?>

<div class="row">
  <div class="col-12 col-md-4">
    <div class="card card-primary">
      <div class="card-header">
        <h4>Info Profil</h4>
      </div>
      <div class="card-body text-center">
        <img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mb-3" width="100">
        <h5><?= esc($currentUser->username) ?></h5>
        <p class="text-muted"><?= esc($currentUser->email) ?></p>
        <?php foreach ($userGroups as $group): ?>
          <?php
            $badgeClass = match($group) {
              'superadmin' => 'badge-danger',
              'admin'      => 'badge-warning',
              'manager'    => 'badge-info',
              default      => 'badge-primary',
            };
          ?>
          <span class="badge <?= $badgeClass ?>"><?= ucfirst($group) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($profile)): ?>
    <div class="card card-info">
      <div class="card-header">
        <h4>Info Usaha</h4>
      </div>
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <?php if (!empty($profile->nama_usaha ?? $profile['nama_usaha'] ?? '')): ?>
          <li class="mb-2"><i class="fas fa-store mr-2 text-primary"></i> <?= esc($profile->nama_usaha ?? $profile['nama_usaha']) ?></li>
          <?php endif; ?>
          <?php if (!empty($profile->no_telp ?? $profile['no_telp'] ?? '')): ?>
          <li class="mb-2"><i class="fas fa-phone mr-2 text-primary"></i> <?= esc($profile->no_telp ?? $profile['no_telp']) ?></li>
          <?php endif; ?>
          <?php if (!empty($profile->propinsi ?? $profile['propinsi'] ?? '')): ?>
          <li class="mb-2"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> <?= esc($profile->kabupaten ?? $profile['kabupaten'] ?? '') ?><?= !empty($profile->kabupaten ?? $profile['kabupaten'] ?? '') && !empty($profile->propinsi ?? $profile['propinsi'] ?? '') ? ', ' : '' ?><?= esc($profile->propinsi ?? $profile['propinsi']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-12 col-md-8">
    <div class="card">
      <div class="card-header">
        <h4>Edit Profil</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('profile/update') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= old('username', $currentUser->username) ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" value="<?= esc($currentUser->email) ?>" disabled>
            <small class="form-text text-muted">Email tidak dapat diubah.</small>
          </div>

          <hr>
          <h6 class="text-muted mb-3"><i class="fas fa-store"></i> Data Usaha / Pelanggan</h6>

          <div class="form-group">
            <label for="nama_usaha">Nama Usaha</label>
            <input type="text" class="form-control" id="nama_usaha" name="nama_usaha"
                   value="<?= old('nama_usaha', $profile->nama_usaha ?? $profile['nama_usaha'] ?? '') ?>"
                   placeholder="Nama toko / usaha Anda">
          </div>

          <div class="form-group">
            <label for="no_telp">No. HP / Telp</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp"
                   value="<?= old('no_telp', $profile->no_telp ?? $profile['no_telp'] ?? '') ?>"
                   placeholder="08xxxxxxxxxx">
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="propinsi">Propinsi</label>
                <input type="text" class="form-control" id="propinsi" name="propinsi"
                       value="<?= old('propinsi', $profile->propinsi ?? $profile['propinsi'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="kabupaten">Kabupaten / Kota</label>
                <input type="text" class="form-control" id="kabupaten" name="kabupaten"
                       value="<?= old('kabupaten', $profile->kabupaten ?? $profile['kabupaten'] ?? '') ?>">
              </div>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password">
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
          </div>

          <div class="form-group text-right">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
