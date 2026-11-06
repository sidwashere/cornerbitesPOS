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
    <span style="float:right; font-weight:normal;">
        <button class="btn btn-xs btn-default" onclick="if(typeof setOrderSoundMuted==='function') setOrderSoundMuted(!isOrderSoundMuted());" data-order-sound-toggle>🔊 Sound: On</button>
        <span id="websiteOrdersSummary" style="font-size:11px; color:#999;"></span>
    </span>
</div>

<div style="max-height: 420px; overflow-y: auto; border:1px solid #ddd; border-top:none;">
<table id="websiteorderstable" class="table table-striped table-bordered table-hover cb-compact" style="width:100%; margin-bottom:0; font-size:12px;">
<thead style="position:sticky; top:0; z-index:5;">
<tr style="background:#f0f0f0;">
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">ID</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">External Ref</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Customer</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Phone</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Address</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Table</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Items</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Total</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Status</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Received</th>
    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Actions</th>
</tr>
</thead>
<tbody id="websiteordersbody">
    <tr><td colspan="11" class="center">Loading...</td></tr>
</tbody>
</table>
</div>
<style>.cb-compact td{padding:2px 6px !important; vertical-align:middle;} .cb-compact .btn-xs{padding:1px 5px; font-size:11px;}</style>

</div>
</div>

<script>
function loadWebsiteOrders(){
    var tbody = $('#websiteordersbody');
    tbody.html('<tr><td colspan="11" class="center">Loading...</td></tr>');
    $.get('/api/poll.php?last=0', function(data){
        if (data.error) { tbody.html('<tr><td colspan="11" class="center text-danger">'+data.error+'</td></tr>'); return; }
        var sales = (data.result && data.result.sales) ? data.result.sales : [];
        try { if (typeof checkNewOrders === 'function') checkNewOrders(sales, 'website'); } catch(e){}
        try {
            var webSales = sales.filter(function(s){ return s.source === 'website'; });
            var queued = webSales.filter(function(s){ return (s.orderstatus || 'queued') === 'queued'; });
            var pipeline = queued.reduce(function(sum, s){ return sum + parseFloat(s.total || 0); }, 0);
            $('#websiteOrdersSummary').text(webSales.length + ' total · ' + queued.length + ' queued · KES ' + pipeline.toFixed(2) + ' pipeline');
        } catch(e){}
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
                + '<td>'+(s.table_number ? 'Table ' + s.table_number : '-')+'</td>'
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
        if (!rows) rows = '<tr><td colspan="11" class="center">No website orders yet.</td></tr>';
        tbody.html(rows);
    }, 'json').fail(function(){ tbody.html('<tr><td colspan="11" class="center text-danger">Failed to load orders.</td></tr>'); });
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
$(document).ready(function(){ try { if (typeof initOrderSound === 'function') initOrderSound(); } catch(e){} loadWebsiteOrders(); setInterval(loadWebsiteOrders, 15000); });
</script>
