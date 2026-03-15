<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Edit User: <?= esc($user_edit->username) ?></h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/users/update/' . $user_edit->id) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="username">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= old('username', $user_edit->username) ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= old('email', $user_edit->email) ?>" required>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password">
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter.</small>
          </div>

          <?php if (activeGroupCan('users.manage-roles')): ?>
          <div class="form-group">
            <label>Role <small class="text-muted">(bisa pilih lebih dari satu)</small></label>
            <?php foreach ($groups as $key => $group): ?>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="group-<?= $key ?>"
                       name="groups[]" value="<?= $key ?>"
                       <?= in_array($key, $userGroups) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="group-<?= $key ?>">
                  <strong><?= esc($group['title']) ?></strong> — <?= esc($group['description']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <hr>
          <h6 class="text-muted mb-3"><i class="fas fa-store"></i> Data Usaha / Pelanggan <small>(opsional)</small></h6>

          <div class="form-group">
            <label for="nama_usaha">Nama Usaha</label>
            <input type="text" class="form-control" id="nama_usaha" name="nama_usaha"
                   value="<?= old('nama_usaha', $profile->nama_usaha ?? $profile['nama_usaha'] ?? '') ?>"
                   placeholder="Nama toko / usaha">
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

          <div class="form-group text-right">
            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Perbarui
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
