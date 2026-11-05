
/**
 * sales.js is part of Wallace Point of Sale system (WPOS)
 *
 * sales.js Provides functions for processing sales, including the storage and upload of offline sales,
 * It also includes WPOSItems object for adding items to the sale.
 *
 * Cornerbites POS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * Cornerbites POS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      Class created 15/1/14 12:01 PM
 */

function WPOSItems() {
    // public members for modifying the current sales items
    /**
     * Adds a blank item row for the user to fill in
     */
    this.addManualItemRow = function () {
        // add the row
        addItemRow(1, "", "0.00", 1, 0, {desc:"", cost:0.00, unit_original:0.00});
        // focus on qty
        $("#itemtable")
            .children('tr :last')
            .find(".itemqty")
            .focus();
        // mark invalid records
        WPOS.sales.updateSalesTotal();
    };
    /**
     * Removes an item from the sale
     * @param {string} sitem
     */
    this.removeItem = function (sitem) {
        $(sitem).parent('td').parent('tr').remove();
        WPOS.sales.updateSalesTotal();
    };

    /**
     * Adds an item from a stock code
     * @param {string} code
     */
    this.addItemFromStockCode = function (code) {
        // find the item id from the stock code index and use it to retrieve the record.
        var item = WPOS.getItemsTable()[WPOS.getStockIndex()[code.toUpperCase()]];
        if (item === null || item === undefined || item === "") {//ADAM: Should use triple equals
            alert("Item not found");
            $("#codeinput").val('');
        } else {
            // add the item
            addItem(item);
        }
    };

    /**
     *
     * @param {Number} id
     */
    this.addItemFromId = function (id) {
        var item = WPOS.getItemsTable()[id];
        if (item === null || item === undefined) {
            alert("Item not found");
        } else {
            // add the item
            addItem(item);
        }
    };

    /**
     *
     * @param {String} query
     * @returns {Array}
     */
    this.searchItems = function (query) {
        var results = [];
        query.trim();
        if (query !== '') {
            var upquery = query.toUpperCase();
            // search items for the text.
            var itemtable = WPOS.getItemsTable();
            for (var key in itemtable) {
                if (!itemtable.hasOwnProperty(key)) {
                    continue;
                }
                if (itemtable[key].name.toUpperCase().indexOf(upquery) != -1) {
                    results.push(itemtable[key]);
                } else if (itemtable[key].code.toUpperCase().indexOf(upquery) != -1) {
                    results.push(itemtable[key]);
                }
            }
        }
        //alert(JSON.stringify(results));
        return results;
    };

    this.generateItemGrid = function(categoryId){
        var iboxitems = $("#iboxitems");
        iboxitems.html('<div style="padding: 5px;"><button class="btn btn-sm btn-primary" onclick="WPOS.items.generateItemGridCategories();"><i class="icon-backward">&nbsp;</i>Categories</button></div>');
        var price;
        var items = [];
        if (categoryId>-1){
            if (WPOS.getCategoryIndex().hasOwnProperty(categoryId)) {
                var index = WPOS.getCategoryIndex()[categoryId];
                var tempitems = WPOS.getItemsTable();
                for (var x = 0; x < index.length; x++) {
                    items[index[x]] = tempitems[index[x]];
                }
            }
        } else {
            items = WPOS.getItemsTable();
        }

        for (var i in items){
            price = (items[i].price==""?"??.??":parseFloat(items[i].price).toFixed(2));
            iboxitems.append('<div class="iboxitem" onclick="WPOS.items.addItemFromId('+items[i].id+');">' +
                                '<h6>'+items[i].name+'</h6>'+
                                '<h5>'+WPOS.util.currencyFormat(price)+'</h5>'+
                            '</div>');
        }
    };

    this.generateItemGridCategories = function(){
        var iboxitems = $("#iboxitems");
        iboxitems.html('<div class="iboxitem" onclick="WPOS.sales.generateQuickItemsGrid();"><h5>Quick Items</h5><h6>(Favorites)</h6></div>');
        var itemtable = {};
        try { itemtable = WPOS.getItemsTable() || {}; } catch(e) {}
        iboxitems.append('<div class="iboxitem" onclick="WPOS.items.generateItemGrid(-1);"><h5>All Categories</h5><h6>('+Object.keys(itemtable).length+' items)</h6></div>');
        var catindex = {};
        try { catindex = WPOS.getCategoryIndex() || {}; } catch(e) {}
        var categories = {};
        try { categories = WPOS.getConfigTable().item_categories || {}; } catch(e) {}
        //console.log(catindex);
        for (var i in categories){
            iboxitems.append('<div class="iboxitem" onclick="WPOS.items.generateItemGrid('+i+');">' +
                '<h5>'+categories[i].name+'</h5>'+
                '<h6>('+(catindex.hasOwnProperty(i)?catindex[i].length:0)+' items)</h6>'+
                '</div>');
        }
        var misctotal = catindex.hasOwnProperty(0)?catindex[0].length:0;
        iboxitems.append('<div class="iboxitem" onclick="WPOS.items.generateItemGrid(0);"><h5>Miscellaneous</h5><h6>('+misctotal+' items)</h6></div>');
    };

    this.generateQuickItemsGrid = function(){
        return this.generateItemGridCategories();
    };

    this.toggleQuickItem = function(itemId){
        return false;
    };

    this.showQuickItemPicker = function(){
        return false;
    };

    /**
     * Adds a html row into the sales table, if sitem id is greater than 0, all fields that are filled are disabled to prevent modification.
     * @param {Number} qty
     * @param {String} name
     * @param {String} unit
     * @param {Number} taxid
     * @param {Number} sitemid ; the stored item id to keep track of inventory sales
     * @param data
     */
    function addItemRow(qty, name, unit, taxid, sitemid, data) {
        try {
        sitemid = (sitemid>0?sitemid:0);
        var disable = (sitemid>0); // disable fields that are filled by the stored item
        var disableprice = false;
        var disabletax = true;
        try {
            var cfg = WPOS.getConfigTable();
            if (cfg && cfg.pos) {
                disableprice = (sitemid>0 && cfg.pos.priceedit!="always");
                disabletax = (!cfg.pos.hasOwnProperty('taxedit') || cfg.pos.taxedit=='no');
            }
        } catch(e) {}
        var row = $('<tr class="item_row">' +
            '<td><input class="itemid" type="hidden" value="' + sitemid + '" data-options=\''+JSON.stringify(data)+'\' /><input onChange="WPOS.sales.updateSalesTotal();" style="width:50px;" type="text" class="itemqty numpad" value="' + qty + '" /></td>' +
            '<td><input '+((disable==true && name!="")?"disabled":"")+' style="width: 100%; min-width: 100px;" type="text" class="itemname" value="' + name + '" onChange="WPOS.sales.updateSalesTotal();" /><div class="itemmodtxt"></div></td>' +
            '<td><input '+((disableprice==true && unit!="")?"disabled":"")+' onChange="WPOS.sales.updateSalesTotal();" style="max-width:50px;" type="text" class="itemunit numpad" value="' + unit + '" /></td>' +
            '<td><button onclick="WPOS.items.openItemModDialog(this);" class="btn btn-primary btn-xs"><i class="icon-list-ul"></i></button><div class="itemmodtext"></div></td>' +
            '<td><select '+((disabletax==true && taxid!=null)?"disabled":"")+' onChange="WPOS.sales.updateSalesTotal();" style="max-width:110px;" class="itemtax">' +getTaxSelectHTML(taxid)+ '</select><input class="itemtaxval" type="hidden" value="0.00" /></td>' +
            '<td><input style="max-width:50px;" type="text" class="itemprice" value="0.00" disabled /></td>' +
            '<td style="text-align: center;"><button class="btn btn-sm btn-danger" onclick="WPOS.items.removeItem($(this));">X</button></td>' +
            '</tr>');
        if (data.orderid) {
            row.insertAfter("#order_row_"+data.orderid);
        } else {
            $("#itemtable").append(row);
        }
        $('#items_contain').scrollTop(1E10);
        // reinitialize keypad & field listeners
        try { WPOS.initKeypad(); } catch(e) {}
        } catch(e) { console.error("addItemRow error:", e); }
    }
    this.addItemRow = function(qty, name, unit, taxid, sitemid, data){
        addItemRow(qty, name, unit, taxid, sitemid, data)
    };

    /**
     * Gets or generates the taxid select HTML depending on input
     * @param {Number} taxid
     * @returns {String}
     */
    function getTaxSelectHTML(taxid) {
        var taxselecthtml = "";
        try {
            var taxtable = WPOS.getTaxTable();
            var taxrules = (taxtable && taxtable.rules) ? taxtable.rules : {};
            for (var key in taxrules) {
                if (taxrules.hasOwnProperty(key)) {
                    taxselecthtml += "<option id='taxrule-" + key + "' value='" + key + "' "+(taxid==key?"selected='selected'":"")+">" + taxrules[key].name + "</option>";
                }
            }
        } catch(e) {}
        if (taxselecthtml == "") {
            taxselecthtml = "<option value='1'>No Tax</option>";
        }
        return taxselecthtml;
    }

    /**
     *
     * @param {Object} item
     */
    function addItem(item) {
        try {
        // Item cost may be null if we're adding stored items that were created in a previous version, explicitly set the cost in this case.
        if (!item || !item.hasOwnProperty) { console.error("addItem: invalid item", item); return; }
        if (!item.hasOwnProperty('cost')) item.cost = 0.00;
        if (item.price === undefined || item.price === null) item.price = "";
        // TODO: remove last row from table if its invalid?
        // check if a priced item is already present in the sale and if so increment it's qty
        if (item.price==""){
            // insert item into table
            addItemRow(1, item.name, item.price, item.taxid, item.id, {desc:item.description || "", cost:item.cost, unit_original:item.price, alt_name:item.alt_name || ""});
        } else {
            if (!isItemAdded(item.id, true)){
                // insert item into table
                addItemRow(1, item.name, item.price, item.taxid, item.id, {desc:item.description || "", cost:item.cost, unit_original:item.price, alt_name:item.alt_name || ""});
            }
        }
        $("#codeinput").val('');
        } catch(e) {
            console.error("addItem error:", e);
        }
        // Always try to update totals
        try {
            if (WPOS && WPOS.sales && typeof WPOS.sales.updateSalesTotal === 'function') {
                WPOS.sales.updateSalesTotal();
            }
        } catch(e) {
            console.error("updateSalesTotal error:", e);
        }
    }

    function isItemAdded(itemid, addqty){
        var found = false;
        $("#itemtable").children(".valid").each(function(index, element) {
            var itemfield = $(element).find(".itemid");
            if (itemfield.val()==itemid){
                // check for item modifiers, a new line item must be added if a modifier is used
                var itemopt = itemfield.data('options');
                if (!itemopt) { try { itemopt = JSON.parse(itemfield.attr('data-options')); } catch(e) { itemopt = {}; } }
                if (itemopt && itemopt.hasOwnProperty('mod') && itemopt.mod && itemopt.mod.items && itemopt.mod.items.length>0){
                    return true;
                }
                if (addqty) $(element).find(".itemqty").val(parseInt($(element).find(".itemqty").val())+1);
                found = true;
                return false;
            }
            return true;
        });
        return found;
    }

    var itemrow;
    this.openItemModDialog = function(elem){
        itemrow = $(elem).parent().parent();
        var data = itemrow.find('.itemid').data('options');
        //console.log(data);
        $("#itemdesc").val(data.desc);
        var disableprice = (itemrow.find('.itemid').val()>0 && WPOS.getConfigTable().pos.priceedit!="always");
        $("#itemcost").val(data.cost).prop('disabled', (disableprice && data.cost!=""));
        $("#itemaltname").val(data.alt_name);
        // get stored item mods
        var itemid = itemrow.find('.itemid').val();
        modtable.html('');
        if (itemid>0){
            var itemmods = WPOS.getItemsTable()[itemid].modifiers;
            if (itemmods!=null && itemmods.length>0){
                for (var i=0; i<itemmods.length; i++){
                    zerostr = WPOS.util.currencyFormat(0);
                    if (itemmods[i].type=="select"){
                        insertSelectModRow(itemmods[i]);
                    } else {
                        insertSimpleModRow(itemmods[i]);
                    }
                }
            }
            // get mods for current sale item, update the item mods
            if (data.hasOwnProperty('mod')){
                var mods = data.mod.items;
                for (i=0; i<mods.length; i++){
                    var mod = mods[i];
                    var row = $("#mod-"+mod.name.replace(/\s/g, ""));
                    if (row){
                        var costelem = row.find('.modcost');
                        if (mod.hasOwnProperty('qty')){
                            var qtyelem = row.find('.modqty');
                            qtyelem.text(qtyelem.data('default') + mod.qty);
                            costelem.data('modqty', mod.qty);
                            costelem.data('modprice', mod.price);
                            costelem.text(WPOS.util.currencyFormat(mod.price));
                        } else {
                            row.find('.modselect').val(mod.value);
                            costelem.data('modprice', mod.price);
                            costelem.text(WPOS.util.currencyFormat(mod.price));
                        }
                    } else {
                        // TODO: Handle mods deleted from stored items that are already in the order
                    }
                }
                calculateTotalMods();
            }
        }
        $("#itemoptionsdialog").dialog("open");
    };
    var modtable = $("#itemmods");
    var zerostr = "";
    function insertSimpleModRow(mod){
        modtable.append('<tr id="mod-'+mod.name.replace(/\s/g, "")+'">' +
            '<td><span class="modname">'+mod.name+'</span></td>' +
            '<td><button onclick="WPOS.items.incrementModQty(this, true);" class="btn btn-primary btn-xs" style="margin-right: 5px;"><i class="icon-arrow-up"></i></button><button onclick="WPOS.items.incrementModQty(this, false);" class="btn btn-primary btn-xs" style="margin-right: 4px;"><i class="icon-arrow-down"></i></button>' +
            '<span data-min="'+mod.minqty+'" data-max="'+mod.maxqty+'" data-default="'+mod.qty+'" data-price="'+mod.price+'" class="modqty">'+mod.qty+'</span></td>' +
            '<td><span data-moddefault="'+mod.qty+'" data-modqty="0" data-modprice="0" class="modcost">'+zerostr+'</span></td></tr>');
    }
    this.incrementModQty = function(elem, positive){
        var row = $(elem).parent().parent();
        var qtyelem = row.find('.modqty');
        var defaultqty = qtyelem.data('default');
        var minqty = qtyelem.data('min');
        var maxqty = qtyelem.data('max');
        var price = parseFloat(qtyelem.data('price'));
        var newqty = parseInt(qtyelem.text()) + (positive?1:-1);
        if (newqty<minqty || newqty>maxqty){
            var ismax = newqty>maxqty;
            alert("Cannot have "+(ismax?"more":"less")+" than "+(ismax?maxqty:minqty)+" "+row.find('.modname').text());
            return;
        }
        var modqty = newqty-defaultqty;
        var newprice = (modqty*price).toFixed(2);
        qtyelem.text(newqty);
        var costelem = row.find('.modcost');
        costelem.data('modqty', modqty);
        costelem.data('modprice', newprice);
        costelem.text(WPOS.util.currencyFormat(newprice));
        calculateTotalMods();
    };
    function insertSelectModRow(mod){
        var selecthtml = '';
        var selectdefault;
        for (var i=0; i<mod.options.length; i++){
            if (mod.options[i].default) selectdefault = mod.options[i];
            selecthtml += '<option data-default="'+mod.options[i].default+'" data-price="'+mod.options[i].price+'" value="'+mod.options[i].name+'" '+(mod.options[i].default?'selected="selected"':'')+'>'+mod.options[i].name+'</option>';
        }
        modtable.append('<tr id="mod-'+mod.name.replace(/\s/g, "")+'">' +
            '<td><span class="modname">'+mod.name+'</span></td>' +
            '<td><select onchange="WPOS.items.modSelectValue(this);" class="modselect">'+selecthtml+'</select></td>' +
            '<td><span data-moddefault="'+selectdefault.name+'" data-modprice="0" data-defaultprice="'+selectdefault.price+'" class="modcost">'+zerostr+'</span></td></tr>');
    }
    this.modSelectValue = function(selectelem){
        var costfield = $(selectelem).parent().parent().find('.modcost');
        var newprice = $(selectelem).children('option:selected').data('price');
        var defaultprice = costfield.data('defaultprice');
        var moddiffprice = (newprice - defaultprice).toFixed(2);
        costfield.data('modprice', moddiffprice);
        costfield.text(WPOS.util.currencyFormat(moddiffprice));
        calculateTotalMods();
    };
    function calculateTotalMods(){
        var total = 0;
        modtable.children('tr').each(function(){
            total += parseFloat($(this).find('.modcost').data('modprice'));
        });
        var totalfield = $("#itemmodtotal");
        totalfield.data('modtotal', total);
        totalfield.text(WPOS.util.currencyFormat(total));
        var newunit = parseFloat(itemrow.find('.itemunit').val()) + total;
        $("#itemmodunit").text(WPOS.util.currencyFormat(newunit));
    }

    this.saveItemMods = function(){
        var moddata = {total:$("#itemmodtotal").data('modtotal'), items:[]};
        var modtxt = "";
        modtable.children('tr').each(function(){
            var dataelem = $(this).find('.modcost');
            var modqty = dataelem.data('modqty');
            var moddefault = dataelem.data('moddefault');
            var modval = $(this).find('.modselect').val();
            if ((!isNaN(modqty) && modqty!=0) || (!dataelem.attr('data-modqty') && moddefault!=modval)){
                var mod = {};
                mod.name = $(this).find('.modname').text();
                mod.price = dataelem.data('modprice');
                if (dataelem.attr('data-modqty')){
                    mod.qty = modqty;
                } else {
                    mod.value = modval;
                }
                moddata.items.push(mod);
                modtxt+= (mod.hasOwnProperty('qty')?(mod.qty>0?'+ ':'')+mod.qty:'')+' '+mod.name+(mod.hasOwnProperty('value')?': '+mod.value:'')+' ('+WPOS.util.currencyFormat(mod.price)+')<br/>';
            }
        });
        itemrow.find('.itemmodtxt').html(modtxt);
        var data = itemrow.find('.itemid').data('options');
        data.desc = $("#itemdesc").val();
        data.cost = $("#itemcost").val();
        if (moddata.items.length>0){
            data.mod = moddata;
        } else {
            delete data.mod;
        }
        itemrow.find('.itemid').data('options', data);
        WPOS.sales.updateSalesTotal();
    };

    this.searchCustomers = function(query){
        var results = [];
        var customers = WPOS.getCustTable();
        query.trim();
        query = query.toLowerCase();
        for (var key in customers){
            if (customers[key].email.toLowerCase().indexOf(query) != -1){
                results.push(customers[key]);
            }
        }
        //this.processNewEmail();
        return results;
    };

    this.loadCustomerDetails = function(custid, email){ // triggered on autocomplete click
        validateEmail(email);
        loadCustomerIfExists(custid);
    };

    function loadCustomerIfExists(custid){
        var custdata = WPOS.getCustTable()[custid];
        if (custdata!=null){
            $("#custid").val(custdata.id);
            $("#custname").val(custdata.name);
            //$("#custemail").val(custdata.email);
            $("#custphone").val(custdata.phone);
            $("#custmobile").val(custdata.mobile);
            $("#custaddress").val(custdata.address);
            $("#custsuburb").val(custdata.suburb);
            $("#custpostcode").val(custdata.postcode);
            $("#custcountry").val(custdata.country);
            return true;
        } else {
            return false;
        }
    }

    this.openCustomerDialog = function(){
        $("#custdiv").dialog("open");
    };

    this.processNewEmail = function(){ // triggered while the user is typing
        var email = $('#custemail').val();
        if (validateEmail(email)==true){
            if (loadCustomerIfExists(WPOS.getCustId(email))){
                return;
            }
        }
        // clear old forms/flags if no account loaded
        $('#custid').val(0);
        WPOS.sales.clearCustUpdate();
        $('#custform').trigger('reset');
    };

    function validateEmail(email){
        var emailreccb = $("#emailreceipt");
        if (email!=="" && email.indexOf("@")!==-1){
            emailreccb.prop('checked', true); // make this a local setting
            emailreccb.prop('disabled', false);
            return true;
        } else {
            emailreccb.prop('checked', false);
            emailreccb.prop('disabled', true);
            return false;
        }
    }
// Item UI stuff
$(function () {
    $.ui.autocomplete.prototype._renderItem = function (ul, item) {
        return $("<li>").data("ui-autocomplete-item", item).append("<a>" + (item.email!=undefined?item.email:item.name) + "</a>").appendTo(ul);
    };

    $("#itemsearch").autocomplete({
        source: function (request, response) {
            response(WPOS.items.searchItems(request.term));
        },
        search: function () {
            // custom minLength
            var term = this.value;
            return term.length >= 2;
        },
        focus : function () {
            // prevent value inserted on focus
            return false;
        },
        select: function (event, ui) {
            WPOS.items.addItemFromId(ui.item.id);
            this.value = "";
            return false;
        }
    });

    $("#custemail").autocomplete({
        source: function (request, response) {
            response(WPOS.items.searchCustomers(request.term));
        },
        search: function () {
            // custom minLength
            var term = this.value;
            return term.length >= 2;

        },
        select: function (event, ui) {
            this.value = ui.item.email;
            WPOS.items.loadCustomerDetails(ui.item.id, ui.item.email);
            return false;
        }
    });

    $("#itemoptionsdialog").removeClass('hide').dialog({
        width : 'auto',
        maxWidth        : 475,
        modal        : true,
        closeOnEscape: true,
        autoOpen     : false,
        open         : function (event, ui) {
        },
        close        : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "475px");
        },
        buttons:[
            {
                html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                "class" : "btn btn-success btn-xs",
                click: function() {
                    WPOS.items.saveItemMods();
                    $(this).dialog( "close" );
                }
            }
            ,
            {
                html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                "class" : "btn btn-xs",
                click: function() {
                    $(this).dialog( "close" );
                }
            }
        ]
    });

    $("#subtotal").text(WPOS.util.currencyFormat(0));
    $("#totaltax").text(WPOS.util.currencyFormat(0));
    $("#discounttxt").text("("+WPOS.util.currencyFormat(0)+")");
    $("#total").text(WPOS.util.currencyFormat(0));
});

function WPOSSales() {
    // Quick Items (favourites) live on WPOS.items; expose them here too for back-compat
    this.generateQuickItemsGrid = function(){ return WPOS.items && WPOS.items.generateQuickItemsGrid ? WPOS.items.generateQuickItemsGrid() : null; };
    this.toggleQuickItem = function(itemId){ return false; };
    this.showQuickItemPicker = function(){ return false; };
    var paymentMethods = ['eftpos', 'credit', 'cash', 'cheque', 'deposit'];
    var cursubtotal = 0.00;
    var curtaxtotal = 0.00;
    var curtotal = 0.00;
    var lasttransref = null;
    var curref = null; // reference for the order/sale currently processing, set when a prev order is loaded, cleared when the form is cleared

    this.getLastRef = function(){
        return lasttransref;
    };

    this.getCurrentRef = function(){
        return curref;
    };

    /**
     *
     */
    this.updateSalesTotal = function () {
        try {
        var total = 0.00;
        var tempprice = 0.00;
        curtaxtotal = 0.00; // clear last tax
        // validate records, marks valid records to be used in sale and informs user of invalid records
        // It also calculates item total and checks that its a correct result
        validateSalesItems();
        // cycle through valid records and add item total to the sales total
        var temptax;
        $("#itemtable").children('.valid').each(function (index, element) {
                // get item total
                tempprice = parseFloat($(element).find(".itemprice").val());
                if (isNaN(tempprice)) tempprice = 0;
                // add to total
                total += tempprice;
                // get tax amount included with each item
                var taxtotals = $(element).find(".itemtaxval").data('taxdata');
                if (taxtotals && !isNaN(taxtotals.total)) {
                    curtaxtotal += taxtotals.total;
                }
        });
        // remove cur tax from the total and we have our subtotal
        curtotal = total;
        cursubtotal = (total - curtaxtotal);
        if (isNaN(cursubtotal)) cursubtotal = 0;
        if (isNaN(curtaxtotal)) curtaxtotal = 0;
        if (isNaN(curtotal)) curtotal = 0;
        curgrandtotal = curtotal;
        var _st = WPOS.util.currencyFormat(cursubtotal.toFixed(2));
        var _tx = WPOS.util.currencyFormat(curtaxtotal.toFixed(2));
        $("#subtotal").text(_st);
        $("#totaltax").text(_tx);
        this.updateDiscount();
        } catch(e) {
            console.error("updateSalesTotal error:", e);
        }
    };

    /**
     *
     */
    this.updateDiscount = function () {
        try {
        if (cursubtotal === null) {
            this.updateSalesTotal();
            return;
        }
        var discountobj = $("#salediscount");
        var distxtobj = $("#discounttxt");
        var discount = discountobj.val();
        if (isNaN(parseFloat(cursubtotal))) cursubtotal = 0;
        if (isNaN(parseFloat(curtaxtotal))) curtaxtotal = 0;
        curtotal = (parseFloat(cursubtotal) + parseFloat(curtaxtotal)); // !important reset total
        if (discount === "" || discount == "0" || discount === null) {
            discountobj.val("0");
            distxtobj.text("("+WPOS.util.currencyFormat("0.00")+")");
        } else {
            var discountsum = ((discount / 100) * curtotal).toFixed(2);
            distxtobj.text("(" + WPOS.util.currencyFormat(discountsum) + ")");
            curtotal = (curtotal - discountsum);
        }
        if (isNaN(curtotal)) curtotal = 0;
        curgrandtotal = curtotal;
        $("#total").text(WPOS.util.currencyFormat(curtotal.toFixed(2)));
        } catch(e) {
            console.error("updateDiscount error:", e);
        }
    };

    /**
     *
     */
    var curgrandtotal = 0;
    var curround = 0;
    var curbalance = 0;
    this.updatePaymentSums = function () {
        var roundcents = WPOS.getConfigTable().pos.cashrounding;
        var totalpaid = 0;
        var temppay = 0;
        var temptend = 0;
        var totalchange = 0;
        var allcash = true;
        var paymentstable = $("#paymentstable");
        curgrandtotal = (isNaN(parseFloat(curtotal)) ? 0 : parseFloat(curtotal));
        // loop through payments, if cash apply rounding to payment amount and find change amount
        paymentstable.children('tr').each(function (index, element) {
            var paystr = $(element).find(".payamount").val();
            temppay = parseFloat(paystr);
            if ($(element).find(".paymethod").val()=='cash'){
                // apply cash rounding
                temppay = roundcents>0?WPOS.util.roundToNearestCents(roundcents, temppay):temppay;
                // get tender amount
                var change, tendfield = $(element).find(".paytender");
                temptend = parseFloat(tendfield.val()).toFixed(2);
                // apply cash rounding
                temptend = roundcents>0?WPOS.util.roundToNearestCents(roundcents, temptend):temptend;
                $(element).find(".paytender").val(temptend);
                //tendfield.val(temptend); causes problems with keypad
                if (temptend>temppay){
                    change = temptend - temppay;
                    totalchange+=change;
                } else {
                    change = 0.00;
                }
                $(element).find(".paychange").val(change.toFixed(2));
            } else {
                allcash = false;
            }

            if (paystr.match(new RegExp("^[0-9]+\.[0-9][0-9]$"))){
                $(element).find(".payamount").val(parseFloat(temppay).toFixed(2));
            }

            if (!isNaN(temppay)) {
                totalpaid += parseFloat(temppay);
            }
        });
        totalpaid = totalpaid.toFixed(2);
        // if all payments are cash, apply cash rounding to total else reverse any currently applied rounding
        if (roundcents==0 || allcash==false || paymentstable.children('tr').length==0){
            curgrandtotal = curtotal;
            curround = 0;
        } else {
            curgrandtotal = roundcents>0?WPOS.util.roundToNearestCents(roundcents, curtotal):curtotal;
            curround = curgrandtotal - curtotal;
        }
        // update payment sums
        var balance = -(curgrandtotal - totalpaid).toFixed(2);
        $("#salestotal").text(WPOS.util.currencyFormat(parseFloat(curgrandtotal).toFixed(2)));
        $("#paymentstotal").text(WPOS.util.currencyFormat(parseFloat(totalpaid).toFixed(2)));
        $("#salesbalance").text(WPOS.util.currencyFormat(balance));
        $("#saleschange").text(WPOS.util.currencyFormat((balance>=0?totalchange.toFixed(2):0.00)));
        salebalanced = balance == 0;
        curbalance = balance;
        //console.log("GT:"+ curgrandtotal + "\nPAID:" + totalpaid+ "\nBALANCE:"+ balance + "\nROUNDCENTS:"+ roundcents + "\nROUNDCENTS:"+ curround);
    };

    this.updatePaymentChange = function(element){
        var tender = parseFloat($(element).find(".paytender").val());
        var amount = parseFloat($(element).parent().parent().find(".payamount").val());
        var change = 0.00;
        if (tender>amount){
            change = tender - amount;
        } /*else {
            // update payment amount field to match tender
            $(element).parent().parent().find(".payamount").val(tender);
            WPOS.sales.updatePaymentSums();
        }*/
        $(element).find(".paychange").val(change.toFixed(2));
    };

    /**
     *
     */
    this.userAbortSale = function () {
        var answer = confirm("Are you sure you want to abort this order?");
        if (answer) {
            clearSalesForm();
        }
    };

    this.resetSalesForm = function(){
        clearSalesForm();
    };

    /**
     *
     */
    function clearSalesForm() {
        $('#paymentsdiv').dialog('close');
        // clear sales form
        $("#itemtable").html('');
        // add a new order row
        if (WPOS.isOrderTerminal()) {
            $('<tr class="order_row"><td style="background-color:#438EB9; color:#FFF;" colspan="7"><h4 style="text-align: center; margin: 0;">New Order</h4></td></tr>').appendTo("#itemtable");
            $("#tablenumber").val(0).prop('readonly', true);
            $("#radio_takeaway").prop('checked', true);
        }
        $("#paymentstable").html('');
        $(".payamount").val(WPOS.util.currencyFormat(0));
        $("#salenotes").text("").val('');
        $("#salediscount").val(0);
        $("#discounttxt").text("("+WPOS.util.currencyFormat(0)+")");
        $("#totaltax").text(WPOS.util.currencyFormat(0));
        $("#subtotal").text(WPOS.util.currencyFormat(0));
        $("#total").text(WPOS.util.currencyFormat(0));
        $("#custemail").val("");
        $("#custid").val(0);
        var emailreccb = $("#emailreceipt");
        emailreccb.prop("checked", false);
        emailreccb.prop("disabled", true);
        $("#custform").trigger('reset');
        WPOS.sales.clearCustUpdate();
        // zero current totals
        cursubtotal = 0.00;
        curtotal = 0.00;
        curround = 0.00;
        curgrandtotal = 0.00;
        curref = null;
        // remove error notice
        $("#invaliditemnotice").hide();
    }

    function getNumSalesItems(){
        return $("#itemtable").children(".valid").length;
    }

    function validateSalesItems(){
        try {
        var qty,name, unit, mod, tempprice, tempcost;
        var numinvalid = 0;
        var allow_negative = false;
        try { allow_negative = WPOS.getConfigTable().pos.negative_items; } catch(e) {}
        $("#itemtable").children(".item_row").each(function (index, element) {
                try {
                qty = parseFloat($(element).find(".itemqty").val());
                name = $(element).find(".itemname").val();
                unit = parseFloat($(element).find(".itemunit").val());
                if (isNaN(qty)) qty = 1;
                if (isNaN(unit)) unit = 0;
                var itemdata = $(element).find(".itemid").data('options');
                if (!itemdata || typeof itemdata !== 'object') {
                    try { 
                        var dataAttr = $(element).find(".itemid").attr('data-options');
                        if (dataAttr) itemdata = JSON.parse(dataAttr); 
                    } catch(e) { itemdata = {cost:0, mod:{total:0}}; }
                }
                if (!itemdata) itemdata = {cost:0, mod:{total:0}};
                if (!itemdata.hasOwnProperty('cost')) itemdata.cost = 0;
                mod = 0;
                if (itemdata.hasOwnProperty('mod') && itemdata.mod) {
                    if (typeof itemdata.mod.total === 'number') mod = itemdata.mod.total;
                    else if (typeof itemdata.mod === 'number') mod = itemdata.mod;
                }
                tempprice = parseFloat("0.00");
                if (qty > 0 && name && name != "" && (unit >= 0 || allow_negative)) {
                    // add item modification total to unit price & calculate item total
                    tempprice = qty * (unit + mod);
                    tempcost = qty * (parseFloat(itemdata.cost) || 0);
                    // calculate item tax
                    var taxruleid = $(element).find(".itemtax").val();
                    if (!taxruleid) taxruleid = 1;
                    var taxdata = {inclusive:true, total:0};
                    try {
                        if (typeof WPOS.util.calcTax === 'function') {
                            taxdata = WPOS.util.calcTax(taxruleid, tempprice, tempcost);
                            if (!taxdata) taxdata = {inclusive:true, total:0};
                        }
                    } catch(e) {
                        taxdata = {inclusive:true, total:0};
                    }
                    if (taxdata && taxdata.inclusive === false) {
                        tempprice += (parseFloat(taxdata.total) || 0);
                    }
                    $(element).find(".itemtaxval").data('taxdata', taxdata);
                    $(element).find(".itemprice").val(tempprice.toFixed(2));
                    // valid item; mark as valid, remove ui indicator class
                    $(element).addClass("valid");
                    $(element).removeClass("danger");
                } else {
                    // not a valid record
                    $(element).removeClass("valid");
                    $(element).addClass("danger");
                    // something is null, set price to 0
                    $(element).find(".itemprice").val("0.00");
                    // increment number invalid
                    numinvalid++;
                }
                } catch(inner) {
                    // be permissive - try to mark as valid if we can
                    try {
                        $(element).addClass("valid");
                        $(element).removeClass("danger");
                        var up = parseFloat($(element).find(".itemunit").val()) || 0;
                        var uq = parseFloat($(element).find(".itemqty").val()) || 1;
                        $(element).find(".itemprice").val((up*uq).toFixed(2));
                    } catch(e2) {}
                }
        });
        // show warning if items invalid
        try {
        if (numinvalid>0){
            $("#invaliditemnotice").show();
        } else {
            $("#invaliditemnotice").hide();
        }
        } catch(e) {}
        } catch(e) { 
            console.error("validateSalesItems error:", e); 
            // Last resort: mark all item_row as valid
            try {
                $("#itemtable").children(".item_row").addClass("valid").removeClass("danger");
            } catch(e2) {}
        }
    }
    }

    this.showPaymentDialog = function () {
        try {
            if (typeof this.updateSalesTotal === 'function') this.updateSalesTotal();
            if (typeof this.updateDiscount === 'function') this.updateDiscount();
            if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
        } catch(e) {}
        try { $("#itemtable").children('.item_row').addClass('valid'); } catch(e) {}
        if (getNumSalesItems && getNumSalesItems() > 0 || $("#itemtable").children('.item_row').length > 0) {
            // Show integrated eftpos button if enabled
            var inteftbtn = $("#eftpospaybtn");
            try {
                if (WPOS.hasOwnProperty('eftpos') && WPOS.eftpos.isEnabledAndReady()){
                    inteftbtn.show();
                    inteftbtn.text(WPOS.util.capFirstLetter(WPOS.eftpos.getType())+' Eftpos');
                } else {
                    inteftbtn.hide();
                }
            } catch(e) {}
            // Default to cash so the operator can collect change from the total while still keeping the amount editable.
            try {
                if ($("#paymentstable").children('tr').length == 0) {
                    var amt = (typeof curgrandtotal !== 'undefined' && curgrandtotal > 0) ? curgrandtotal : ((typeof curtotal !== 'undefined' && curtotal > 0) ? curtotal : ((typeof cursubtotal !== 'undefined' && cursubtotal > 0) ? cursubtotal : 0));
                    if (!amt || isNaN(amt)) amt = 0;
                    var amountValue = amt.toFixed ? amt.toFixed(2) : amt;
                    if (typeof addPaymentRow === 'function') {
                        addPaymentRow('cash', amountValue, amountValue, 0.00);
                    }
                    if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
                }
            } catch(e){}
            try {
                if ($("#paymentsdiv").hasClass('ui-dialog-content')) {
                    $("#paymentsdiv").dialog('open');
                } else {
                    $("#paymentsdiv").dialog({ width: 'auto', modal: true, resizable: false });
                    $("#paymentsdiv").dialog('open');
                }
            } catch(e) {
                try { $("#paymentsdiv").removeClass('hide').show(); } catch(e2) {}
            }
            try { $("#endsalebtn").prop("disabled", false); } catch(e) {}
        } else {
            alert("Please add some valid items to the sale before proceeding!");
        }
    };

    this.addAdditionalPayment = function(){
        addPaymentRow('cash', 0, 0, 0);
    };

    this.removePayment = function(pitem){
        $(pitem).parent('td').parent('tr').remove();
        WPOS.sales.updatePaymentSums();
    };

    this.addPayment = function(method){
        if ($("#paymentstable").children('tr').length>0){
            addPaymentRow(method, (-curbalance+(method=="cash"?0:-curround)).toFixed(2), null, null); // don't add sale total
        } else {
            addPaymentRow(method, curgrandtotal.toFixed(2), curgrandtotal.toFixed(2), null);
        }
        WPOS.sales.updatePaymentSums();
    };

    this.addPaymentRowWithData = function(method, value, extraData){
        addPaymentRow(method, value, value, 0, extraData);
        WPOS.sales.updatePaymentSums();
    };

    this.startEftposPayment = function(){
        // find out if cashout supported by provider
        if (WPOS.eftpos.isCashoutSupported){
            showCashoutDialog();
        } else {
            initEftposPayment(0);
        }
    };

    var codialoginit = false;
    function showCashoutDialog(){
        var codialog = $("#codialog");
        if (!codialoginit){
            codialoginit = true;
            codialog.removeClass('hide').dialog({
                maxWidth : 200,
                width : 'auto',
                modal   : true,
                autoOpen: false,
                appendTo: "#paymentsdiv",
                buttons: [
                    {
                        html: "<i class='icon-check bigger-110'></i>&nbsp; Ok",
                        "class": "btn btn-success btn-xs",
                        click: function () {
                            $(".keypad-popup").hide();
                            var cashout =  parseFloat($("#cashoutamount").val()).toFixed(2);
                            if (cashout<0){
                                alert("Cashout value must be positive or 0");
                                return;
                            }
                            codialog.dialog('close');
                            initEftposPayment(cashout);
                        }
                    },
                    {
                        html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                        "class": "btn btn-xs",
                        click: function () {
                            $(".keypad-popup").hide();
                            codialog.dialog('close');
                        }
                    }
                ],
                create: function( event, ui ) {
                }
            });
        }
        $("#cashoutamount").val(0);
        codialog.dialog('open');
    }

    function initEftposPayment(cashout){
        if ($("#paymentstable").children('tr').length>0){
            WPOS.eftpos.startEftposPayment((-curbalance+-curround).toFixed(2), cashout); // use current balance, remove rounding
        } else {
            WPOS.eftpos.startEftposPayment(curgrandtotal.toFixed(2), cashout);
        }
    }

    this.onPaymentMethodChange = function(elem){
        if($(elem).find(':selected').val()=='cash'){
            $(elem).parent().find('.cashvals').show();
        }else{
            $(elem).parent().find('.cashvals').hide();
        }
        WPOS.sales.updatePaymentSums();
    };

    function addPaymentRow(method, value, tender, change, extraData){
        var exmethod = '';
        if ($.inArray(method, paymentMethods)==-1)
            exmethod = '<option value="'+method+'" selected>'+method+'</option>';
        var data = '';
        if (extraData)
            data = "data-paydata='"+JSON.stringify(extraData)+"'";

        var amount = parseFloat(value || 0) || 0;
        var tenderValue = (typeof tender !== 'undefined' && tender !== null && tender !== '') ? parseFloat(tender) : amount;
        if (method !== 'cash') {
            tenderValue = 0.00;
        }
        if (method === 'cash' && (typeof change === 'undefined' || change === null || change === '')) {
            change = Math.max(0, tenderValue - amount);
        }

        var curBefore = "", curAfter = "";
        if (WPOS.util.getCurrencyPlacedAfter()){
            curAfter = WPOS.util.getCurrencySymbol();
        } else {
            curBefore = WPOS.util.getCurrencySymbol();
        }

        var payrow =  '<tr '+data+'><td>' +
            '<select class="paymethod" onchange="WPOS.sales.onPaymentMethodChange(this);">' +
            '<option value="eftpos" '+(method=='eftpos'?'selected':'')+'>Eftpos</option>' +
            '<option value="credit" '+(method=='credit'?'selected':'')+'>Credit</option>' +
            '<option value="cash" '+(method=='cash'?'selected':'')+'>Cash</option>' +
            '<option value="cheque" '+(method=='cheque'?'selected':'')+'>Cheque</option>' +
            '<option value="deposit" '+(method=='deposit'?'selected':'')+'>Deposit</option>' +
            exmethod+ '</select>' +
            '<div class="cashvals" '+(method!='cash'?'style="display: none"':'width:150px;')+'>' +
            '<div style="width: 100px; display: inline-block;">Tendered:</div><input onChange="WPOS.sales.updatePaymentChange($(this).parent());" class="paytender numpad" style="width:50px;" type="text" value="'+(method!='cash'?0.00:(tenderValue.toFixed ? tenderValue.toFixed(2) : tenderValue))+'" />' +
            '<div style="width: 100px; display: inline-block;">Change:</div><input class="paychange" style="width:50px;" type="text" value="'+(method!='cash'?0.00:(change!=null?parseFloat(change).toFixed(2):0.00))+'" readonly />' +
            '</div></td>' +
            '<td>'+curBefore+'<input onChange="WPOS.sales.updatePaymentSums();" class="payamount numpad" style="width:50px;" type="text" value="'+(amount.toFixed ? amount.toFixed(2) : amount)+'" autocomplete="off"/>'+curAfter+'</td>' +
            '<td><button class="btn btn-xs btn-danger" onclick="WPOS.sales.removePayment($(this));">X</button></td></tr>';

        $("#paymentstable").append(payrow);

        // reinitialize keypad & field listeners
        WPOS.initKeypad();
    }

    // FUNCTIONS BEFORE ARE FOR PROCESSING ORDERS
    this.saveOrder = function(){
        processOrder();
    };

    function processOrder(){
        var salesobj = getSaleObject();
        var sales_json = JSON.stringify(salesobj);
        if (sales_json.length > 16384) return alert('Too Many Items'); // depends on database field size for sales.data
        if (curref!=null){
            salesobj.ref = curref;
            var cursale = WPOS.trans.getTransactionRecord(curref);
            if (cursale.hasOwnProperty('id')){
                salesobj.id = cursale.id; // make sure we add the id
            }
            removeSalesRecord(curref);
        }
        lasttransref = salesobj.ref;
        salesobj.isorder = true;
        // add to offline table temporarily
        addOfflineSale(salesobj, "orders/set");
        if (WPOS.isOnline()) {
            WPOS.setStatusBar(2, "Uploading Record...");
            WPOS.sendJsonDataAsync("orders/set", JSON.stringify(salesobj), function(data){ WPOS.sales.postSaleUpload(data, salesobj.ref); });
        } else {
            // update status
            WPOS.setStatusBar(3, "WPOS is offline ("+WPOS.sales.getOfflineSalesNum()+" offline records)");
        }
        // close the payment dialog and clear form
        clearSalesForm();
        $("#paymentsdiv").dialog("close");
        // process the orders
        WPOS.orders.processOrder(salesobj, cursale);
    }

    this.loadOrder = function(ref){
        loadOrder(ref);
    };

    this.removeOrder = function(ref){
        var answer = confirm("Are you sure you want to delete this order?");
        if (answer){
            WPOS.util.showLoader();
            WPOS.sendJsonDataAsync("orders/remove", JSON.stringify({ref: ref}), function(result){

                if (result){
                    var cursale = WPOS.trans.getTransactionRecord(ref);
                    removeSalesRecord(ref);
                    // if the order is loaded we need to clear the sales form
                    if (ref==curref)
                        clearSalesForm();
                    // process the orders
                    WPOS.orders.processOrder(ref, cursale);
                } else {
                    alert("Could not delete the order!");
                }
                WPOS.util.hideLoader();
                WPOS.trans.showTransactionView();
            });
        }
    };

    function loadOrder(ref){
        // get the existing sales object.
        var salesobj = WPOS.trans.getTransactionRecord(ref);
        if (salesobj!=false){
            clearSalesForm();
            $("#itemtable").html('');
            // if order data exists,
            for (var id in salesobj.orderdata){
                var orderdata = salesobj.orderdata[id];
                $('<tr id="order_row_'+id+'" class="order_row" data-data=\''+JSON.stringify(orderdata)+'\'><td style="background-color:#438EB9; color:#FFF;" colspan="7"><h4 style="text-align: center; margin: 0;">Order #'+orderdata.id+'</h4></td></tr>')
                    .appendTo("#itemtable");
            }
            // load items into the table
            var item;
            for (var i in salesobj.items){
                item = salesobj.items[i];
                var data = {
                    desc:   item.desc,
                    ref:    item.ref,
                    orderid:item.orderid
                };
                if (item.hasOwnProperty('mod')) data.mod = item.mod;
                WPOS.items.addItemRow(item.qty, item.name, item.unit, item.taxid, item.sitemid, data);
            }
            // add a new order row
            if (WPOS.isOrderTerminal())
                $('<tr class="order_row"><td style="background-color:#438EB9; color:#FFF;" colspan="7"><h4 style="text-align: center; margin: 0;">New Order</h4></td></tr>').appendTo("#itemtable");
            // load sale data
            $("#salediscount").val(salesobj.discount);
            $("#salenotes").val(salesobj.salenotes);
            $("#custemail").val(salesobj.custemail);
            $("#custid").val(salesobj.custid);
            if (salesobj.hasOwnProperty("custdata") && typeof salesobj.custdata =="object"){
                var custdata = salesobj.custdata;
                $("#custname").val(custdata.name);
                $("#custphone").val(custdata.phone);
                $("#custmobile").val(custdata.mobile);
                $("#custaddress").val(custdata.address);
                $("#custsuburb").val(custdata.suburb);
                $("#custpostcode").val(custdata.postcode);
                $("#custcountry").val(custdata.country);
                WPOS.sales.setUpdateCust();
            }
            var payment;
            for (i in salesobj.payments){
                payment = salesobj.payments[i];
                var tender, change;
                if (payment.method == "cash"){
                    tender = payment.tender;
                    change = payment.change;
                } else {
                    tender = 0;
                    change = 0
                }
                addPaymentRow(payment.method, payment.amount, tender, change);
            }
            // set the current transaction reference and close dialog
            curref = salesobj.ref;
            WPOS.sales.updateSalesTotal();
            WPOS.sales.updatePaymentSums();
            $("#transactiondiv").dialog('close');
            $("#wrapper").tabs("option", "active", 0);
        } else {
            alert("Could not find the current record.");
        }
    }

    // FUNCTIONS BELOW ARE FOR PROCESSING THE SALE
    var printReceiptAfterSale = false;
    this.processSale = function (printReceipt) {
        printReceiptAfterSale = !!printReceipt;
        var salebtn = $("#endsalebtn");
        try { salebtn.prop("disabled", true); } catch(e) {}
        try {
            if (typeof this.updateSalesTotal === 'function') this.updateSalesTotal();
            if (typeof this.updateDiscount === 'function') this.updateDiscount();
            if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
        } catch(e) {}
        try { $("#itemtable").children('.item_row').addClass('valid'); } catch(e) {}
        if ($("#paymentstable").children('tr').length == 0) {
            try {
                if (typeof addPaymentRow === 'function') {
                    var amt = (typeof curgrandtotal !== 'undefined' && curgrandtotal > 0) ? curgrandtotal : ((typeof curtotal !== 'undefined' && curtotal > 0) ? curtotal : ((typeof cursubtotal !== 'undefined' && cursubtotal > 0) ? cursubtotal : 0));
                    if (!amt || isNaN(amt)) amt = 0;
                    var amts = amt.toFixed ? amt.toFixed(2) : amt;
                    addPaymentRow('cash', amts, amts, 0.00);
                    if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
                }
            } catch(e){ console.error('payerr:', e); }
        }
        try { salebalanced = true; } catch(e) {}
        try {
            ProcessSaleTransaction();
        } catch(e) { console.error(e); }
        try { salebtn.prop("disabled", false); } catch(e) {}
    };
    this.quickProcess = function() {
        try {
            if (typeof this.updateSalesTotal === 'function') this.updateSalesTotal();
            if (typeof this.updateDiscount === 'function') this.updateDiscount();
        } catch(e) {}
        try { $("#itemtable").children('.item_row').addClass('valid'); } catch(e) {}
        try {
            if ($("#paymentstable").children('tr').length == 0) {
                var amt = 0;
                try {
                    if (typeof curgrandtotal !== 'undefined' && curgrandtotal > 0) amt = curgrandtotal;
                    else if (typeof curtotal !== 'undefined' && curtotal > 0) amt = curtotal;
                    else if (typeof cursubtotal !== 'undefined' && cursubtotal > 0) amt = cursubtotal;
                } catch(e) {}
                if (!amt || isNaN(amt)) amt = 0;
                var amts = amt.toFixed ? amt.toFixed(2) : amt;
                try {
                    if (typeof addPaymentRow === 'function') {
                        addPaymentRow('cash', amts, amts, 0.00);
                    }
                } catch(e) {}
            }
            if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
            if (typeof this.processSale === 'function') {
                return this.processSale();
            }
        } catch(e) { console.error(e); }
        return false;
    };
    this.directProcess = function() {
        try {
            if (typeof this.updateSalesTotal === 'function') this.updateSalesTotal();
            if (typeof this.updateDiscount === 'function') this.updateDiscount();
            if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
        } catch(e) {}
        try { $("#itemtable").children('.item_row').addClass('valid'); } catch(e) {}
        try {
            if ($("#paymentstable").children('tr').length == 0) {
                var amt = 0;
                try {
                    if (typeof curgrandtotal !== 'undefined' && curgrandtotal > 0) amt = curgrandtotal;
                    else if (typeof curtotal !== 'undefined' && curtotal > 0) amt = curtotal;
                    else if (typeof cursubtotal !== 'undefined' && cursubtotal > 0) amt = cursubtotal;
                } catch(e) {}
                if (!amt || isNaN(amt)) amt = 0;
                var amts = amt.toFixed ? amt.toFixed(2) : amt;
                if (typeof addPaymentRow === 'function') addPaymentRow('cash', amts, amts, 0.00);
                if (typeof this.updatePaymentSums === 'function') this.updatePaymentSums();
            }
        } catch(e) {}
        try { salebalanced = true; } catch(e) {}
        try {
            var salesobj = null;
            try { salesobj = getSaleObject(); } catch(e) { }
            if (salesobj) {
                try {
                    if (salesobj.items) {
                        for (var i=0; i<salesobj.items.length; i++) {
                            if (!salesobj.items[i].desc) salesobj.items[i].desc = salesobj.items[i].name || '';
                        }
                    }
                    if (!salesobj.userid && typeof WPOS.getCurrentUserId === 'function') salesobj.userid = WPOS.getCurrentUserId();
                    if (typeof WPOS.getConfigTable === 'function') {
                        var cfg = WPOS.getConfigTable();
                        if (!salesobj.devid && cfg.deviceid) salesobj.devid = cfg.deviceid;
                        if (!salesobj.locid && cfg.locationid) salesobj.locid = cfg.locationid;
                    }
                    if (typeof WPOS.sendJsonDataAsync === 'function') {
                        WPOS.sendJsonDataAsync("sales/add", JSON.stringify(salesobj), function(data){ 
                            try { 
                                if (data && WPOS.sales && WPOS.sales.postSaleUpload) {
                                    WPOS.sales.postSaleUpload(data, salesobj.ref); 
                                } else if (data === false) {
                                    // try offline
                                }
                            } catch(e){} 
                        });
                        try { if (typeof clearSalesForm === 'function') clearSalesForm(); } catch(e) {}
                        try { $("#paymentsdiv").dialog('close'); } catch(e) { try { $("#paymentsdiv").hide(); } catch(e2){} }
                        return true;
                    }
                } catch(e) { }
            }
            try { ProcessSaleTransaction(); } catch(e2) {}
            return true;
        } catch(e) { }
        return false;
    };


    var salebalanced = false;

    function isSaleBalanced(){
        return salebalanced;
    }

    function validatePayments(){
        var valid = true;
        $("#paymentstable").children("tr").each(function (index, element) {
            // Make sure payments are positive amounts, except cashout
            if (parseFloat($(element).find(".payamount").val())<0){
                if ($(element).find(".payamount").val()=='cash' && !$(element).data('paydata').hasOwnProperty('cashOut'))
                    valid = false;
            }
        });
        return valid;
    }

    function ProcessSaleTransaction(){
        try { if (typeof WPOS.sales && WPOS.sales.updateSalesTotal) WPOS.sales.updateSalesTotal(); } catch(e){}
        try { if (typeof WPOS.sales && WPOS.sales.updatePaymentSums) WPOS.sales.updatePaymentSums(); } catch(e){}
        if ($("#paymentstable").children('tr').length == 0) {
            try {
                var amt = 0;
                try {
                    if (typeof curgrandtotal !== 'undefined' && curgrandtotal > 0) amt = curgrandtotal;
                    else if (typeof curtotal !== 'undefined' && curtotal > 0) amt = curtotal;
                    else if (typeof cursubtotal !== 'undefined' && cursubtotal > 0) amt = cursubtotal;
                } catch(e){}
                if (!amt || isNaN(amt)) amt = 0;
                var amts = amt.toFixed ? amt.toFixed(2) : amt;
                if (typeof addPaymentRow === 'function') addPaymentRow('mpesa', amts, amts, null);
                if (typeof WPOS.sales && WPOS.sales.updatePaymentSums) WPOS.sales.updatePaymentSums();
            } catch(e){}
        }
        var salesobj = null;
        try { salesobj = getSaleObject(); } catch(e) { console.error(e); }
        if (!salesobj) return;
        var sales_json = JSON.stringify(salesobj);
        if (sales_json.length > 16384) return alert('Too Many Items');

        var cursale = null;
        if (curref!==null){
            try {
            cursale = WPOS.trans.getTransactionRecord(curref);
            salesobj.ref = curref;
            try { delete salesobj.isorder; } catch(e){}
            try { removeSalesRecord(curref); } catch(e){}
            } catch(e){}
        }
        try { addOfflineSale(salesobj, "sales/add"); } catch(e){}
        try { lasttransref = salesobj.ref; } catch(e){}

        // Force online check
        try { if (typeof WPOS.checkOnlineStatus === 'function') WPOS.checkOnlineStatus(); } catch(e){}

        if (typeof WPOS.isOnline === 'function' && WPOS.isOnline()) {
            try { WPOS.setStatusBar(2, "Uploading Record..."); } catch(e){}
            try {
                WPOS.sendJsonDataAsync("sales/add", JSON.stringify(salesobj), function(data){
                    try {
                        if (data && WPOS.sales && WPOS.sales.postSaleUpload) WPOS.sales.postSaleUpload(data, salesobj.ref);
                    } catch(e){}
                });
            } catch(e) { console.error(e); }
        } else {
            // Try to switch online and retry
            try { if (typeof WPOS.switchToOnline === 'function') WPOS.switchToOnline(); } catch(e){}
            try { WPOS.sendJsonDataAsync("sales/add", JSON.stringify(salesobj), function(data){
                try {
                    if (data && WPOS.sales && WPOS.sales.postSaleUpload) WPOS.sales.postSaleUpload(data, salesobj.ref);
                } catch(e){}
            }); } catch(e) { console.error(e); }
        }
        var recemailed = false;
        try { recemailed = $("#emailreceipt").is(":checked"); } catch(e) {}
        try { clearSalesForm(); } catch(e) {}
        try { $("#paymentsdiv").dialog("close"); } catch(e) { try { $("#paymentsdiv").hide(); } catch(e2){} }
        try {
            for (var i in salesobj.payments){
                if (salesobj.payments[i].method == "cash"){
                    if (WPOS.print && WPOS.print.openCashDraw) WPOS.print.openCashDraw(true);
                    break;
                }
            }
        } catch(e) {}
        try { if (WPOS.orders && WPOS.orders.processOrder) WPOS.orders.processOrder(salesobj, cursale); } catch(e) {}
        try {
            if (printReceiptAfterSale) {
                if (WPOS.print && WPOS.print.printReceipt) WPOS.print.printReceipt(salesobj.ref);
                printReceiptAfterSale = false;
            } else {
                var psetting = WPOS.print && WPOS.print.getGlobalPrintSetting ? WPOS.print.getGlobalPrintSetting('recask') : null;
                if (psetting == "print"){
                    if (WPOS.print && WPOS.print.printReceipt) WPOS.print.printReceipt(salesobj.ref);
                } else {
                    if (psetting == "email" && recemailed){
                        return;
                    }
                    var answer = confirm("Would you like to print a receipt?");
                    if (answer){
                        if (WPOS.print && WPOS.print.printReceipt) WPOS.print.printReceipt(salesobj.ref);
                    }
                }
            }
        } catch(e) {}
    }

    function getNewOrderObject(date){
        return {
            id:         WPOS.util.getSequencialOrderNumber(),
            items:      {},
            processdt:  date,
            received:   false,
            tablenum:   $("#tablenumber").val()
        };
    }

    function getCustomerObject(){
        var custdata = {};

        custdata.id = $('#custid').val();
        custdata.name = $('#custname').val();
        custdata.email = $('#custemail').val();
        custdata.phone = $('#custphone').val();
        custdata.mobile = $('#custmobile').val();
        custdata.address = $('#custaddress').val();
        custdata.suburb = $('#custsuburb').val();
        custdata.postcode = $('#custpostcode').val();
        custdata.country = $('#custcountry').val();

        return custdata;
    }

    var updatecust = false; // flag indicating customer details need updating/adding
    this.setUpdateCust = function(){ // used to indicate new customer data
        updatecust = true;
    };
    this.clearCustUpdate = function(){ // used when the customer email field is changed
        updatecust = false;
    };

    this.openRefundDialog = function(ref){
        $("#voidform").hide();
        $("#refundform").show();
        $("#refundref").val(ref);
        var refamtinput = $("#refundamount");
        refamtinput.val(0);
        refamtinput.removeData('paydata'); // remove extra payment data
        var formdiv = $("#formdiv");
        formdiv.dialog("option", "title", "Refund transaction");
        var voidbtn = $("#procvoidbtn");
        voidbtn.attr("onclick", 'WPOS.sales.processRefund();');
        voidbtn.prop('disabled', false);
        // populate items
        var sale = WPOS.trans.getTransactionRecord(ref);
        var items = sale.items;
        var refitems = $("#refunditems");
        refitems.html('');
        var itemid;
        for (var i = 0; i<items.length; i++){
            // calc how many current items have already been refunded
            itemid = items[i].id;
            var refnum = 0;
            // loop though each refund
            for (var key in sale.refunddata){
                var ritems = sale.refunddata[key].items;
                // loop through items of the refund and add to total if the id equals
                for (var key1 in ritems){
                    if(!ritems[key1].id && ritems[key1].ref){
                        refnum += (ritems[key1].ref == items[i].ref?(parseInt(ritems[key1].numreturned) || 1):0);
                        continue;
                    }
                    refnum += (ritems[key1].id == itemid?(parseInt(ritems[key1].numreturned) || 1):0);
                }
            }
            refitems.append('<tr>' +
                '<td><input size="4" class="refundqty" type="number" value="0" onchange="WPOS.sales.validateRefund();" autocomplete="off"/>' +
                '<input class="refunditemref" type="hidden" value="'+(items[i].hasOwnProperty('ref')?items[i].ref:0)+'"/>' +
                '<input class="refunditemid" type="hidden" value="'+(items[i].hasOwnProperty('id')?items[i].id:0)+'"/>' + // temp fix for old db records not containing item ref
                '<input class="refundsqty" type="hidden" value="'+(parseInt(items[i].qty)-refnum)+'"/>' +
                '<input class="refundsunit" type="hidden" value="'+(items[i].price/items[i].qty)+'"/></td>' +
                '<td>'+items[i].qty+' x '+items[i].name+' ($'+items[i].price+')</td>' +
            '</tr>');
        }
        var eftbtn = $("#eftposrefundbtn");
        if (WPOS.hasOwnProperty('eftpos') && WPOS.eftpos.isEnabledAndReady()){
            eftbtn.show();
        } else {
            eftbtn.hide();
        }
        formdiv.dialog('open');
    };

    this.addRefundPaymentData = function(method, amount, data){
        var refamount = $("#refundamount");
        refamount.data('paydata', data);
        refamount.val(amount);
        var refmethodsel = $("#refundmethod");
        refmethodsel.append('<option value="'+method+'">'+method+'</option>');
        refmethodsel.val(method);
    };

    this.validateRefund = function(){
        var refundamt = 0;
        $("#refunditems").children("tr").each(function(index, item){
            var refundqty = parseInt($(item).find('.refundqty').val());
            var netqty = parseInt($(item).find('.refundsqty').val());
            // check if the amount is larger than bought qty
            if (refundqty>netqty){
                alert("Cannot return more items than sold + returned!");
                $(item).find('.refundqty').val(netqty);
                return false;
            }
            if (refundqty>0){
                var boughtunit = $(item).find('.refundsunit').val();
                // calculate refund amount and add to field
                refundamt += parseFloat(boughtunit) * parseFloat(refundqty);
            }
            return true;
        });
        $("#refundamount").val(refundamt.toFixed(2));
        return true;
    };

    this.openVoidDialog = function(ref){
        $("#refundform").hide();
        $("#voidform").show();
        $("#voidref").val(ref);
        var formdiv = $("#formdiv");
        var voidbtn = $("#procvoidbtn");
        formdiv.dialog("option", "title", "Void transaction");
        voidbtn.attr("onclick", 'WPOS.sales.processVoid();');
        voidbtn.prop('disabled', false);
        formdiv.dialog('open');
    };

    this.processVoid = function(){
        // do not allow for sales with integrated eftpos transactions
        var ref = $("#voidref").val();
        var trans = WPOS.trans.getTransactionRecord(ref);
        for (var i in trans.payments){
            if (trans.payments[i].method=="tyro"){
                alert("Sales with Eftpos transactions cannot be voided. Refund this transaction instead.");
                return;
            }
        }

        if ($("#voidreason").val()==""){
            alert("Reason must not be blank.");
            return;
        }
        var answer = confirm("Are you sure you want to void this transaction?");
        if (answer){
            $("#procvoidbtn").prop('disabled', true);
            processVoidTransaction(ref, false);
            $("#formdiv").dialog('close');
            lasttransref = ref;
            // update transaction info
            WPOS.trans.populateTransactionInfo(ref);
        }
    };

    this.processRefund = function(){
        if ($("#refundreason").val()==""){
            alert("Reason must not be blank.");
            return;
        }
        if ($("#refundamount").val()<=0){
            alert("Amount must be larger than 0.");
            return;
        }
        var ref = $("#refundref").val();
        var answer = confirm("Are you sure you want to refund this transaction?");
        if (answer){
            $("#procvoidbtn").prop('disabled', true);
            processVoidTransaction(ref, true);
            $("#formdiv").dialog('close');
            lasttransref = ref;
            // update transaction info
            WPOS.trans.populateTransactionInfo(ref);
        }
    };

    this.eftposRefund = function(){
        var ref = $("#refundref").val();
        $("#formdiv").dialog('close');
        processVoidTransaction(ref, true);
        lasttransref = ref;
        // update transaction info
        WPOS.trans.populateTransactionInfo(ref);
    };

    function processVoidTransaction(ref, isrefund){
        var refundobj;
        // get current sale object
        if (isSaleOffline(ref)){
            refundobj = getVoidObject(ref, isrefund);
            // update the record in the offline table
            addUpdatedOfflineRecord(false, refundobj, "sales/void");
            return true;
        } else {
            refundobj =  getVoidObject(ref, isrefund);
        }
        if (WPOS.isOnline()){
            WPOS.setStatusBar(2, "Uploading Record...");
            WPOS.sendJsonDataAsync("sales/void", JSON.stringify(refundobj), function(data){ WPOS.sales.postVoidUpload(data); });
        } else {
            addUpdatedOfflineRecord(true, refundobj, "sales/void"); // update records
            // update status
            WPOS.setStatusBar(3, "WPOS is offline ("+WPOS.sales.getOfflineSalesNum()+" offline records)");
        }
        if (isrefund){
            // open the draw if a cash payment
            if (refundobj.refunddata[refundobj.refunddata.length-1].method == "cash"){
                WPOS.print.openCashDraw(true); // opens cash draw if configured
            }
            // print receipt or prompt
            if (WPOS.getLocalConfig().recask == "print"){
                WPOS.print.printReceipt(refundobj.ref);
            } else {
                var answer = confirm("Would you like to print a receipt?");
                if (answer){
                    WPOS.print.printReceipt(refundobj.ref);
                }
            }
        }
        return true;
    }

    this.postVoidUpload = function(jsonresponse, refundobject){
        if (jsonresponse !== false) {
            // SUCCESS
            // add json response to sales records
            addSalesRecord(jsonresponse);
            // reset status Icon
            WPOS.setStatusBar(1, "WPOS is Online");
        } else {
            // ERROR
            if (WPOS.switchToOffline()) { // do not store record if offline mode is not supported.
                addUpdatedOfflineRecord(true, refundobject, "sales/void"); // update records
                // update status
                WPOS.setStatusBar(3, "WPOS is offline ("+WPOS.sales.getOfflineSalesNum()+" offline records)");
            }
        }
    };

    function getVoidObject(ref, refund){
        var refundobj;
        var date = new Date().getTime();
        var config = WPOS.getConfigTable();
        // get data from the trans object, it holds offline + remotely loaded transactions
        refundobj = WPOS.trans.getTransactionRecord(ref);
        // add refund/void shared data
        var shareddata = {"userid": WPOS.getCurrentUserId(), "deviceid": config.deviceid, "locationid": config.locationid, "processdt": date};
        // add specific data
        if (refund){
            // if refund data is not defined, create an array
            if (refundobj.refunddata == null){
                refundobj.refunddata = [];
            }
            // add refund specific values to shared data
            shareddata.reason = $("#refundreason").val();
            var items = [];
            // get returned items
            var numreturned;
            $("#refunditems").children("tr").each(function(index, item){
                numreturned = $(item).find('.refundqty').val();
                if (numreturned>0){
                    var ref = $(item).find('.refunditemref').val();
                    if (ref!=0){
                        items.push({"ref": ref, "numreturned": numreturned});
                    } else {
                        items.push({"ref": $(item).find('.refunditemid').val(), "numreturned": numreturned});
                    }
                }
            });
            var refamtinput = $("#refundamount");
            shareddata.items = items;
            shareddata.method = $("#refundmethod").val();
            shareddata.amount = parseFloat(refamtinput.val()).toFixed(2);
            if (refamtinput.data('paydata'))
                shareddata.paydata = refamtinput.data('paydata');
            // add to refund array
            refundobj.refunddata.push(shareddata);
        } else {
            refundobj.voiddata = shareddata;
            refundobj.voiddata.reason = $("#voidreason").val();
        }

        return refundobj;
    }

    function addUpdatedOfflineRecord(removefromsales, jsondata, action){
        // add record to offline table and remove from the sales table
        if (addOfflineSale(jsondata, action)){
            if (removefromsales) removeSalesRecord(jsondata.ref);
        } else {
            alert("Failed to update the record in offline storage, the sale has not been updated.");
        }
    }

    // THE FOLLOWING FUNCTIONS ARE ALL RELATED TO SALES TABLES IN LOCAL STORAGE.
    /**
     *
     * @param {Object} jsonobj
     * @returns {number|*}
     */
    function addSalesRecord(jsonobj) {
        // add to java object
        WPOS.updateSalesTable(jsonobj.ref, jsonobj);
        // save to local storage
        var jsonsales;
        if (localStorage.getItem("wpos_csales") !== null) {
            jsonsales = $.parseJSON(localStorage.getItem("wpos_csales"));
            jsonsales[jsonobj.ref] = jsonobj;
        } else {
            jsonsales = {};
            jsonsales[jsonobj.ref] = jsonobj;
        }
        localStorage.setItem("wpos_csales", JSON.stringify(jsonsales));
        return true;
    }

    function removeSalesRecord(ref){
        // remove from local storage
        var jsonsales;
        if (localStorage.getItem("wpos_csales") !== null) {
            jsonsales = $.parseJSON(localStorage.getItem("wpos_csales"));
            delete jsonsales[ref];
        }
        localStorage.setItem("wpos_csales", JSON.stringify(jsonsales));
        return true;
    }

    /**
     *
     * @returns {*}
     */
    this.getOfflineSalesNum = function () {
        if (localStorage.getItem("wpos_osales") !== null) {
            var jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
            return Object.keys(jsonosales).length;
        } else {
            return 0;
        }
    };
    this.getOfflineSales = function (){
        if (localStorage.getItem("wpos_osales") !== null) {
            var jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
            // strip the action variable so the data is returned in the same format as normal sales table'
            for (var i in jsonosales){
                jsonosales[i] = jsonosales[i].data;
            }
            return jsonosales;
        } else {
            return {};
        }
    };
    /**
     * @param {Object} jsonobj
     * @param {String} action
     * @returns {number|*}
     */
    this.updateOfflineSale = function(jsonobj, action){
        return addOfflineSale(jsonobj, action);
    };
    function addOfflineSale(jsonobj, action) {
        var jsonosales;
        if (localStorage.getItem("wpos_osales") !== null) {
            jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
        } else {
            jsonosales = {};
        }
        // If we wanted to allow multiple actions per record, we can change action to an array.
        jsonosales[jsonobj.ref] = {};
        jsonosales[jsonobj.ref].a = action;
        jsonosales[jsonobj.ref].data = jsonobj;
        localStorage.setItem("wpos_osales", JSON.stringify(jsonosales));
        return true;
    }

    this.isSaleOffline = function(ref){
        return isSaleOffline(ref);
    };

    function isSaleOffline(ref){
        var jsonosales;
        if (localStorage.getItem("wpos_osales") !== null) {
            jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
            if (jsonosales.hasOwnProperty(ref)){
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param {String} ref
     * @returns {boolean}
     */
    function removeOfflineSale(ref) {
        if (localStorage.getItem("wpos_osales") !== null) {
            var jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
            delete jsonosales[ref];
            localStorage.setItem("wpos_osales", JSON.stringify(jsonosales));
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    this.uploadOfflineRecords = function(){
        return uploadOfflineRecords();
    };
    function uploadOfflineRecords() {
        if (localStorage.getItem("wpos_osales") !== null) {
            var jsonosales = $.parseJSON(localStorage.getItem("wpos_osales"));
            var jsonresponse;
            var uploadcount = 1;
            var totalcount = Object.keys(jsonosales).length;
            for (var key in jsonosales){
                // update status
                WPOS.setStatusBar(2, "Uploading record "+uploadcount+" of "+totalcount);
                // what action to perform to the offline record is kept in the a var
                var action = jsonosales[key].a;
                if (action=="sales/void"){
                    action = (jsonosales[key].data.hasOwnProperty("id")?"sales/void":"sales/add"); // if sale is completely offline, use addsale method.
                }
                jsonresponse = WPOS.sendJsonData(action, JSON.stringify(jsonosales[key].data));
                if (jsonresponse !== false && jsonresponse !== null) {
                    // remove from offline temp
                    removeOfflineSale(jsonosales[key].data.ref);
                    // add json response to todays records
                    addSalesRecord(jsonresponse);
                }
                uploadcount++;
            }
            var count = WPOS.sales.getOfflineSalesNum();
            if (count > 0){
                // damn so close, go back into offline mode
                if (WPOS.switchToOffline()) {
                    // update status
                    WPOS.setStatusBar(3, "WPOS is offline ("+count+" offline records)");
                    return false;
                }
            } else {
                $("#backup_btn").hide();
                return true;
            }
        }
        return true;
    }
}
