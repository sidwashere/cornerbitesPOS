<div class="row">
    <div class="col-xs-12">
        <h3 class="header blue lighter bigger">
            <i class="icon-fire"></i>&nbsp;Fast Food Operations Center
        </h3>
        <p>Active orders, queue, dine-in tables and delivery status in one place.</p>
        <button class="btn btn-sm btn-primary" onclick="loadFastFoodOrders();"><i class="icon-refresh"></i> Refresh</button>
        <hr/>
        <div id="ffops-container" class="row"></div>
    </div>
</div>
<script>
    function statusBadge(status){
        var map = {queued:'warning', preparing:'info', ready:'success', completed:'default', delivered:'success'};
        var cls = map[status] || 'default';
        return '<span class="label label-'+cls+'">'+(status||'queued')+'</span>';
    }
    function loadFastFoodOrders(){
        var result = WPOS.sendJsonData("sales/get", JSON.stringify({}));
        if (result === false) { return; }
        var container = $("#ffops-container");
        container.html("");
        var count = 0;
        for (var ref in result) {
            var sale = result[ref];
            if (sale.status != 0) continue; // only active orders
            count++;
            var orderstatus = sale.orderstatus || 'queued';
            var itemsHtml = "";
            for (var i=0; i<sale.items.length; i++){
                itemsHtml += sale.items[i].qty + "x " + sale.items[i].name + "<br/>";
            }
            var typeLabel = (sale.tablenum && sale.tablenum != 0) ? "Dine In (Table "+sale.tablenum+")" : "Take Away / Delivery";
            var card = '<div class="col-sm-4 col-xs-12" style="margin-bottom:15px;">'+
                '<div class="widget-box">'+
                '<div class="widget-header"><h4 class="smaller">Order #'+sale.id+' <span style="float:right;">'+statusBadge(orderstatus)+'</span></h4></div>'+
                '<div class="widget-body" style="padding:10px;">'+
                '<strong>Type:</strong> '+typeLabel+'<br/>'+
                '<strong>Total:</strong> '+WPOS.util.currencyFormat(sale.total)+'<br/>'+
                '<strong>Items:</strong><br/>'+itemsHtml+
                '<div style="margin-top:10px;">'+
                '<button class="btn btn-xs btn-warning" onclick="updateOrderStatus(\''+sale.ref+'\',\'preparing\')">Preparing</button> '+
                '<button class="btn btn-xs btn-success" onclick="updateOrderStatus(\''+sale.ref+'\',\'ready\')">Ready</button> '+
                '<button class="btn btn-xs btn-primary" onclick="updateOrderStatus(\''+sale.ref+'\',\'completed\')">Complete</button>'+
                '</div></div></div></div>';
            container.append(card);
        }
        if (count === 0) {
            container.html('<div class="col-xs-12"><div class="alert alert-info">No active orders in queue.</div></div>');
        }
    }
    function updateOrderStatus(ref, status){
        var result = WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref:ref, orderstatus:status}));
        if (result !== false) {
            loadFastFoodOrders();
        }
    }
    // initial load
    $(function(){ loadFastFoodOrders(); });
</script>