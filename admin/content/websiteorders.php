<!-- WallacePOS: Copyright (c) 2024 Corner Bites <info@cornerbites.co.ke> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Website Orders
    </h1>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="table-header">
    Orders received from cornerbites.co.ke
</div>

<table id="websiteorderstable" class="table table-striped table-bordered table-hover" style="width:100%;">
<thead>
<tr>
    <th>ID</th>
    <th>External Ref</th>
    <th>Customer</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Items</th>
    <th>Total</th>
    <th>Status</th>
    <th>Received</th>
    <th>Actions</th>
</tr>
</thead>
<tbody id="websiteordersbody">
    <tr><td colspan="10" class="center">Loading...</td></tr>
</tbody>
</table>

</div>
</div>

<script>
function loadWebsiteOrders(){
    var tbody = $('#websiteordersbody');
    tbody.html('<tr><td colspan="10" class="center">Loading...</td></tr>');
    $.get('/api/poll.php?last=0', function(data){
        if (data.error) { tbody.html('<tr><td colspan="10" class="center text-danger">'+data.error+'</td></tr>'); return; }
        var sales = (data.result && data.result.sales) ? data.result.sales : [];
        var rows = '';
        sales.forEach(function(s){
            if (s.source !== 'website') return;
            var items = '';
            for (var k in s.items) { items += s.items[k].qty + 'x ' + s.items[k].name + '<br>'; }
            var statusClass = {'queued':'warning','preparing':'info','ready':'success','completed':'default'}[s.orderstatus] || 'default';
            rows += '<tr>'
                + '<td>'+s.id+'</td>'
                + '<td>'+s.external_ref+'</td>'
                + '<td>'+(s.customer ? s.customer.name : '-')+'</td>'
                + '<td>'+(s.customer ? s.customer.phone : '-')+'</td>'
                + '<td>'+(s.delivery_address || '-')+'</td>'
                + '<td>'+items+'</td>'
                + '<td>'+s.total+'/=</td>'
                + '<td><span class="label label-'+statusClass+'">'+s.orderstatus+'</span></td>'
                + '<td>'+s.received_at+'</td>'
                + '<td style="white-space:nowrap;">'
                + '<button class="btn btn-xs btn-info" onclick="updateWebsiteOrderStatus(\''+s.ref+'\',\'preparing\')">Preparing</button> '
                + '<button class="btn btn-xs btn-success" onclick="updateWebsiteOrderStatus(\''+s.ref+'\',\'ready\')">Ready</button> '
                + '<button class="btn btn-xs btn-default" onclick="updateWebsiteOrderStatus(\''+s.ref+'\',\'completed\')">Done</button> '
                + '<button class="btn btn-xs btn-danger" onclick="deleteWebsiteOrder(\''+s.ref+'\')" title="Delete"><i class="icon-trash"></i></button>'
                + '</td>'
                + '</tr>';
        });
        if (!rows) rows = '<tr><td colspan="10" class="center">No website orders yet.</td></tr>';
        tbody.html(rows);
    }, 'json').fail(function(){ tbody.html('<tr><td colspan="10" class="center text-danger">Failed to load orders.</td></tr>'); });
}
function updateWebsiteOrderStatus(ref, status){
    WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref: ref, orderstatus: status}), function(r){
        if (r) loadWebsiteOrders();
    });
}
function deleteWebsiteOrder(ref){
    if (!confirm('Delete this order permanently?')) return;
    WPOS.sendJsonData("orders/remove", JSON.stringify({ref: ref}), function(r){
        if (r !== false) loadWebsiteOrders();
    });
}
$(document).ready(function(){ loadWebsiteOrders(); setInterval(loadWebsiteOrders, 10000); });
</script>
