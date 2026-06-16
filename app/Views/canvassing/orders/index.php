<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Order Customer Saya</h4>
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
          <table class="table table-striped" id="table-customer-orders" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>No. Order</th>
                <th>Customer</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Kode Unik</th>
                <th>Status</th>
                <th>Tanggal</th>
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
  $('.select2').select2({ width: '100%', placeholder: 'Pilih status...' });

  var statusBadges = {
    'pending': 'badge-warning', 'awaiting_confirmation': 'badge-info',
    'paid': 'badge-success', 'cancelled': 'badge-danger', 'expired': 'badge-secondary'
  };
  var statusLabels = {
    'pending': 'Pending', 'awaiting_confirmation': 'Menunggu Review',
    'paid': 'Lunas', 'cancelled': 'Dibatalkan', 'expired': 'Expired'
  };

  function formatRupiah(val) { return 'Rp ' + parseInt(val).toLocaleString('id-ID'); }
  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear()+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);
  }

  var table = $('#table-customer-orders').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('canvassing/customer-orders/ajax') ?>',
      data: function(d) {
        var status = $('#filter-status').val();
        d.status = status ? status.join(',') : '';
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'order_number', render: function(d) { return '<code>' + d + '</code>'; } },
      { data: 'username' },
      { data: 'plan_name', render: function(d) { return d || '-'; } },
      { data: 'amount', render: function(d) { return formatRupiah(d); } },
      { data: 'unique_code', render: function(d) { return '<span class="text-info font-weight-bold">Rp ' + parseInt(d || 0).toLocaleString('id-ID') + '</span>'; } },
      { data: 'status', render: function(d) {
          return '<span class="badge ' + (statusBadges[d]||'badge-light') + '">' + (statusLabels[d]||d) + '</span>';
        }
      },
      { data: 'created_at', render: function(d) { return formatDate(d); } },
      { data: 'order_number', orderable: false,
        render: function(d, type, row) {
          var html = '<a href="<?= base_url('canvassing/customer-orders/view') ?>/' + d + '" class="btn btn-sm btn-info mr-1"><i class="fas fa-eye"></i></a>';
          if (row.status === 'pending') {
            html += '<a href="<?= base_url('canvassing/customer-orders/upload-proof') ?>/' + d + '" class="btn btn-sm btn-success"><i class="fas fa-upload"></i></a>';
          }
          return html;
        }
      }
    ]
  });

  $('#filter-status').on('change', function() { table.ajax.reload(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val(null).trigger('change');
    table.ajax.reload();
  });
});
</script>
