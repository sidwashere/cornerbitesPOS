function WPOSOrders(){
    var self = this;
    try {
        setInterval(function(){
            try {
                if (document.hidden) return; // keep stale in background — no flash on return
                if ($("#onlineordersbody").length && $("#wrapper").tabs('option','active') == 2) {
                    if(typeof self.loadOnlineOrders === 'function'){ self.loadOnlineOrders(); }
                } else if (document.getElementById('posBrief')) {
                    // Keep the Till brief fresh even off the Online tab
                    if (typeof WPOS !== 'undefined' && WPOS.sendJsonDataAsync) {
                        WPOS.sendJsonDataAsync('orders/get', JSON.stringify({limit: 100}), function(data){
                            var sales = Array.isArray(data) ? data : (data && data.data ? data.data : []);
                            try { updatePosBrief(Array.isArray(sales) ? sales : Object.values(sales || {})); } catch(e) {}
                        }, function(){});
                    }
                }
            } catch(e){}
        }, 15000);
    } catch(e){}
    var ordercontain = $("#ordercontainer");
    var orderhistcontain = $("#orderhistcontainer");

    this.processOrder = function(orderdata, olddata){
        var device = WPOS.getConfigTable().deviceconfig;
        if (device.type=="order_register") {
            if (device.ordertype == "printer") {
                // print to the kitchen printer
                processOrders(orderdata, olddata, 'kitchen', device.orderdisplay);
            } else if (device.ordertype == "terminal") {
                // set timeout only if updates
                if (processOrders(orderdata, olddata, null, device.orderdisplay)) {
                    timeouts[orderdata.ref] = setTimeout(function () {
                        kitchenTerminalFallback(orderdata, olddata);
                    }, 10000);
                }
            }
        }
    };

    function processOrders(data, olddata, print, display){
        var modcount = 0;
        if (typeof data === "object") {
            // check for old data, if none available process as new orders
            if (olddata) {
                if (data.hasOwnProperty('orderdata')){
                    for (var i in data.orderdata){
                        if (olddata.orderdata.hasOwnProperty(i)){
                            // the moddt param exists the order may have been modified, check further
                            if (data.orderdata[i].hasOwnProperty('moddt')){
                                // if the moddt flag doesn't exist on the old order moddt or is smaller than the new value
                                if (!olddata.orderdata[i].hasOwnProperty('moddt') || data.orderdata[i].moddt>olddata.orderdata[i].moddt) {

                                    if (display){
                                        WPOS.orders.removeOrder(data.ref, i);
                                        insertOrder(data, i);
                                    }
                                    if (print)
                                        WPOS.print.printOrderTicket(print, data, i, "ORDER UPDATED");
                                    modcount++;
                                }
                            }
                        } else {

                            if (display)
                                insertOrder(data, i);
                            if (print)
                                WPOS.print.printOrderTicket(print, data, i, null);
                            modcount++;
                        }
                    }
                } else {
                    // no order data exists in the new data, remove all
                    if (olddata.hasOwnProperty('orderdata'))
                        for (var r in olddata.orderdata){
                            if (display)
                                WPOS.orders.removeOrder(olddata.ref, r);
                            if (print)
                                WPOS.print.printOrderTicket(print, olddata, r, "ORDER CANCELLED");
                            modcount++;
                        }
                }
            } else {

                if (data.hasOwnProperty('orderdata'))
                    for (var o in data.orderdata) {
                        if (display)
                            insertOrder(data, o);
                        if (print)
                            WPOS.print.printOrderTicket(print, data,o, null);
                        modcount++;
                    }
            }
        } else {
            // process removed orders if they exists in the system
            if (olddata){
                if (olddata.hasOwnProperty('orderdata'))
                    for (var d in olddata.orderdata){
                        if (display)
                            WPOS.orders.removeOrder(olddata.ref, d);
                        if (print)
                            WPOS.print.printOrderTicket(print, olddata, d, "ORDER CANCELLED");
                        modcount++;
                    }
            }
        }

        return modcount;
    }

    var timeouts = [];
    this.kitchenTerminalAcknowledge = function(ref){
        // the main kitchen terminal has received the order
        if (timeouts.hasOwnProperty(ref)) {
            clearTimeout(timeouts[ref]);
            delete timeouts[ref];
        }
        console.log("Order acknowledgement received from kitchen terminal");
    };

    function kitchenTerminalFallback(orderdata, olddata){
        // TODO: have kitchen printer for a fallback option
       var answer = confirm("The last order has not been received by the kitchen terminal,\nwould you like to print and order tickets here to take to the kitchen?");
       if (answer){
           processOrders(orderdata, olddata, 'receipts', false);
       }
    }

    function insertOrder(saleobj, orderid){
        var order = saleobj.orderdata[orderid];
        var elem = $("#orderbox_template").clone().removeClass('hide').attr('id', 'order_box_'+saleobj.ref+'-'+order.id);
        elem.find('.orderbox_orderid').text(order.id);
        elem.find('.orderbox_saleref').text(saleobj.ref);
        elem.find('.orderbox_orderdt').text(WPOS.util.getDateFromTimestamp(order.processdt));
        var itemtbl = elem.find('.orderbox_items');
        for (var i in order.items){
            var item = saleobj.items[order.items[i]]; // the items object links the item id with it's index in the data
            var modStr = "";
            if (item.hasOwnProperty('mod')){
                for (var x=0; x<item.mod.items.length; x++){
                    var mod = item.mod.items[x];
                    modStr+= '<br/>'+(mod.hasOwnProperty('qty')?((mod.qty>0?'+ ':'')+mod.qty+' '):'')+mod.name+(mod.hasOwnProperty('value')?': '+mod.value:'')+' ('+WPOS.util.currencyFormat(mod.price)+')';
                }
            }
            itemtbl.append('<tr><td style="width:10%;"><strong>'+item.qty+'</strong></td><td><strong>'+item.name+'</strong>'+modStr+'<br/></td></tr>');
        }
        ordercontain.prepend(elem);
    }
    this.removeOrder = function(ref, orderid){
        $("#order_box_" + ref + '-' + orderid).remove();
    };
    this.moveOrderToHistory = function(ref, orderid){
        $("#order_box_" + ref + '-' + orderid).detach().prependTo(orderhistcontain);
    };

    this.loadOnlineOrders = function(){
        var tbody = $("#onlineordersbody");
        if (tbody.length == 0) return;
        if (document.hidden) return; // keep stale while tab is in background — no flash on return
        // First paint only shows a loader; refreshes keep stale rows until data changes
        if (!tbody.attr('data-cb-sig') && tbody.text().trim() === '') {
            tbody.html('<tr><td colspan="12" class="center">Loading...</td></tr>');
        }
        if (typeof WPOS !== 'undefined' && WPOS.sendJsonDataAsync) {
            WPOS.sendJsonDataAsync("orders/get", JSON.stringify({limit: 100}), function(data){
                if (!data) { tbody.html('<tr><td colspan="11" class="center text-danger">no data</td></tr>'); return; }
                if (data === false) { tbody.html('<tr><td colspan="11" class="center text-danger">request failed</td></tr>'); return; }
                var sales = Array.isArray(data) ? data : (data && data.data ? data.data : []);
                try { if (typeof checkNewOrders === 'function') checkNewOrders(Array.isArray(sales) ? sales : Object.values(sales || {}), 'website'); } catch(e){}
                var rows = '';
                (Array.isArray(sales)?sales:Object.values(sales||{})).forEach(function(s){
                    var items = '';
                    if (s.items) {
                        for (var k in s.items) { items += (s.items[k].qty||1) + 'x ' + (s.items[k].name||'') + '<br>'; }
                    }
                    var status = s.orderstatus || (s.status==1?'completed':(s.status==0?'queued':'unknown'));
                    var statusClass = {'queued':'warning','preparing':'info','ready':'success','completed':'default','received':'warning'}[status] || 'default';
                    var custName = s.customer ? (s.customer.name || s.customer.first_name || '') : '-';
                    var custPhone = s.customer ? (s.customer.phone || s.customer.telephone || '') : '-';
                    var locText = s.delivery_address || (s.table_number ? 'Table ' + s.table_number : (s.order_type ? s.order_type : '-'));
                    var payMethod = s.payment_method || 'mpesa till';
                    rows += '<tr>'
                        + '<td>'+(s.id||'-')+'</td>'
                        + '<td>'+(s.ref||'')+'<br/>'+(s.external_ref||'')+'</td>'
                        + '<td>'+(s.source||'pos')+'</td>'
                        + '<td>'+custName+'</td>'
                        + '<td>'+custPhone+'</td>'
                        + '<td style="font-size:11px; max-width:140px; overflow:hidden; text-overflow:ellipsis;" title="'+String(locText).replace(/"/g,'&quot;')+'">'+String(locText).replace(/</g,'&lt;')+'</td>'
                        + '<td>'+items+'</td>'
                        + '<td>'+(s.total||0)+'/=</td>'
                        + '<td>'+payMethod+'</td>'
                        + '<td><span class="label label-'+statusClass+'">'+status+'</span></td>'
                        + '<td>'+(s.received_at||s.dt||s.processdt||'')+'</td>'
                        + '<td style="white-space:nowrap;">'
                        + '<button class="btn btn-xs btn-info" onclick="WPOS.orders.updateOnlineStatus(\''+s.ref+'\',\'preparing\')">Preparing</button> '
                        + '<button class="btn btn-xs btn-success" onclick="WPOS.orders.updateOnlineStatus(\''+s.ref+'\',\'ready\')">Ready</button> '
                        + '<button class="btn btn-xs btn-primary" onclick="WPOS.orders.markOnlineComplete(\''+s.ref+'\')">Complete & Move to Sales</button> '
                        + '<button class="btn btn-xs btn-default" onclick="WPOS.trans.showTransactionInfo(\''+s.ref+'\'); $(\'#wrapper\').tabs(\'option\',\'active\',1);">View</button> '
                        + '<button class="btn btn-xs btn-danger" onclick="WPOS.orders.removeOnlineOrder(\''+s.ref+'\')" title="Delete"><i class="icon-trash"></i></button>'
                        + '</td>'
                        + '</tr>';
                });
                if (!rows) rows = '<tr><td colspan="12" class="center">No online orders yet.</td></tr>';
                // Flicker-free: only write when the rendered list actually changed
                if (typeof WPOS !== 'undefined' && WPOS.util && typeof WPOS.util.patchIfChanged === 'function') {
                    WPOS.util.patchIfChanged(tbody, rows);
                } else { tbody.html(rows); }
                try { updatePosBrief(Array.isArray(sales) ? sales : Object.values(sales || {})); } catch(e) {}
            }, function(){ /* keep stale rows on transient errors; never alert from a background refresh */ });
        } else {
            tbody.html('<tr><td colspan="12" class="center text-danger">WPOS not ready</td></tr>');
        }
    };

    function updatePosBrief(sales){
        var q = 0, prep = 0, ready = 0, today = 0, oldest = null;
        var now = Date.now();
        (sales || []).forEach(function(s){
            var st = s.orderstatus || (s.status == 1 ? 'completed' : (s.status == 0 ? 'queued' : ''));
            if (st === 'queued') q++;
            else if (st === 'preparing') prep++;
            else if (st === 'ready') ready++;
            else if (st === 'completed') today += parseFloat(s.total || 0);
            var ts = s.processdt || (s.received_at ? Date.parse(s.received_at) : 0);
            if ((st === 'queued' || st === 'preparing') && ts && (oldest === null || ts < oldest)) oldest = ts;
        });
        var setText = function(id, v){ var el = document.getElementById(id); if (el && el.textContent !== String(v)) el.textContent = v; };
        setText('bQueued', q); setText('bPreparing', prep); setText('bReady', ready);
        setText('bOldest', oldest ? Math.max(0, Math.round((now - oldest) / 60000)) + ' min' : '—');
        setText('bToday', (typeof WPOS !== 'undefined' && WPOS.util && WPOS.util.money) ? WPOS.util.money(today) : ('KSh ' + today.toFixed(2)));
    }
    var _lowTick = null;
    function refreshLowStockBrief(){
        try {
            if (typeof WPOS !== 'undefined' && WPOS.sendJsonDataAsync) {
                WPOS.sendJsonDataAsync('stock/stats', JSON.stringify({}), function(res){
                    var el = document.getElementById('bLow');
                    if (el && res && res.kpis) el.textContent = (res.kpis.low_stock || 0) + ' items';
                }, function(){ /* silent background poll */ });
            }
        } catch(e) {}
    }
    try {
        refreshLowStockBrief();
        if (!_lowTick) _lowTick = setInterval(function(){ if (!document.hidden) refreshLowStockBrief(); }, 60000);
    } catch(e) {}

    this.updateOnlineStatus = function(ref, status){
        if (typeof WPOS !== 'undefined' && WPOS.sendJsonData){
            WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref: ref, orderstatus: status}), function(r){
                if (r !== false) { if(WPOS.orders.loadOnlineOrders) WPOS.orders.loadOnlineOrders(); }
            });
        }
    };

    this.removeOnlineOrder = function(ref){
        if (!confirm('Delete this order?')) return;
        if (typeof WPOS !== 'undefined' && WPOS.sendJsonData){
            WPOS.sendJsonData("orders/remove", JSON.stringify({ref: ref}), function(r){
                if (r !== false) { if(WPOS.orders.loadOnlineOrders) WPOS.orders.loadOnlineOrders(); }
            });
        }
    };

    this.markOnlineComplete = function(ref){
        if (typeof WPOS !== 'undefined' && WPOS.sendJsonData){
            // Mark as completed status
            WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref: ref, orderstatus: 'completed', status: 1}), function(r){
                // Also try to ensure it appears in sales as completed
                if (WPOS.orders.loadOnlineOrders) WPOS.orders.loadOnlineOrders();
                if (WPOS.trans && WPOS.trans.setupTransactionView) { WPOS.trans.setupTransactionView(); }
                alert('Order marked as completed. It should appear in Sales tab.');
                $('#wrapper').tabs('option', 'active', 1);
            });
        }
    };

}