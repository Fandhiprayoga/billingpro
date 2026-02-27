<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Paket Lisensi</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('plans.create')): ?>
          <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Paket
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($plans)): ?>
                <?php $no = 1; foreach ($plans as $plan): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td>
                    <strong><?= esc($plan->name) ?></strong>
                    <?php if (!empty($plan->description)): ?>
                      <br><small class="text-muted"><?= esc($plan->description) ?></small>
                    <?php endif; ?>
                  </td>
                  <td>Rp <?= number_format($plan->price, 0, ',', '.') ?></td>
                  <td><?= $plan->duration_days ?> hari</td>
                  <td>
                    <?php if ($plan->is_active): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (activeGroupCan('plans.edit')): ?>
                    <a href="<?= base_url('admin/plans/edit/' . $plan->id) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>

                    <?php if (activeGroupCan('plans.delete')): ?>
                    <form action="<?= base_url('admin/plans/delete/' . $plan->id) ?>" method="post" class="d-inline"
                          onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center">Belum ada data paket.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
