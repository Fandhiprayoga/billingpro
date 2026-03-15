<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Customer Saya</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped" id="table-customers" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nama Usaha</th>
                <th>No. Telp</th>
                <th>Lisensi Aktif</th>
                <th>Order Pending</th>
                <th width="160">Aksi</th>
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
  var table = $('#table-customers').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: '<?= base_url('canvassing/my-customers/ajax') ?>' },
    order: [[1, 'asc']],
    columns: [
      { data: 'customer_id', className: 'text-center',
        render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }
      },
      { data: 'username' },
      { data: 'email', render: function(d) { return d ? d : '-'; } },
      { data: 'nama_usaha', render: function(d) { return d ? d : '-'; } },
      { data: 'no_telp', render: function(d) { return d ? d : '-'; } },
      { data: 'active_licenses', className: 'text-center',
        render: function(d) {
          return d > 0
            ? '<span class="badge badge-success">' + d + '</span>'
            : '<span class="badge badge-secondary">0</span>';
        }
      },
      { data: 'pending_orders', className: 'text-center',
        render: function(d) {
          return d > 0
            ? '<span class="badge badge-warning">' + d + '</span>'
            : '<span class="badge badge-secondary">0</span>';
        }
      },
      { data: 'customer_id', orderable: false,
        render: function(d) {
          return '<a href="<?= base_url('canvassing/my-customers') ?>/' + d + '" class="btn btn-sm btn-info mr-1" title="Detail"><i class="fas fa-eye"></i></a>' +
                 '<a href="<?= base_url('canvassing/customer-orders/create') ?>/' + d + '" class="btn btn-sm btn-primary" title="Buat Order"><i class="fas fa-cart-plus"></i></a>';
        }
      }
    ]
  });
});
</script>
