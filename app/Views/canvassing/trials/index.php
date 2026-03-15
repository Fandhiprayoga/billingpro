<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Trial Lisensi Customer Saya</h4>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Status</label>
            <select id="filter-status" class="form-control select2">
              <option value="">Semua</option>
              <option value="active">Aktif</option>
              <option value="expired">Expired</option>
              <option value="revoked">Dicabut</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-customer-trials" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Customer</th>
                <th>License Key</th>
                <th>Durasi (hari)</th>
                <th>Status</th>
                <th>Expired</th>
                <th>Dibuat</th>
                <th width="100">Aksi</th>
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

  var statusBadges = { 'active': 'badge-success', 'expired': 'badge-secondary', 'revoked': 'badge-danger' };

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
  }

  var table = $('#table-customer-trials').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('canvassing/customer-trials/ajax') ?>',
      data: function(d) { d.status = $('#filter-status').val(); }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'username' },
      { data: 'license_key', render: function(d) { return '<code>' + d + '</code>'; } },
      { data: 'trial_duration_days', className: 'text-center' },
      { data: 'status', render: function(d) {
          return '<span class="badge ' + (statusBadges[d]||'badge-light') + '">' + d.charAt(0).toUpperCase() + d.slice(1) + '</span>';
        }
      },
      { data: 'expires_at', render: function(d, type, row) {
          var s = formatDate(d);
          if (row.status === 'active' && d) {
            var days = Math.ceil((new Date(d) - new Date()) / 86400000);
            if (days <= 7) s += ' <small class="text-danger">(' + Math.max(0,days) + ' hari)</small>';
          }
          return s;
        }
      },
      { data: 'created_at', render: function(d) { return formatDate(d); } },
      { data: 'uuid', orderable: false,
        render: function(d) {
          return '<a href="<?= base_url('canvassing/customer-trials/view') ?>/' + d + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
        }
      }
    ]
  });

  $('#filter-status').on('change', function() { table.ajax.reload(); });
  $('#btn-reset').on('click', function() { $('#filter-status').val('').trigger('change'); table.ajax.reload(); });
});
</script>
