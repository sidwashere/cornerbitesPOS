<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 style="display: inline-block;">
        Customers
    </h1>
    <button onclick="WPOS.customers.openAddCustomerDialog();" id="addbtn" class="btn btn-primary btn-sm pull-right"><i class="icon-pencil align-top bigger-125"></i>Add</button>
    <button class="btn btn-success btn-sm pull-right" style="margin-right: 10px;" onclick="exportCustomers();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
    <button class="btn btn-xs btn-primary pull-right" style="margin-right:8px; margin-top:4px;" onclick="showCreditBalances();"><i class="icon-list align-top bigger-125"></i>Credit Balances</button>
    <div id="credit-balances" style="display:none; clear:both; margin-top:10px; background:#fff; border:1px solid #ddd; border-radius:8px; padding:10px;"></div>
</div><!-- /.page-header -->

<div class="row">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->

        <div class="row">
            <div class="col-xs-12">

                <div class="table-header">
                    Manage your customer base
                </div>

                    <table id="customertable" class="table table-striped table-bordered table-hover dt-responsive" style="width: 100%;">
                        <thead>
                        <tr>
                            <th data-priority="0" class="center">
                                <label>
                                    <input type="checkbox" class="ace" />
                                    <span class="lbl"></span>
                                </label>
                            </th>
                            <th data-priority="2" >ID</th>
                            <th data-priority="3" >Name</th>
                            <th data-priority="4" >Email</th>
                            <th data-priority="5" >Phone</th>
                            <th data-priority="7" >Suburb</th>
                            <th data-priority="6" >Open Balance</th>
                            <th data-priority="5" >Credit Limit</th>
                            <th data-priority="1" ></th>
                        </tr>
                        </thead>

                        <tbody>

                        </tbody>
                    </table>
            </div>
        </div>

    </div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->

<!-- inline scripts related to this page -->
<script type="text/javascript">
    var datatable;
    $(function() {
        WPOS.customers.loadCustomers();
        // Enrich rows with open credit balances (best-effort)
        try {
            WPOS.sendJsonDataAsync('customers/credit_report', JSON.stringify({}), function(res){
                if (res && res.length) {
                    var custs = WPOS.customers.getCustomers();
                    res.forEach(function(r){ if (custs[r.id]) custs[r.id].open_balance = r.open_balance; });
                    if (typeof datatable !== 'undefined' && datatable && datatable.api) {
                        var arr = [];
                        for (var k in custs) arr.push(custs[k]);
                        datatable.fnClearTable(false); datatable.fnAddData(arr, false); datatable.api().draw(false);
                    }
                }
            });
        } catch(e) {}
        var data = WPOS.customers.getCustomers();
        var itemarray = [];
        for (var key in data){
            itemarray.push(data[key]);
        }
        datatable = $('#customertable').dataTable({
            "bProcessing": true,
            "aaData": itemarray,
            "aaSorting": [[ 1, "asc" ]],
            "aLengthMenu": [ 10, 25, 50, 100, 200],
            "aoColumns": [
                { mData:null, sDefaultContent:'<div style="text-align: center"><label><input class="ace dt-select-cb" type="checkbox"><span class="lbl"></span></label><div>', bSortable: false },
                { "mData":"id" },
                { "mData":"name" },
                { "mData":"email" },
                { "mData":"phone" },
                { "mData":"suburb" },
                { mData:null, mRender:function(d){ var bal = d && d.open_balance ? parseFloat(d.open_balance) : 0; var lim = d && d.credit_limit ? parseFloat(d.credit_limit) : 0; if (bal<=0.005) return '<span style="color:#888;">—</span>'; var col = (lim>0 && bal>lim) ? 'color:#c00;font-weight:700;' : 'color:#a35c00;'; return '<span style="'+col+'">'+WPOS.util.money(bal)+'</span>'; } },
                { mData:null, mRender:function(d){ var lim = d && d.credit_limit ? parseFloat(d.credit_limit) : 0; return lim>0 ? WPOS.util.money(lim) : '<span style="color:#888;">—</span>'; } },
                { mData:null, sDefaultContent:'<div class="action-buttons"><a class="green" onclick="WPOS.customers.openCustomerDialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-pencil bigger-130"></i></a><a class="red" onclick="WPOS.customers.deleteCustomer($(this).closest(\'tr\').find(\'td\').eq(1).text())"><i class="icon-trash bigger-130"></i></a></div>', bSortable: false }
            ],
            "columns": [
                {},
                {type: "numeric"},
                {type: "string"},
                {type: "string"},
                {type: "string"},
                {type: "numeric"},
                {type: "currency"},
                {type: "string"},
                {type: "string"},
                {type: "string"},
                {}
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Add selected row count to footer
                var selected = this.api().rows('.selected').count();
                return sPre+(selected>0 ? '<br/>'+selected+' row(s) selected':'');
            }
        });

        datatable.find("tbody").on('click', '.dt-select-cb', function(e){
            var row = $(this).parents().eq(3);
            if (row.hasClass('selected')) {
                row.removeClass('selected');
            } else {
                row.addClass('selected');
            }
            datatable.api().draw(false);
            e.stopPropagation();
        });

        $('table.dataTable th input:checkbox').on('change' , function(){
            var that = this;
            $(this).closest('table.dataTable').find('tr > td:first-child input:checkbox')
                .each(function(){
                    var row = $(this).parents().eq(3);
                    if ($(that).is(":checked")) {
                        row.addClass('selected');
                        $(this).prop('checked', true);
                    } else {
                        row.removeClass('selected');
                        $(this).prop('checked', false);
                    }
                });
            datatable.api().draw(false);
        });

        // hide loader
        WPOS.util.hideLoader();
    });
    if (!window._cbHideBalancesBound) {
        window._cbHideBalancesBound = true;
        $(document).on('click', '.cb-hide-balances', function(){ $('#credit-balances').hide(); });
    }
    function showCreditBalances(){
        var box = $("#credit-balances");
        box.show().html('<div style="color:#888;">Loading credit balances…</div>');
        WPOS.sendJsonDataAsync('customers/credit_report', JSON.stringify({}), function(res){
            var rows = (res && res.length) ? res : [];
            rows = rows.filter(function(r){ return parseFloat(r.open_balance) > 0.005; });
            if (!rows.length) { box.html('<div style="color:#777;">No open credit balances. All customers are paid up 🎉</div>'); return; }
            var html = '<div style="display:flex;justify-content:space-between;align-items:center;"><strong>Open credit balances (' + rows.length + ' customers)</strong><span><button class="btn btn-xs btn-default" onclick="window.print();">🖨 Print / Save PDF</button> <button class="btn btn-xs btn-default cb-hide-balances">Hide</button></span></div>';
            html += '<table class="table table-striped table-bordered" style="font-size:12px;margin-top:8px;"><thead><tr><th>Customer</th><th>Phone</th><th>Open (KSh)</th><th>Limit</th><th>Available</th><th></th></tr></thead><tbody>';
            rows.forEach(function(r){
                var over = r.over_limit ? ' <span style="color:#c00;font-weight:700;">OVER</span>' : '';
                html += '<tr><td><strong>' + r.name + '</strong></td><td>' + (r.phone || '') + '</td><td>' + WPOS.util.money(r.open_balance) + over + '</td><td>' + (parseFloat(r.credit_limit) > 0 ? WPOS.util.money(r.credit_limit) : '—') + '</td><td>' + (r.available === null ? '—' : WPOS.util.money(r.available)) + '</td><td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="window.open(\'/api/credit_statement.php?custid=' + r.id + '\',\'_blank\')">Statement PDF</button> <button class="btn btn-xs btn-success" onclick="WPOS.customers.creditPayDialog(' + r.id + ');">Pay</button></td></tr>';
            });
            html += '</tbody></table>';
            box.html(html);
        });
    }
    function reloadCustomerData(){
        WPOS.customers.loadCustomers();
        // enrich with open balances for the table (best-effort, silent on failure)
        try {
            WPOS.sendJsonDataAsync('customers/credit_report', JSON.stringify({}), function(res){
                if (res && res.length) {
                    var custs = WPOS.customers.getCustomers();
                    var byId = {};
                    res.forEach(function(r){ byId[r.id] = r; });
                    for (var k in custs) {
                        if (byId[k]) { custs[k].open_balance = byId[k].open_balance; custs[k].credit_limit = custs[k].credit_limit || byId[k].credit_limit; }
                    }
                }
                reloadCustomerTable();
            });
        } catch(e) { reloadCustomerTable(); }
        return;
    }
    function reloadCustomerTable(){
        var itemarray = [];
        var data = WPOS.customers.getCustomers();
        for (var key in data){
            itemarray.push(data[key]);
        }
        datatable.fnClearTable(false);
        datatable.fnAddData(itemarray, false);
        datatable.api().draw(false);
    }
    function exportCustomers(){

        var filename = "customers-"+WPOS.util.getDateFromTimestamp(new Date());
        filename = filename.replace(" ", "");
        var customers = WPOS.customers.getCustomers();

        var data = {};
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id }).join(',').split(',');

        if (ids && ids.length > 0 && ids[0]!='') {
            for (var i = 0; i < ids.length; i++) {
                var id = ids[i];
                if (customers.hasOwnProperty(id))
                    data[id] = customers[id];
            }
        } else {
            data = customers;
        }

        var csv = WPOS.data2CSV(
            ['ID', 'Name', 'Email', 'Phone', 'Address', 'Suburb', 'State', 'Country', 'Credit Limit', 'Due Days', 'Credit Enabled', 'Notes', 'Contacts'],
            ['id', 'name', 'email', 'phone', 'address', 'suburb', 'state', 'country', 'credit_limit', 'credit_due_days', 'credit_enabled', 'notes', 'contacts'],
            data
        );

        WPOS.initSave(filename, csv);
    }
</script>
<style type="text/css">
    #customertable_processing {
        display: none;
    }
</style>