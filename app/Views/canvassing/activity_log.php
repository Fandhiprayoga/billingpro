<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><h4>Log Aktivitas Canvassing</h4></div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Dari Tanggal</label>
            <input type="datetime-local" id="filter-date-from" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="small font-weight-bold">Sampai Tanggal</label>
            <input type="datetime-local" id="filter-date-to" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="small font-weight-bold">Customer</label>
            <select id="filter-customer" class="form-control select2">
              <option value="">Semua Customer</option>
              <?php foreach ($customers as $cust): ?>
              <option value="<?= $cust->customer_id ?>"><?= esc($cust->username) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="small font-weight-bold">Aksi</label>
            <select id="filter-action" class="form-control select2">
              <option value="">Semua Aksi</option>
              <option value="create_order">Buat Order</option>
              <option value="upload_payment">Upload Bukti</option>
              <option value="manage_license">Kelola Lisensi</option>
              <option value="view_profile">Lihat Profil</option>
              <option value="assign_customer">Assign</option>
              <option value="unassign_customer">Unassign</option>
              <option value="create_trial">Buat Trial</option>
              <option value="approve_order">Setujui Order</option>
              <option value="reject_order">Tolak Order</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-12">
            <button id="btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-undo"></i> Reset</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-activity-log" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Waktu</th>
                <th>Customer</th>
                <th>Aksi</th>
                <th>Keterangan</th>
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
  $('.select2').select2({ width: '100%' });

  var actionLabels = {
    'create_order': '<span class="badge badge-primary"><i class="fas fa-cart-plus"></i> Buat Order</span>',
    'upload_payment': '<span class="badge badge-success"><i class="fas fa-upload"></i> Upload Bukti</span>',
    'manage_license': '<span class="badge badge-warning"><i class="fas fa-key"></i> Kelola Lisensi</span>',
    'view_profile': '<span class="badge badge-info"><i class="fas fa-eye"></i> Lihat Profil</span>',
    'assign_customer': '<span class="badge badge-secondary"><i class="fas fa-user-plus"></i> Assign</span>',
    'unassign_customer': '<span class="badge badge-danger"><i class="fas fa-user-minus"></i> Unassign</span>',
    'create_trial': '<span class="badge badge-warning"><i class="fas fa-flask"></i> Buat Trial</span>',
    'approve_order': '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Setujui Order</span>',
    'reject_order': '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Tolak Order</span>'
  };

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear()+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);
  }

  var table = $('#table-activity-log').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('canvassing/activity-log/ajax') ?>',
      data: function(d) {
        d.date_from    = $('#filter-date-from').val();
        d.date_to      = $('#filter-date-to').val();
        d.customer_id  = $('#filter-customer').val();
        d.action_type  = $('#filter-action').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'created_at', render: function(d) { return formatDate(d); } },
      { data: 'customer_username' },
      { data: 'action_type', render: function(d) { return actionLabels[d] || d; } },
      { data: 'description', render: function(d) { return d || '-'; } }
    ]
  });

  $('#btn-filter').on('click', function() { table.ajax.reload(); });
  $('#btn-reset').on('click', function() {
    $('#filter-date-from').val('');
    $('#filter-date-to').val('');
    $('#filter-customer').val('').trigger('change');
    $('#filter-action').val('').trigger('change');
    table.ajax.reload();
  });
});
</script>
