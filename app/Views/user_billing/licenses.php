<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Lisensi Saya</h4>
        <div class="card-header-action">
          <a href="<?= base_url('plans') ?>" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i> Beli Lisensi Baru
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Status</label>
            <select id="filter-status" class="form-control select2">
              <option value="">Semua Status</option>
              <option value="active" selected>Aktif</option>
              <option value="expired">Expired</option>
              <option value="revoked">Dicabut</option>
              <option value="suspended">Ditangguhkan</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset Filter
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-my-licenses" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>License Key</th>
                <th>Paket</th>
                <th>No. Order</th>
                <th>Device</th>
                <th>Status</th>
                <th>Berlaku Sampai</th>
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
  $('.select2').select2({ width: '100%' });

  var statusBadges = {
    'active': 'badge-success',
    'expired': 'badge-secondary',
    'revoked': 'badge-danger',
    'suspended': 'badge-warning'
  };
  var statusLabels = {
    'active': 'Aktif',
    'expired': 'Expired',
    'revoked': 'Dicabut',
    'suspended': 'Ditangguhkan'
  };

  var table = $('#table-my-licenses').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('my-licenses/ajax') ?>',
      data: function(d) {
        d.status = $('#filter-status').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'license_key',
        render: function(data) { return '<code>' + $('<span>').text(data).html() + '</code>'; }
      },
      { data: 'plan_name',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'order_number',
        render: function(data) { return '<small>' + $('<span>').text(data || '-').html() + '</small>'; }
      },
      { data: 'device_id',
        render: function(data) {
          if (data) {
            return '<span class="badge badge-light" title="' + $('<span>').text(data).html() + '"><i class="fas fa-desktop"></i> Terkunci</span>';
          }
          return '<span class="text-muted">Belum aktif</span>';
        }
      },
      { data: null,
        render: function(data, type, row) {
          var now = new Date().getTime();
          var exp = new Date(row.expires_at).getTime();
          var isExpired = exp < now;
          var status = row.status;

          if (status === 'active' && isExpired) {
            status = 'expired';
          }

          var cls = statusBadges[status] || 'badge-light';
          var lbl = statusLabels[status] || status;
          return '<span class="badge ' + cls + '">' + lbl + '</span>';
        }
      },
      { data: 'expires_at',
        render: function(data, type, row) {
          if (!data) return '-';
          var d = new Date(data);
          var now = new Date().getTime();
          var exp = d.getTime();
          var dd = ('0' + d.getDate()).slice(-2);
          var mm = ('0' + (d.getMonth() + 1)).slice(-2);
          var dateStr = dd + '/' + mm + '/' + d.getFullYear();

          if (exp > now && row.status === 'active') {
            var days = Math.ceil((exp - now) / 86400000);
            dateStr += '<br><small class="text-muted">(' + days + ' hari lagi)</small>';
          }
          return dateStr;
        }
      },
      { data: null, orderable: false,
        render: function(data, type, row) {
          var now = new Date().getTime();
          var exp = new Date(row.expires_at).getTime();
          var status = row.status;
          if (status === 'active' && exp < now) status = 'expired';
          var isTrial = parseInt(row.is_trial) === 1;

          var html = '<div class="dropdown d-inline">';
          html += '<button class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown"><i class="fas fa-cog"></i></button>';
          html += '<div class="dropdown-menu dropdown-menu-right">';
          html += '<a class="dropdown-item" href="<?= base_url('my-licenses/view/') ?>' + row.uuid + '"><i class="fas fa-eye mr-2 text-info"></i>Detail</a>';
          if (!isTrial && (status === 'active' || status === 'expired')) {
            html += '<a class="dropdown-item" href="<?= base_url('my-licenses/renew/') ?>' + row.uuid + '"><i class="fas fa-sync-alt mr-2 text-success"></i>Perpanjang / Topup</a>';
          }
          if (!isTrial) {
            html += '<div class="dropdown-divider"></div>';
            html += '<a class="dropdown-item" href="<?= base_url('my-licenses/history/') ?>' + row.uuid + '"><i class="fas fa-history mr-2 text-secondary"></i>Riwayat Pembayaran</a>';
          }
          html += '</div></div>';
          return html;
        }
      }
    ],
    language: {
      processing: 'Memuat...',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(difilter dari _MAX_ total data)',
      zeroRecords: 'Tidak ada data yang cocok',
      emptyTable: 'Belum ada lisensi. <a href="<?= base_url('plans') ?>">Beli paket</a> untuk mendapatkan lisensi.',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-status').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val('').trigger('change');
  });
});
</script>
