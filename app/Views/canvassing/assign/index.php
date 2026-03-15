<div class="row">
  <!-- Form Assign -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h4>Assign Customer</h4></div>
      <div class="card-body">
        <form action="<?= base_url('admin/canvassing-assign/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="manager_id">Manager <span class="text-danger">*</span></label>
            <select class="form-control select2" id="manager_id" name="manager_id" required>
              <option value="">Pilih Manager...</option>
              <?php foreach ($managers as $m): ?>
              <option value="<?= $m->id ?>"><?= esc($m->username) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="customer_id">Customer (Belum di-assign) <span class="text-danger">*</span></label>
            <select class="form-control select2" id="customer_id" name="customer_id" required>
              <option value="">Pilih Customer...</option>
              <?php foreach ($unassignedUsers as $u): ?>
              <option value="<?= $u->id ?>"><?= esc($u->username) ?> (<?= esc($u->email ?? '') ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-link"></i> Assign
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- List -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h4>Daftar Assignment</h4></div>
      <div class="card-body">
        <!-- Filter -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label class="small font-weight-bold">Filter Manager</label>
            <select id="filter-manager" class="form-control select2">
              <option value="">Semua Manager</option>
              <?php foreach ($managers as $m): ?>
              <option value="<?= $m->id ?>"><?= esc($m->username) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-assignments" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Manager</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Tanggal Assign</th>
                <th>Status</th>
                <th width="80">Aksi</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';

  $('.select2').select2({ width: '100%' });

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
  }

  var table = $('#table-assignments').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/canvassing-assign/ajax') ?>',
      data: function(d) {
        d.manager_id = $('#filter-manager').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'manager_username' },
      { data: 'customer_username' },
      { data: 'customer_email', render: function(d) { return d || '-'; } },
      { data: 'assigned_at', render: function(d) { return formatDate(d); } },
      { data: 'status', render: function(d) {
          return d === 'active'
            ? '<span class="badge badge-success">Aktif</span>'
            : '<span class="badge badge-secondary">Nonaktif</span>';
        }
      },
      { data: 'id', orderable: false,
        render: function(d, type, row) {
          if (row.status !== 'active') return '';
          return '<form method="post" action="<?= base_url('admin/canvassing-assign/remove') ?>/' + d + '" style="display:inline" onsubmit="return confirm(\'Yakin unassign customer ini?\')">' +
                 '<input type="hidden" name="' + csrfName + '" value="' + csrfHash + '">' +
                 '<button type="submit" class="btn btn-sm btn-danger" title="Unassign"><i class="fas fa-unlink"></i></button></form>';
        }
      }
    ]
  });

  $('#filter-manager').on('change', function() { table.ajax.reload(); });
});
</script>
