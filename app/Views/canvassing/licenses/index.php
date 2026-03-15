<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Lisensi Customer Saya</h4>
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
              <option value="suspended">Ditangguhkan</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-customer-licenses" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Customer</th>
                <th>License Key</th>
                <th>Paket</th>
                <th>Status</th>
                <th>Expired</th>
                <th width="130">Aksi</th>
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

  var statusBadges = { 'active': 'badge-success', 'expired': 'badge-secondary', 'revoked': 'badge-danger', 'suspended': 'badge-warning' };

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
  }

  var table = $('#table-customer-licenses').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('canvassing/customer-licenses/ajax') ?>',
      data: function(d) { d.status = $('#filter-status').val(); }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'username' },
      { data: 'license_key', render: function(d) { return '<code>' + d + '</code>'; } },
      { data: 'plan_name', render: function(d) { return d || '-'; } },
      { data: 'status', render: function(d) {
          return '<span class="badge ' + (statusBadges[d]||'badge-light') + '">' + d.charAt(0).toUpperCase() + d.slice(1) + '</span>';
        }
      },
      { data: 'expires_at', render: function(d, type, row) {
          var s = formatDate(d);
          if (row.status === 'active' && d) {
            var days = Math.ceil((new Date(d) - new Date()) / 86400000);
            if (days <= 14) s += ' <small class="text-danger">(' + Math.max(0,days) + ' hari)</small>';
          }
          return s;
        }
      },
      { data: 'uuid', orderable: false,
        render: function(d, type, row) {
          var html = '<a href="<?= base_url('canvassing/customer-licenses') ?>/' + d + '" class="btn btn-sm btn-info mr-1"><i class="fas fa-eye"></i></a>';
          if (row.status === 'active' && !parseInt(row.is_trial)) {
            html += '<a href="<?= base_url('canvassing/customer-licenses/renew') ?>/' + d + '" class="btn btn-sm btn-warning"><i class="fas fa-redo"></i></a>';
          }
          return html;
        }
      }
    ]
  });

  $('#filter-status').on('change', function() { table.ajax.reload(); });
  $('#btn-reset').on('click', function() { $('#filter-status').val('').trigger('change'); table.ajax.reload(); });
});
</script>
