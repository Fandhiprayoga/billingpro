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
