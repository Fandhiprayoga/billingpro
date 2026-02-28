<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Order Saya</h4>
        <div class="card-header-action">
          <a href="<?= base_url('my-orders/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Order Baru
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Status</label>
            <select id="filter-status" class="form-control select2" multiple>
              <option value="pending" selected>Menunggu Pembayaran</option>
              <option value="awaiting_confirmation" selected>Menunggu Verifikasi</option>
              <option value="paid">Lunas</option>
              <option value="cancelled">Dibatalkan</option>
              <option value="expired">Kadaluarsa</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset Filter
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-my-orders" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>No. Order</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tanggal</th>
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
  $('.select2').select2({ width: '100%', placeholder: 'Pilih status...' });

  var statusBadges = {
    'pending': 'badge-warning',
    'awaiting_confirmation': 'badge-info',
    'paid': 'badge-success',
    'cancelled': 'badge-danger',
    'expired': 'badge-secondary'
  };
  var statusLabels = {
    'pending': 'Menunggu Pembayaran',
    'awaiting_confirmation': 'Menunggu Verifikasi',
    'paid': 'Lunas',
    'cancelled': 'Dibatalkan',
    'expired': 'Kadaluarsa'
  };

  function formatRupiah(val) {
    return 'Rp ' + parseInt(val).toLocaleString('id-ID');
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var dd = ('0' + d.getDate()).slice(-2);
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var hh = ('0' + d.getHours()).slice(-2);
    var mi = ('0' + d.getMinutes()).slice(-2);
    return dd + '/' + mm + '/' + d.getFullYear() + ' ' + hh + ':' + mi;
  }

  var table = $('#table-my-orders').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('my-orders/ajax') ?>',
      data: function(d) {
        var status = $('#filter-status').val();
        d.status = status ? status.join(',') : '';
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'order_number',
        render: function(data) { return '<code>' + $('<span>').text(data).html() + '</code>'; }
      },
      { data: 'plan_name',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'amount',
        render: function(data) { return formatRupiah(data); }
      },
      { data: 'status',
        render: function(data) {
          var cls = statusBadges[data] || 'badge-light';
          var lbl = statusLabels[data] || data;
          return '<span class="badge ' + cls + '">' + lbl + '</span>';
        }
      },
      { data: 'created_at',
        render: function(data) { return formatDate(data); }
      },
      { data: null, orderable: false,
        render: function(data, type, row) {
          var html = '<a href="<?= base_url('my-orders/view/') ?>' + row.order_number + '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a> ';
          if (row.status === 'pending') {
            html += '<a href="<?= base_url('my-orders/upload-confirmation/') ?>' + row.order_number + '" class="btn btn-sm btn-success" title="Upload Bukti"><i class="fas fa-upload"></i></a>';
          }
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
      emptyTable: 'Belum ada order. <a href="<?= base_url('plans') ?>">Pilih paket</a> untuk membuat order baru.',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-status').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val(null).trigger('change');
  });
});
</script>
