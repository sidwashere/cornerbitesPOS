function WPOSOrders(){
    var self = this;
    try {
        setInterval(function(){ if(typeof self.loadOnlineOrders === 'function'){ try { self.loadOnlineOrders(); } catch(e){} } }, 10000);
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
}

    this.loadOnlineOrders = function(){
        var tbody = $("#onlineordersbody");
        if (tbody.length == 0) { $("body").append("<div id=dbg>NO TBODY</div>"); return; }
        tbody.html('<tr><td colspan="11" class="center">DEBUG: function called, tbody found</td></tr>');
        if (typeof WPOS === 'undefined') { tbody.html('<tr><td colspan="11" class="center">WPOS undefined</td></tr>'); return; }
        if (!WPOS.orders) { tbody.html('<tr><td colspan="11" class="center">WPOS.orders undefined</td></tr>'); return; }
        tbody.html('<tr><td colspan="11" class="center">WPOS.orders exists, calling API...</td></tr>');
        WPOS.sendJsonDataAsync("orders/get", JSON.stringify({limit: 5}), function(data){
            tbody.html('<tr><td colspan="11" class="center">CALLBACK FIRED, data type: '+(typeof data)+', isArray: '+Array.isArray(data)+'</td></tr>');
        });
    };

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
