<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 class="inline">
        Reports
    </h1>
    <select id="reptype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
        <option value="stats/dashboard">POS + Online (Rich)</option>
        <option value="customers/credit_report">Customer Credit Balances</option>
        <option value="suppliers/credit_report">Supplier Payables</option>
        <option value="stats/general">Summary</option>
        <option value="stats/takings">Takings Count</option>
        <option value="stats/itemselling">Item Sales</option>
        <option value="stats/categoryselling">Category Sales</option>
        <option value="stats/supplyselling">Supplier Sales</option>
        <option value="stats/stock">Current Stock</option>
        <option value="stats/devices">Device Takings</option>
        <option value="stats/locations">Location Takings</option>
        <option value="stats/users">User Takings</option>
        <option value="stats/tax">Tax Breakdown</option>
    </select>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Transactions
        <select id="reptranstype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
            <option value="all">All Sales</option>
            <option value="sale">POS Sales</option>
            <option value="invoice">Invoices</option>
        </select>
        </label>
    </div>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Range: <input type="text" style="width: 85px;" id="repstime" onclick="$(this).blur();" /></label>
        <label>to <input type="text" style="width: 85px;" id="repetime" onclick="$(this).blur();" /></label>
        <span style="white-space:nowrap;">
            <button class="btn btn-xs btn-default" onclick="repPreset('6h')">6h</button>
            <button class="btn btn-xs btn-default" onclick="repPreset('12h')">12h</button>
            <button class="btn btn-xs btn-default" onclick="repPreset('24h')">24h</button>
            <button class="btn btn-xs btn-default" onclick="repPreset('7d')">7d</button>
            <button class="btn btn-xs btn-default" onclick="repPreset('30d')">30d</button>
            <input type="datetime-local" id="repcustfrom" title="Custom from" style="width:160px; vertical-align:middle;" />
            <input type="datetime-local" id="repcustto" title="Custom to" style="width:160px; vertical-align:middle;" />
            <button class="btn btn-xs btn-primary" onclick="repPreset('custom')">Go</button>
        </span>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <button onclick="printCurrentReport();" class="btn btn-primary btn-sm"><i class="icon-print align-top bigger-125"></i>Print</button>&nbsp;
        <button class="btn btn-success btn-sm" onclick="exportCurrentReport();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
    </div>
</div><!-- /.page-header -->
<div class="row">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->
        <div style="overflow-x: auto; padding: 10px;">
            <div id="reportcontain">

            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<script type="text/javascript">
    var repdata;
    var etime;
    var stime;

    // Date presets: 6h/12h/24h/7d/30d + custom from/to (ms). Datepickers stay for date-only picks.
    function repPreset(p){
        var etime = Date.now(), stime = etime;
        if (p === '6h') stime = etime - 6*3600000;
        else if (p === '12h') stime = etime - 12*3600000;
        else if (p === '24h') stime = etime - 24*3600000;
        else if (p === '30d') stime = etime - 30*86400000;
        else if (p === 'custom') {
            var f = $('#repcustfrom').val(), tt = $('#repcustto').val();
            var ff = f ? new Date(f).getTime() : (etime - 7*86400000);
            var tu = tt ? new Date(tt).getTime() : etime;
            if (!isNaN(ff) && !isNaN(tu) && tu > ff) { stime = ff; etime = tu; }
        }
        else stime = etime - 7*86400000;
        window._repStime = stime; window._repEtime = etime;
        // reflect the window in the visible datepickers (date part)
        try { $("#repstime").datepicker('setDate', new Date(stime)); $("#repetime").datepicker('setDate', new Date(etime)); } catch(e) {}
        generateReport();
    }
    // Generate report
    function generateReport(){
        // presets set precise ms (incl. time); datepickers only give dates
        if (window._repStime && window._repEtime) { stime = window._repStime; etime = window._repEtime; }
        // show loader
        WPOS.util.showLoader();
        var type = $("#reptype").val();
        // load the data
        repdata = WPOS.sendJsonData(type, JSON.stringify({"stime":stime, "etime":etime, "type":$("#reptranstype").val()}));
        // populate the report using the correct function
        switch (type){
            case "stats/dashboard":
                populateRichDashboard();
                break;
            case "customers/credit_report":
                populateCreditBalances();
                break;
            case "suppliers/credit_report":
                populateSupplierBalances();
                break;
            case "stats/general":
                populateSummary();
                break;
            case "stats/takings":
                populateTakings("Takings Count", "Method");
                break;
            case "stats/itemselling":
                populateItems("Item Sales");
                break;
            case "stats/categoryselling":
                populateSelling("Category Sales");
                break;
            case "stats/supplyselling":
                populateSelling("Supplier Sales");
                break;
            case "stats/stock":
                populateStock();
                break;
            case "stats/devices":
                populateTakings("Device Takings", "Device Name");
                break;
            case "stats/locations":
                populateTakings("Location Takings", "Location Name");
                break;
            case "stats/users":
                populateTakings("User Takings", "User Name");
                break;
            case "stats/tax":
                populateTax();
        }
        // hide loader
        WPOS.util.hideLoader();
    }

    // REPORT GEN FUNCTIONS
    function getReportHeader(heading){
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+$("#repstime").val()+" - "+$("#repetime").val()+"</h5></div>";
    }

    function getCurrentReportHeader(heading){
        var timestamp = new Date();
        timestamp = timestamp.getTime();
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+WPOS.util.getDateFromTimestamp(timestamp)+"</h5>";
    }

    function populateCreditBalances(){
        var rows = (repdata && repdata.length) ? repdata : [];
        rows = rows.filter(function(r){ return parseFloat(r.open_balance) > 0.005; });
        var html = getCurrentReportHeader("Credit Balances — what was credited and when");
        if (!rows.length) { $("#reportcontain").html(html + "<div class='well'>No open credit balances — all customers are paid up.</div>"); return; }
        html += "<table class='table table-stripped' style='width:100%'><thead><tr><th>Customer</th><th>Phone</th><th>Open (KSh)</th><th>Limit</th><th>Available</th><th>Statement</th></tr></thead><tbody>";
        rows.forEach(function(r){
            var over = r.over_limit ? ' <span style="color:#c00;font-weight:700;">OVER</span>' : '';
            html += '<tr><td><strong>' + r.name + '</strong><br/><small>' + (r.email || '') + '</small></td><td>' + (r.phone || '') + '</td><td>' + WPOS.util.money(r.open_balance) + over + '</td><td>' + (parseFloat(r.credit_limit) > 0 ? WPOS.util.money(r.credit_limit) : '—') + '</td><td>' + (r.available === null ? '—' : WPOS.util.money(r.available)) + '</td><td class="noexport"><button class="btn btn-xs btn-primary" onclick="window.open(\'/api/credit_statement.php?custid=' + r.id + '\',\'_blank\')">PDF</button></td></tr>';
        });
        html += "</tbody></table><div style='margin-top:8px;font-size:12px;color:#666;'>Use Print for a neat PDF claim. Select a customer in Customers → Statement PDF for per-sale detail with due dates.</div>";
        $("#reportcontain").html(html);
    }
    function populateSupplierBalances(){
        var rows = (repdata && repdata.length) ? repdata : [];
        rows = rows.filter(function(r){ return parseFloat(r.open_balance) > 0.005; });
        var html = getCurrentReportHeader("Supplier Payables — what we owe and when");
        if (!rows.length) { $("#reportcontain").html(html + "<div class='well'>No open supplier payables — all suppliers are paid up.</div>"); return; }
        var tot = 0; rows.forEach(function(r){ tot += parseFloat(r.open_balance||0); });
        html += "<table class='table table-stripped' style='width:100%'><thead><tr><th>Supplier</th><th>Phone</th><th>Purchased</th><th>Open (KSh)</th><th>Next due</th><th>Limit</th><th>Available</th><th>Statement</th></tr></thead><tbody>";
        rows.forEach(function(r){
            var over = r.over_limit ? ' <span style="color:#c00;font-weight:700;">OVER</span>' : '';
            var dueTxt = r.next_due ? new Date(r.next_due).toLocaleDateString('en-KE', {day:'2-digit',month:'short',year:'numeric'}) : '—';
            var dueCol = (r.next_due && r.next_due < Date.now()) ? 'color:#c00;font-weight:700;' : '';
            html += '<tr><td><strong>' + r.name + '</strong><br/><small>' + (r.email || '') + '</small></td><td>' + (r.phone || '') + '</td><td>' + WPOS.util.money(r.total_purchased||0) + '</td><td>' + WPOS.util.money(r.open_balance) + over + '</td><td><span style="' + dueCol + '">' + dueTxt + (r.open_purchases ? ' <small>(' + r.open_purchases + ' open)</small>' : '') + '</span></td><td>' + (parseFloat(r.credit_limit) > 0 ? WPOS.util.money(r.credit_limit) : '—') + '</td><td>' + (r.available === null ? '—' : WPOS.util.money(r.available)) + '</td><td class="noexport"><button class="btn btn-xs btn-primary" onclick="window.open(\'/api/supplier_statement.php?supplierid=' + r.id + '\',\'_blank\')">PDF</button></td></tr>';
        });
        html += "</tbody></table><div style='margin-top:8px;font-size:12px;color:#666;'>Total payable " + WPOS.util.money(tot) + ". Use Print for a neat PDF claim. Open Suppliers → Statement PDF for per-purchase detail with due dates.</div>";
        $("#reportcontain").html(html);
    }
    function populateRichDashboard(){
        var html = getReportHeader("POS + Online — Rich Report");
        var k = (repdata && repdata.kpis) ? repdata.kpis : {revenue:0, orders:0, completed:0, queued:0, avg_order:0, completion_rate:0};
        html += "<table class='table table-stripped' style='width:100%'><thead><tr><td>Metric</td><td>Value</td></tr></thead><tbody>";
        html += "<tr><td>Collected revenue (completed)</td><td>"+WPOS.util.currencyFormat(k.revenue)+"</td></tr>";
        html += "<tr><td>Total orders (POS + online)</td><td>"+(k.orders||0)+"</td></tr>";
        html += "<tr><td>Completed / Queued</td><td>"+(k.completed||0)+" / "+(k.queued||0)+"</td></tr>";
        html += "<tr><td>Average order value (completed)</td><td>"+WPOS.util.currencyFormat(k.avg_order)+"</td></tr>";
        html += "<tr><td>Completion rate</td><td>"+(k.completion_rate||0)+"%</td></tr>";
        html += "</tbody></table>";
        html += "<h4>By Source</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>Source</td><td>Orders</td><td>Collected</td><td>Gross (incl. queued)</td></tr></thead><tbody>";
        for (var s in (repdata.by_source || {})) { var r = repdata.by_source[s]; html += '<tr><td>'+s+'</td><td>'+r.orders+'</td><td>'+WPOS.util.currencyFormat(r.revenue)+'</td><td>'+WPOS.util.currencyFormat(r.gross)+'</td></tr>'; }
        html += "</tbody></table>";
        html += "<h4>By Order Type</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>Type</td><td>Orders</td><td>Collected</td></tr></thead><tbody>";
        for (var t in (repdata.by_type || {})) { var r2 = repdata.by_type[t]; html += '<tr><td>'+t+'</td><td>'+r2.orders+'</td><td>'+WPOS.util.currencyFormat(r2.revenue)+'</td></tr>'; }
        html += "</tbody></table>";
        html += "<h4>By Payment</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>Method</td><td>Payments</td><td>Collected</td></tr></thead><tbody>";
        for (var p in (repdata.by_payment || {})) { var r3 = repdata.by_payment[p]; html += '<tr><td>'+p+'</td><td>'+r3.payments+'</td><td>'+WPOS.util.currencyFormat(r3.revenue)+'</td></tr>'; }
        html += "</tbody></table>";
        html += "<h4>Hourly</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>Hour</td><td>Orders</td><td>Collected</td></tr></thead><tbody>";
        for (var h in (repdata.by_hour || {})) { var r4 = repdata.by_hour[h]; html += '<tr><td>'+h+':00</td><td>'+r4.orders+'</td><td>'+WPOS.util.currencyFormat(r4.revenue)+'</td></tr>'; }
        html += "</tbody></table>";
        html += "<h4>Top Items</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>Item</td><td>Qty</td><td>Revenue</td></tr></thead><tbody>";
        (repdata.top_items || []).forEach(function(it){ html += '<tr><td>'+it.name+'</td><td>'+it.qty+'</td><td>'+WPOS.util.currencyFormat(it.revenue)+'</td></tr>'; });
        html += "</tbody></table>";
        // Per-order detail (delivery location + items on demand) — recent 20 with View
        html += "<h4>Orders detail (delivery + items — View for full detail)</h4><table class='table table-stripped' style='width:100%'><thead><tr><th>ID</th><th>Ref</th><th>Source</th><th>Customer</th><th>Location</th><th>Total</th><th>Status</th><th>View</th></tr></thead><tbody>";
        var recs = (repdata.recent || []);
        if (!recs.length) html += "<tr><td colspan='8'>No orders in range.</td></tr>";
        recs.forEach(function(r){
            var loc = r.delivery_address || (r.table_number ? 'Table ' + r.table_number : '-');
            var st = r.status == 1 ? 'Completed' : (r.orderstatus || 'Queued');
            html += '<tr><td>' + r.id + '</td><td>' + (r.ref || '-') + '</td><td>' + (r.source || '-') + '</td><td>' + (r.customer || '-') + '</td><td style="font-size:11px;" title="' + String(loc).replace(/"/g,'&quot;') + '">' + loc + '</td><td>' + (r.total || 0) + '/=</td><td>' + st + '</td><td class="noexport"><button class="btn btn-xs btn-primary" onclick="WPOS.transactions.openTransactionDialog(\'' + r.ref + '\')">View</button></td></tr>';
        });
        html += "</tbody></table><div style='margin-top:6px;font-size:11px;color:#666;'>View shows delivery address/table, full item list, payments and customer — use Print for a neat PDF claim.</div>";
        html += "<h4>Recent (detailed)</h4><table class='table table-stripped' style='width:100%'><thead><tr><td>ID</td><td>Ref</td><td>Source</td><td>Type</td><td>Total</td><td>Status</td><td>Customer</td><td>Received</td></tr></thead><tbody>";
        (repdata.recent || []).forEach(function(o){ html += '<tr><td>'+o.id+'</td><td>'+o.ref+'</td><td>'+(o.source||'-')+'</td><td>'+(o.order_type||'-')+'</td><td>'+o.total+'/=</td><td>'+o.status+'</td><td>'+(o.customer||'-')+'</td><td>'+(o.received_at||o.dt||'')+'</td></tr>'; });
        html += "</tbody></table>";
        $("#reportcontain").html(html);
    }

    function populateSummary(){
        var html = getReportHeader("Summary");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td></td><td># Sales</td><td>Total</td></tr></thead><tbody>";
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.salerefs+'\');">Sales</a></td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.saletotal)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refundrefs+'\');">Refunds</a></td><td>'+repdata.refundnum+'</td><td>'+WPOS.util.currencyFormat(repdata.refundtotal)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.voidrefs+'\');">Voids</a></td><td>'+repdata.voidnum+'</td><td>'+WPOS.util.currencyFormat(repdata.voidtotal)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refs+'\');">Revenue</a></td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.totaltakings)+'</td></tr>';
        html += '<tr><td>Cost</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.cost)+'</td></tr>';
        html += '<tr><td>Profit</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.profit)+'</td></tr>';
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }


    function populateTakings(repname, colname){
        var html = getReportHeader(repname);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>"+colname+"</td><td># Sales</td><td>Takings</td><td># Refunds</td><td>Refunds</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+(rowdata.hasOwnProperty('name')?rowdata.name:i)+'</a></td><td>'+rowdata.salenum+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletotal)+'</td><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refundrefs+'\');">'+rowdata.refundnum+'</a></td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }
        html += "</tbody></table>";
        $("#reportcontain").html(html);
    }

    function populateSelling(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Sold</td><td>Total</td><td># Refunded</td><td>Total</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.soldqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.soldtotal)+'</td><td>'+rowdata.refundqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateItems(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Sold</td><td>Discounts</td><td>Tax</td><td>Total</td><td># Refunded</td><td>Total</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.soldqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.discounttotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.taxtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.soldtotal)+'</td><td>'+rowdata.refundqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateTax(){
        var html = getReportHeader("Tax Breakdown");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Items</td><td>Sale Subtotal</td><td>Tax</td><td>Refund Subtotal</td><td>Refund Tax</td><td>Total Tax</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            if (i!=0){
                rowdata = repdata[i];
                html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.qtyitems+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletax)+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtax)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
            }
        }

        html += "</tbody></table><br/>";

        rowdata = repdata[0];
        html += '<p style="text-align: center;">Note: <a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.qty+'</a> sales have been cash rounded to a total amount of '+WPOS.util.currencyFormat(rowdata.total)+'.<br/>Since tax is calculated on a per item level, rounding has not been included in the calculations above.<br/>Subtotals above have discounts applied.</p>';

        $("#reportcontain").html(html);
    }

    function populateStock(){
        var html = getCurrentReportHeader("Current Stock");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Supplier</td><td>Location</td><td>Stock Qty</td><td>Stock Value</td></tr></thead><tbody>";
        for (var i in repdata){
            rowdata = repdata[i];
            html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.supplier+"</td><td>"+rowdata.location+"</td><td>"+rowdata.stocklevel+"</td><td>"+rowdata.stockvalue+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    var REPORT_CSS = "@page{margin:12mm} body{font-size:12px; font-family:Arial,sans-serif;} "
        + ".rep-biz{display:flex; align-items:center; gap:12px; border-bottom:3px solid #84cc16; padding-bottom:10px; margin-bottom:12px;}"
        + ".rep-biz img{height:52px; width:52px; border-radius:50%; object-fit:cover; border:2px solid #84cc16;}"
        + ".rep-biz .nm{font-size:20px; font-weight:800;} .rep-biz .ct{font-size:11px; color:#555;}"
        + ".rep-meta{font-size:11px; color:#666; margin:4px 0 10px;}"
        + "table{border-collapse:collapse; width:100%;} th,td{border:1px solid #ccc !important; padding:4px 6px !important; text-align:left;} thead{display:table-header-group;} tfoot{display:table-footer-group;} tr:nth-child(even) td{background:#f7f7f5;} "
        + ".btn{display:none !important} h3{text-align:center} h5{text-align:center;color:#666}";
    function printReportHeader(){
        var d = new Date();
        var rangeTxt = $("#repstime").val() + " - " + $("#repetime").val();
        var typeTxt = $("#reptype").find("option:selected").text();
        return '<div class="rep-biz"><img src="' + window.location.origin + '/assets/images/biz-logo.jpg" alt="Corner Bites"/><div><div class="nm">Corner Bites</div><div class="ct">Tasty Meals, Every Time! &bull; Utawala-Kincar, Nairobi &bull; Hotline 0185112315</div></div></div>'
            + '<div class="rep-meta"><strong>' + typeTxt + '</strong> &nbsp;|&nbsp; Generated ' + d.toLocaleString('en-KE') + (rangeTxt ? ' &nbsp;|&nbsp; Range: ' + rangeTxt : '') + '</div>';
    }
    function printCurrentReport(){
        browserPrintHtml(printReportHeader() + $("#reportcontain").html(), REPORT_CSS);
    }

    function exportCurrentReport(){
        var title = $("#reportcontain div h3").text() || 'report';
        var data = WPOS.table2CSV($("#reportcontain"));
        var d = new Date();
        var rangeTxt = $("#repstime").val() + " - " + $("#repetime").val();
        var csvHeader = '"Corner Bites - Tasty Meals, Every Time!"\n'
            + '"' + title.replace(/"/g, "'") + '"\n'
            + '"Generated: ' + d.toLocaleString('en-KE') + '"\n'
            + '"Range: ' + rangeTxt + '"\n'
            + '"Report: ' + (("#reptype").length ? $("#reptype").find("option:selected").text() : '') + '"\n\n';
        var filename = (title + "-" + (new Date().getTime())).replace(/[^A-Za-z0-9_-]/g, "-");
        WPOS.initSave(filename, csvHeader + data);
    }

    function browserPrintHtml(html, extraCss){
        var printw = window.open('', 'wpos report', 'height=800,width=650');
        printw.document.write('<html><head><title>Wpos Report</title>');
        printw.document.write('<link media="all" href="/assets/css/bootstrap.min.css" rel="stylesheet"/><link media="all" rel="stylesheet" href="/assets/css/font-awesome.min.css"/><link media="all" rel="stylesheet" href="/assets/css/ace-fonts.css"/><link media="all" rel="stylesheet" href="/assets/css/ace.min.css"/>');
        printw.document.write('<style>' + (extraCss || '') + '</style>');
        printw.document.write('</head><body style="background-color: #FFFFFF;">');
        printw.document.write(html);
        printw.document.write('</body></html>');
        printw.document.close();

        printw.print();
        printw.close();
    }

    $(function(){
        etime = new Date().getTime();
        stime = (etime - 604800000); // a week ago

        $("#repstime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repstime").datepicker("getDate");
                date.setHours(0); date.setMinutes(0); date.setSeconds(0);
                stime = date.getTime();
                generateReport();
            }
        });
        $("#repetime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repetime").datepicker("getDate");
                date.setHours(23); date.setMinutes(59); date.setSeconds(59);
                etime = date.getTime();
                generateReport();
            }
        });

        $("#repstime").datepicker('setDate', new Date(stime));
        $("#repetime").datepicker('setDate', new Date(etime));
        generateReport(); // generate initial report

        // hide loader
        WPOS.util.hideLoader();
    });
</script>