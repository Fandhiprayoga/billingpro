<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Tambah Paket Baru</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/plans/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="name">Nama Paket <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= old('name') ?>" placeholder="Contoh: Basic, Pro, Enterprise" required>
          </div>

          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3"
                      placeholder="Deskripsi singkat paket"><?= old('description') ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="price">Harga (Rp) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="price" name="price"
                       value="<?= old('price') ?>" min="0" step="1000" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="duration_days">Durasi (hari) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="duration_days" name="duration_days"
                       value="<?= old('duration_days') ?>" min="1" placeholder="Contoh: 30, 90, 365" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="features">Fitur <small class="text-muted">(satu fitur per baris)</small></label>
            <textarea class="form-control" id="features" name="features" rows="5"
                      placeholder="Contoh:&#10;Unlimited transaksi&#10;Support 24/7&#10;Multi-outlet"><?= old('features') ?></textarea>
          </div>

          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                     <?= old('is_active', '1') ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Aktif</label>
            </div>
          </div>

          <div class="form-group text-right">
            <a href="<?= base_url('admin/plans') ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
