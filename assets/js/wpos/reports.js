/**
 * reports.js is part of Wallace Point of Sale system (WPOS)
 *
 * reports.js Provides functions for calculating till reports and reconciliation items.
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

function WPOSReports() {
    // Overview
    this.populateOverview = function () {
        var stats = getOverviewStats();
        // Fill UI
        $("#rsalesnum").text(stats.salesnum);
        $("#rsalestotal").text(WPOS.util.currencyFormat(stats.salestotal.toFixed(2)));
        $("#rrefundsnum").text(stats.refundnum);
        $("#rrefundstotal").text(WPOS.util.currencyFormat(stats.refundtotal.toFixed(2)));
        $("#rvoidsnum").text(stats.voidnum);
        $("#rvoidstotal").text(WPOS.util.currencyFormat(stats.voidtotal.toFixed(2)));
        $("#rtotaltakings").text(WPOS.util.currencyFormat(stats.totaltakings.toFixed(2)));

        showAdditionalReports();
    };

    function showAdditionalReports(){
        // show eftpos reports if available
        if (WPOS.hasOwnProperty('eftpos') && WPOS.eftpos.isEnabledAndReady() && WPOS.eftpos.getType()=="tyro"){
            $("#tyroreports").removeClass('hide');
        } else {
            $("#tyroreports").addClass('hide');
        }
    }

    var curstats;

    // Normalise sale timestamps: POS/webhook use ms, older rows may be seconds.
    function saleTimeMs(saleobj) {
        var ts = parseFloat(saleobj && saleobj.processdt ? saleobj.processdt : 0);
        if (!ts && saleobj && saleobj.dt) ts = Date.parse(saleobj.dt);
        if (!ts) return 0;
        return ts < 1e11 ? ts * 1000 : ts;
    }
    function dayWindow() {
        var stime = new Date(); stime.setHours(0, 0, 0, 0);
        var etime = new Date(); etime.setHours(23, 59, 59, 999);
        return { stime: stime.getTime(), etime: etime.getTime() };
    }

    function getTodaysRecords(includerefunds) {
        var sales = WPOS.getSalesTable();
        var todaysales = {};
        var w = dayWindow();
        var stime = w.stime;
        var etime = w.etime;
        for (var key in sales) {
            // ignore if an order (uncompleted web/phone orders are not sales yet)
            if (sales[key].hasOwnProperty('isorder') && sales[key].isorder) continue;
            var ts = saleTimeMs(sales[key]);
            // include every channel once it is an actual sale: POS tills, website/online
            // (devid 0) and other sources — staff reports are store-wide, not this-till only.
            if (ts >= stime && ts <= etime) {
                todaysales[key] = sales[key];
            } else if (includerefunds && sales[key].hasOwnProperty('refunddata')) {
                var refdata = sales[key].refunddata;
                for (var record in refdata) {
                    var rts = parseFloat(refdata[record].processdt || 0);
                    if (rts && rts < 1e11) rts *= 1000;
                    if (rts >= stime && rts <= etime) { todaysales[key] = sales[key]; break; }
                }
            }
        }
        return todaysales;
    }

    function getOverviewStats() {
        var sales = getTodaysRecords(true);
        var sale;
        var emptfloat = parseFloat("0.00");
        var w = dayWindow();
        var stime = w.stime;
        var etime = w.etime;
        var data = {'salesnum': 0, 'salestotal': emptfloat, 'voidnum': 0, 'voidtotal': emptfloat, 'refundnum': 0, 'refundtotal': emptfloat, 'totaltakings': emptfloat, 'methodtotals': {}};
        var salestat;
        for (var key in sales) {
            sale = sales[key];
            salestat = getTransactionStatus(sale);
            var amount;
            var method;
            switch (salestat) {
                case 2:
                    data.voidnum++;
                    data.voidtotal += parseFloat(sale.total);
                    break;
                case 3:
                    // cycle though all refunds and add to total
                    for (var i in sale.refunddata) {
                        amount = parseFloat(sale.refunddata[i].amount);
                        method = sale.refunddata[i].method;
                        data.refundnum++;
                        data.refundtotal += amount;
                        // add payment type totals
                        if (data.methodtotals.hasOwnProperty(method)) { // check if payment method field is alredy set
                            data.methodtotals[method].refamount += amount;
                            data.methodtotals[method].refqty++;
                        } else {
                            data.methodtotals[method] = {};
                            data.methodtotals[method].refamount = amount;
                            data.methodtotals[method].refqty = 1;
                            data.methodtotals[method].amount = parseFloat(0);
                            data.methodtotals[method].qty = 0;
                        }
                    }
                    // count refund as a sale, but only if it was sold today
                    var _ts = saleTimeMs(sale);
                    if (_ts < stime || _ts > etime) {
                        break; // the sale was not made today
                    }
                case 1:
                    data.salesnum++;
                    data.salestotal += parseFloat(sale.total);
                    // calc payment methods
                    for (var p in sale.payments) {
                        amount = parseFloat(sale.payments[p].amount);
                        method = sale.payments[p].method;
                        if (data.methodtotals.hasOwnProperty(method)) { // check if payment method field is alredy set
                            data.methodtotals[method].amount += amount;
                            data.methodtotals[method].qty++;
                        } else {
                            data.methodtotals[method] = {};
                            data.methodtotals[method].amount = amount;
                            data.methodtotals[method].qty = 1;
                            data.methodtotals[method].refamount = parseFloat(0);
                            data.methodtotals[method].refqty = 0;
                        }
                    }
            }
        }
        for (var x in data.methodtotals){
            data.methodtotals[x].amount = parseFloat(data.methodtotals[x].amount).toFixed(2);
            data.methodtotals[x].refamount = parseFloat(data.methodtotals[x].refamount).toFixed(2);
        }
        // calculate takings
        data.totaltakings = data.salestotal.toFixed(2) - data.refundtotal.toFixed(2);
        // (cash handling happens via the Accounts cash book — no drawer reconciliation needed)
        curstats = data;
        //alert(JSON.stringify(data));
        return data;
    }

    function getTransactionStatus(saleobj) {
        if (saleobj.hasOwnProperty('voiddata')) {
            return 2;
        } else if (saleobj.hasOwnProperty('refunddata')) {
            return 3;
        }
        return 1;
    }

    var config;
    var reportheader = function (name) {
        if (config == null) {
            config = WPOS.getConfigTable();
        }
        return '<div style="text-align: center; margin-bottom: 5px;"><h3>' + name + '</h3><h5>' + WPOS.util.getShortDate(null) + ' - ' + config.devicename + ' - ' + config.locationname + '</h5></div>';
    };

    function getSellerStats(){
        var sales = getTodaysRecords(true);
        var sale;
        var emptfloat = parseFloat("0.00");
        var w = dayWindow();
        var stime = w.stime;
        var etime = w.etime;

        var data = {};
        var salestat;
        for (var key in sales) {
            sale = sales[key];
            salestat = getTransactionStatus(sale);
            var userid;
            switch (salestat) {
                case 2:
                    userid = sale.voiddata.userid;
                    if (data.hasOwnProperty(userid)) {
                        data[userid].voidrefs.push(sale.ref);
                        data[userid].voidnum++;
                        data[userid].voidtotal += parseFloat(sale.total);
                    } else {
                        data[userid] = {};
                        data[userid].salerefs = [];
                        data[userid].salenum = 0;
                        data[userid].saleamount = 0;
                        data[userid].refrefs = [];
                        data[userid].refnum = 0;
                        data[userid].refamount = 0;
                        data[userid].voidrefs = [sale.ref];
                        data[userid].voidnum = 1;
                        data[userid].voidamount = parseFloat(sale.total);
                    }
                    break;
                case 3:
                    // cycle though all refunds and add to total
                    for (var i in sale.refunddata) {
                        var amount = parseFloat(sale.refunddata[i].amount);
                        userid = sale.refunddata[i].userid;
                        if (data.hasOwnProperty(userid)) {
                            data[userid].refrefs.push(sale.ref);
                            data[userid].refnum++;
                            data[userid].reftotal += parseFloat(amount);
                        } else {
                            data[userid] = {};
                            data[userid].salerefs = [];
                            data[userid].salenum = 0;
                            data[userid].saletotal = 0;
                            data[userid].refrefs = [sale.ref];
                            data[userid].refnum = 1;
                            data[userid].reftotal = parseFloat(amount);
                            data[userid].voidrefs = [];
                            data[userid].voidnum = 0;
                            data[userid].voidtotal = 0;
                        }
                    }
                    // count refund as a sale, but only if it was sold today
                    var _ts = saleTimeMs(sale);
                    if (_ts < stime || _ts > etime) {
                        break; // the sale was not made today
                    }
                case 1:
                    if (data.hasOwnProperty(sale.userid)) {
                        data[sale.userid].salerefs.push(sale.ref);
                        data[sale.userid].salenum++;
                        data[sale.userid].saletotal += parseFloat(sale.total);
                    } else {
                        data[sale.userid] = {};
                        data[sale.userid].salerefs = [sale.ref];
                        data[sale.userid].salenum = 1;
                        data[sale.userid].saletotal = parseFloat(sale.total);
                        data[sale.userid].refrefs = [];
                        data[sale.userid].refnum = 0;
                        data[sale.userid].reftotal = 0;
                        data[sale.userid].voidrefs = [];
                        data[sale.userid].voidnum = 0;
                        data[sale.userid].voidtotal = 0;
                    }
            }
        }
        for (var x in data){
            data[x].balance = (data[x].saletotal - data[x].reftotal).toFixed(2);
            data[x].saletotal = data[x].saletotal.toFixed(2);
            data[x].reftotal = data[x].reftotal.toFixed(2);
        }

        return data;
    }

    function getWhatsSellingStats() {
        var itemstats = {items: [], totalsold: 0};
        var records = getTodaysRecords(false);
        var items = [];
        var item = {};
        var discount = 0;
        var discprice = 0;
        for (var ref in records) {
            if (!records[ref].hasOwnProperty('voiddata')) { // do not count voided sales
                discount = parseFloat(records[ref].discount) || 0;
                items = records[ref].items;
                if (!items) continue;
                for (var index in items) {
                    item = items[index];
                    if (!item) continue;
                    // Delivery-fee service lines are not products — keep totals in Overview, skip here.
                    if (item.is_delivery_fee) continue;
                    var qty = parseInt(item.qty) || 1;
                    var unit = parseFloat(item.price) || 0;
                    var lineTotal = (unit - unit * (discount / 100)) * qty;
                    var sid = (item.sitemid === undefined || item.sitemid === null) ? (item.name || 'Miscellaneous') : item.sitemid;
                    if (itemstats.items.hasOwnProperty(sid)) {
                        itemstats.items[sid].qty += qty;
                        itemstats.items[sid].total += lineTotal;
                    } else {
                        var itemname = (sid == "0" ? "Miscellaneous" : (item.name || sid));
                        itemstats.items[sid] = {"qty": qty, "total": lineTotal, "name": itemname};
                    }
                    itemstats.totalsold += qty;
                }
            }
        }

        return itemstats;
    }



    this.generateWhatsSellingReport = function () {
        var html = reportheader("What's Selling Report") + '<table style="width:100%;" class="table table-stripped"><thead><tr><th>Item</th><th># Sold</th><th>Total</th></tr></thead><tbody>';
        var stats = getWhatsSellingStats();
        var item;
        var rows = 0;
        for (var id in stats.items) {
            item = stats.items[id];
            html += '<tr><td>' + item.name + '</td><td>' + item.qty + '</td><td>' + WPOS.util.currencyFormat(item.total) + '</td></tr>';
            rows++;
        }
        if (!rows) html += '<tr><td colspan="3" style="text-align:center;">No sales today yet.</td></tr>';

        html += '</tbody></table>';
        // put into report window
        $("#reportcontain").html(html);
    };

    this.generateSellerReport = function () {
        var html = reportheader("Seller Takings") + '<table style="width:100%;" class="table table-stripped"><thead><tr><th>User</th><th>Sales</th><th>Voids</th><th>Refunds</th><th>Balance</th></tr></thead><tbody>';
        var stats = getSellerStats();
        var item;
        var users = WPOS.getConfigTable().users;
        for (var id in stats) {
            item = stats[id];
            var user = users.hasOwnProperty(id) ? users[id].username : 'Unknown';
            html += '<tr><td>' + user + '</td><td>' + WPOS.util.currencyFormat(item.saletotal) + ' ('+item.salenum+')' + '</td><td>' + WPOS.util.currencyFormat(item.voidtotal) + ' ('+item.voidnum+')' + '</td><td>' + WPOS.util.currencyFormat(item.reftotal) + ' ('+item.refnum+')' + '</td><td>' + WPOS.util.currencyFormat(item.balance) + '</td></tr>';
        }

        html += '</tbody></table>';
        // put into report window
        $("#reportcontain").html(html);
    };

    this.generateTyroReport = function(){
        var type = $("#tyroreptype").val();
        WPOS.eftpos.getTyroReport(type, (type=="detail"?WPOS.reports.populateTyroDetailed:WPOS.reports.populateTyroSummary));
    };

    this.populateTyroSummary = function(xml){
        xml = parseXML(xml);
        var html = reportheader("Tyro Eftpos Summary Report") + '<table style="width:100%;" class="table table-stripped"><thead><tr><th>Card Type</th><th style="text-align: right;">Purchase</th><th style="text-align: right;">Cash-Out</th><th style="text-align: right;">Refunds</th><th style="text-align: right;">Total</th></tr></thead><tbody>';
        var line = xml.find("card");
        //console.log(xml);
        //console.log(recon);
        $.each(line, function(i){
            //console.log($(this));
            html += '<tr><td style="text-align: left;">' + WPOS.util.capFirstLetter($(this).attr('type')) + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('purchases')) + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('cash-out')?$(this).attr('cash-out'):'0.00') + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('refunds')) + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('total')) + '</td></tr>';
        });
        html += '<tr><td colspan="4" style="text-align: left;"><strong>Total:</strong></td><td style="text-align: right;">' +WPOS.util.currencyFormat(xml.find("reconciliation-summary").attr('total')) + '</td></tr>';
        html += '</tbody></table>';
        // put into report window
        $("#reportcontain").html(html);
    };

    this.populateTyroDetailed = function(xml){
        xml = parseXML(xml);
        var html = reportheader("Tyro Eftpos Detail Report") + '<table style="width:100%;" class="table table-stripped"><thead><tr><th>Time</th><th>Type</th><th>Card Type</th><th style="text-align: right;">Cash Out</th><th style="text-align: right;">Total</th></tr></thead><tbody>';
        var line = xml.find("transaction");
        $.each(line, function(i){
            html += '<tr><td  style="text-align: left;">' + $(this).attr('transaction-local-date-time') + '</td><td style="text-align: left;">' + WPOS.util.capFirstLetter($(this).attr('type')) + '</td><td style="text-align: left;">' + WPOS.util.capFirstLetter($(this).attr('card-type')) + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('cash-out')?$(this).attr('cash-out'):'0.00') + '</td><td style="text-align: right;">' + WPOS.util.currencyFormat($(this).attr('amount')) + '</td></tr>';
        });
        html += '<tr><td colspan="3" style="text-align: left;"><strong>Total:</strong></td><td style="text-align: right;">' + WPOS.util.currencyFormat(xml.find("reconciliation-detail").attr('total')) + '</td></tr>';
        html += '</tbody></table>';
        // put into report window
        $("#reportcontain").html(html);
    };

    function parseXML(xml){
        var xmlobj = $.parseXML(xml);
        return $(xmlobj);
    }
}