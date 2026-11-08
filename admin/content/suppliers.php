<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Suppliers
    </h1>
    <button onclick="$('#addsupdialog').dialog('open');" id="addbtn" class="btn btn-primary btn-sm pull-right"><i class="icon-pencil align-top bigger-125"></i>Add</button>
    <button onclick="openSupplierPurchase();" class="btn btn-info btn-sm pull-right" style="margin-right:8px;"><i class="icon-cart-arrow-down align-top bigger-125"></i>New Purchase</button>
    <div id="supplier-balances" style="display:none; clear:both; margin:10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:10px;"></div>
</div><!-- /.page-header -->

<div style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #e3e3d8;">
    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
        <strong style="font-size:13px;">Supplier overview</strong>
        <span id="sup-kpis" style="display:flex; flex-wrap:wrap; gap:8px;"></span>
        <span style="margin-left:auto;"><button class="btn btn-xs btn-default" onclick="loadSupplierDashboard();"><i class="icon-refresh"></i> Refresh</button></span>
    </div>
    <div id="sup-cards" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;"></div>
</div>

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="row">
<div class="col-xs-12">

<div class="table-header">
    Manage your suppliers
</div>

<table id="supplierstable" class="table table-striped table-bordered table-hover dt-responsive" style="width: 100%;">
<thead>
<tr>
    <th data-priority="0" class="center noexport">
        <label>
            <input type="checkbox" class="ace" />
            <span class="lbl"></span>
        </label>
    </th>
    <th data-priority="4">ID</th>
    <th data-priority="2">Name</th>
    <th data-priority="3"># Items</th>
    <th data-priority="3">Open Payable</th>
    <th data-priority="3">Credit Limit</th>
    <th data-priority="1" class="noexport"></th>
</tr>
</thead>

<tbody>

</tbody>
</table>

</div>
</div>

</div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<div id="editsupdialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Name:&nbsp;</label></td>
            <td><input id="suppliername" type="text"/>
                <input id="supplierid" type="hidden"/></td>
        </tr>
        <tr><td style="text-align:right;"><label>Phone:&nbsp;</label></td><td><input id="supplierphone" type="text"/></td></tr>
        <tr><td style="text-align:right;"><label>Email:&nbsp;</label></td><td><input id="supplieremail" type="text"/></td></tr>
        <tr><td style="text-align:right;"><label>Address:&nbsp;</label></td><td><input id="supplieraddress" type="text"/></td></tr>
        <tr><td colspan="2"><label><input id="supplier-credit-enabled" type="checkbox"/> Approved for supplier credit</label></td></tr>
        <tr><td style="text-align:right;"><label>Credit limit&nbsp;</label></td><td><input id="supplier-credit-limit" type="text" style="width:110px;"/></td></tr>
        <tr><td style="text-align:right;"><label>Due in days&nbsp;</label></td><td><input id="supplier-credit-days" type="text" style="width:80px;"/></td></tr>
        <tr><td style="text-align:right;"><label>Payable:&nbsp;</label></td><td><span id="supplier-payable">—</span> <button type="button" class="btn btn-xs btn-primary" onclick="window.open('/api/supplier_statement.php?supplierid='+$('#supplierid').val(),'_blank')">Statement PDF</button> <button type="button" class="btn btn-xs btn-success" onclick="paySupplierDebt($('#supplierid').val())">Pay</button> <button type="button" class="btn btn-xs btn-info" onclick="openSupplierPurchase($('#supplierid').val())">Buy</button></td></tr>
    </table>
    <div id="paydialog" class="hide" style="padding:12px; min-width:380px;">
        <div style="margin-bottom:8px; font-size:12px; color:#666;">Pay <strong id="pay-who"></strong> — money leaves the account below and lands on the supplier ledger.</div>
        <label>Amount (KSh): <input id="pay-amount" type="text" style="width:110px;"/></label>
        <span id="pay-owed" style="font-size:12px; color:#a35c00; font-weight:700;"></span><br/>
        <label>From account: <select id="pay-account" style="width:240px;"></select></label><br/>
        <label>Method note: <input id="pay-note" type="text" style="width:200px;" placeholder="e.g. bank transfer ref"/></label><br/>
        <div style="margin-top:10px;text-align:center;">
            <button class="btn btn-success btn-sm" onclick="submitSupplierPay();">Pay now</button>
            <button class="btn btn-default btn-sm" onclick="$('#paydialog').dialog('close')">Cancel</button>
        </div>
        <h5 style="margin-top:12px;">Recent payments</h5>
        <div id="pay-history" style="max-height:140px; overflow:auto; font-size:12px;">—</div>
    </div>
    <div id="purchasedialog" class="hide" style="padding:12px; min-width:380px;">
        <label>Supplier: <select id="pur-supplier" style="width:220px;"></select></label><br/>
        <label>Product: <select id="pur-item" style="width:280px;"></select></label><br/>
        <label>Qty: <input id="pur-qty" type="text" style="width:70px;" value="10"/></label>
        <label>Unit cost (KSh): <input id="pur-cost" type="text" style="width:90px;" value="100"/></label><br/>
        <label>Paid now (KSh): <input id="pur-paid" type="text" style="width:90px;" value="0"/></label> <small>0 = all on credit</small><br/>
        <label>From account: <select id="pur-account" style="width:220px;"></select></label><br/>
        <div style="margin-top:10px;text-align:center;">
            <button class="btn btn-success btn-sm" onclick="submitSupplierPurchase()">Save Purchase</button>
            <button class="btn btn-default btn-sm" onclick="$('#purchasedialog').dialog('close')">Cancel</button>
        </div>
    </div>
</div>
<div id="addsupdialog" class="hide">
    <table>
       <tr>
           <td style="text-align: right;"><label>Name:&nbsp;</label></td>
           <td><input id="newsuppliername" type="text"/><br/></td>
       </tr>
    </table>
</div>

<!-- page specific plugin scripts; migrated to index.php due to heavy use -->

<!-- inline scripts related to this page -->
<script type="text/javascript">
    var suppliers = null;
    var datatable;
    $(function() {
        suppliers = WPOS.getJsonData("suppliers/get");
        var suparray = [];
        var supitem;
        for (var key in suppliers){
            supitem = suppliers[key];
            suparray.push(supitem);
        }
        datatable = $('#supplierstable').dataTable({
            "bProcessing": true,
            "aaData": suparray,
            "aaSorting": [[ 2, "asc" ]],
            "aoColumns": [
                { mData:null, sDefaultContent:'<div style="text-align: center"><label><input class="ace dt-select-cb" type="checkbox"><span class="lbl"></span></label><div>', bSortable: false, sClass:"noexport" },
                { "mData":"id" },
                { "mData":"name" },
                { "mData": "numitems"},
                { mData:null, mRender:function(d){ var bal = d && d.open_balance ? parseFloat(d.open_balance) : 0; if (bal<=0.005) return '<span style="color:#888;">—</span>'; return '<span style="color:#a35c00;font-weight:700;">'+WPOS.util.money(bal)+'</span>'; } },
                { mData:null, mRender:function(d){ var lim = d && d.credit_limit ? parseFloat(d.credit_limit) : 0; return lim>0 ? WPOS.util.money(lim) : '<span style="color:#888;">—</span>'; } },
                { mData:null, sDefaultContent:'<div class="action-buttons"><a class="green" onclick="openeditsupdialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-pencil bigger-130"></i></a><a class="red" onclick="removeSupplier($(this).closest(\'tr\').find(\'td\').eq(1).text())"><i class="icon-trash bigger-130"></i></a></div>', "bSortable": false, sClass: "noexport" }
            ],
            "columns": [
                {},
                {type: "numeric"},
                {type: "string"},
                {type: "numeric"},
                {type: "currency"},
                {type: "currency"},
                {}
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Add selected row count to footer
                var selected = this.api().rows('.selected').count();
                return sPre+(selected>0 ? '<br/>'+selected+' row(s) selected <span class="action-buttons"><a class="red" onclick="removeSelectedSuppliers();"><i class="icon-trash bigger-130"></i></a></span>':'');
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

        // dialogs
        $( "#addsupdialog" ).removeClass('hide').dialog({
                resizable: false,
                width: 'auto',
                modal: true,
                autoOpen: false,
                title: "Add Supplier",
                title_html: true,
                buttons: [
                    {
                        html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                        "class" : "btn btn-success btn-xs",
                        click: function() {
                            saveSupplier(true);
                        }
                    }
                    ,
                    {
                        html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                        "class" : "btn btn-xs",
                        click: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                ],
                create: function( event, ui ) {
                    // Set maxWidth
                    $(this).css("maxWidth", "375px");
                }
        });
        $( "#editsupdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Edit Supplier",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Update",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveSupplier(false);
                    }
                }
                ,
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "375px");
            }
        });
        loadSupplierDashboard();
        // Enrich rows with open payables (best-effort, mirrors Customers)
        try {
            WPOS.sendJsonDataAsync('suppliers/credit_report', JSON.stringify({}), function(res){
                if (res && res.length) {
                    res.forEach(function(r){ if (suppliers[r.id]) { suppliers[r.id].open_balance = r.open_balance; suppliers[r.id].next_due = r.next_due || null; } });
                    reloadTable();
                }
            });
        } catch(e) {}
        // hide loader
        WPOS.util.hideLoader();
    });
    // updating records
    function openeditsupdialog(id){
        var item = suppliers[id];
        $("#supplierid").val(item.id);
        $("#suppliername").val(item.name);
        try {
            $("#supplierphone").val(item.phone || '');
            $("#supplieremail").val(item.email || '');
            $("#supplieraddress").val(item.address || '');
            $("#supplier-credit-enabled").prop("checked", !!item.credit_enabled);
            $("#supplier-credit-limit").val(item.credit_limit || 0);
            $("#supplier-credit-days").val(item.credit_due_days || 7);
            refreshSupplierPayable(id);
        } catch(e) {}
        $("#editsupdialog").dialog("open");
    }
    function loadSupplierDashboard(){
        var kpi = $('#sup-kpis'), cards = $('#sup-cards');
        if (!kpi.length) return;
        var renderCards = function(rep){
            var html = '';
            (rep || []).forEach(function(s){
                if ((parseFloat(s.open_balance||0) > 0.005) || (parseFloat(s.total_paid||0) > 0) || (parseFloat(s.total_all_purchased||0) > 0)) {
                    var od = parseFloat(s.overdue||0) > 0.005;
                    var due = s.next_due ? new Date(s.next_due).toLocaleDateString('en-KE', {day:'2-digit',month:'short',year:'numeric'}) : '—';
                    var dueCol = od ? '#c00;font-weight:800;' : '';
                    var lim = parseFloat(s.credit_limit||0) > 0 ? ('L ' + WPOS.util.money(s.credit_limit) + ' · avail ' + WPOS.util.money(s.available)) : 'No credit limit';
                    html += '<div style="width:300px; background:#fff; border:1px solid #ddd; border-radius:10px; padding:12px;">'
                        + '<div style="display:flex; align-items:center;"><strong style="font-size:13px;">' + (s.name || ('#'+s.id)) + '</strong>'
                        + (od ? '<span class="label label-danger" style="margin-left:6px;">OVERDUE</span>' : '') + '</div>'
                        + '<div style="margin:8px 0 4px; font-size:20px; font-weight:800; color:#a35c00;">' + WPOS.util.money(s.open_balance) + ' <span style="font-size:11px; color:#888; font-weight:600;">owed</span></div>'
                        + '<div style="font-size:12px; color:#555;">Paid <b>' + WPOS.util.money(s.total_paid) + '</b> · Purchased <b>' + WPOS.util.money(s.total_all_purchased) + '</b></div>'
                        + '<div style="font-size:12px; color:#555;">Open <b>' + (s.open_purchases || 0) + '</b> purchases · Due <span style="' + dueCol + '">' + (od ? 'was ' : '') + due + '</span></div>'
                        + '<div style="font-size:11px; color:#888;">' + lim + '</div>'
                        + '<div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">'
                        + '<button class="btn btn-xs btn-primary" onclick="openSupplierPayDialog(' + s.id + ')">Pay</button>'
                        + '<button class="btn btn-xs btn-default" onclick="window.open(\'/api/supplier_statement.php?supplierid=' + s.id + '\',\'_blank\')">Statement</button>'
                        + '<button class="btn btn-xs btn-info" onclick="openSupplierPurchase(' + s.id + ')">Buy</button>'
                        + '</div></div>';
                }
            });
            cards.html(html || '<div style="color:#888; font-size:12px;">No suppliers with balances yet.</div>');
        };
        try {
            WPOS.sendJsonDataAsync('suppliers/dashboard', JSON.stringify({}), function(d){
                if (!d) return;
                var c = function(l, v, sub){ return '<div style="background:#f7f8f5; border:1px solid #ddd; border-radius:10px; padding:8px 12px; min-width:130px;"><div style="font-size:11px; color:#666;">' + l + '</div><div style="font-size:16px; font-weight:800;">' + v + '</div>' + (sub ? '<div style="font-size:11px; color:#888;">' + sub + '</div>' : '') + '</div>'; };
                kpi.html(
                    c('Owed now', WPOS.util.money(d.owed), (d.debtors || 0) + ' suppliers owed')
                    + c('Overdue', WPOS.util.money(d.overdue), 'due already')
                    + c('Due in 7 days', WPOS.util.money(d.due_7d), 'coming up')
                    + c('Paid (30d)', WPOS.util.money(d.paid_30d || 0), 'to suppliers')
                    + c('Purchased (30d)', WPOS.util.money(d.purchases_30d), '')
                );
            }, function(){ kpi.html('<span style="color:#999; font-size:12px;">Totals unavailable.</span>'); });
            WPOS.sendJsonDataAsync('suppliers/credit_report', JSON.stringify({}), function(rep){
                if (rep) renderCards(rep);
            }, function(){});
        } catch(e) {}
    }
    function refreshSupplierPayable(id){
        var el = $("#supplier-payable");
        el.text('Loading…');
        WPOS.sendJsonDataAsync('suppliers/credit', JSON.stringify({supplierid: id}), function(res){
            if (!res || !res.entries) { el.text('KSh 0.00'); return; }
            el.html(WPOS.util.money(res.open_balance || 0) + ' owed · ' + res.entries.length + ' open');
        });
    }
    function openSupplierPayDialog(id){
        var sel = $('#pay-account');
        var accts = WPOS.sendJsonData('accounts/list', JSON.stringify({})) || [];
        sel.html('<option value="">— no account movement (manual note only) —</option>');
        accts.forEach(function(a){ sel.append('<option value="' + a.id + '">' + a.name + ' (bal ' + WPOS.util.money(a.balance) + ')</option>'); });
        $('#paydialog').data('supplierid', id);
        $('#pay-who').text('supplier #'+id);
        $('#pay-amount').val('');
        $('#pay-note').val('');
        $('#pay-owed').text('');
        $('#pay-history').text('Loading…');
        WPOS.sendJsonDataAsync('suppliers/credit', JSON.stringify({supplierid: id}), function(res){
            if (!res) { $('#pay-owed').text(''); return; }
            $('#pay-owed').text(WPOS.util.money(res.open_balance || 0) + ' currently owed');
            $('#pay-account').val(accts.length ? accts[0].id : '');
            var hp = (res.payments || []).slice(0, 10);
            $('#pay-history').html(hp.length ? hp.map(function(x){
                return '<div>' + new Date(x.processdt).toLocaleString('en-KE', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) + ' · <b>' + WPOS.util.money(x.amount) + '</b> · from ' + (x.account || '—') + '</div>';
            }).join('') : 'No payments recorded yet.');
        }, function(){});
        var dlg = $('#paydialog');
        if (!dlg.hasClass('ui-dialog-content')) dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '460px', modal: true, autoOpen: false, title: 'Pay supplier' });
        dlg.dialog('open');
    }
    function paySupplierDebt(id){ openSupplierPayDialog(id); }
    function submitSupplierPay(){
        var id = parseInt($('#paydialog').data('supplierid') || $('#supplierid').val(), 10);
        var amt = parseFloat($('#pay-amount').val() || 0);
        var accId = parseInt($('#pay-account').val() || '0', 10) || 0;
        var note = ($('#pay-note').val() || '').trim();
        if (!id || !amt || amt <= 0) { alert('Enter amount > 0'); return; }
        WPOS.util.showLoader();
        var r = WPOS.sendJsonData('suppliers/credit_pay', JSON.stringify({supplierid: id, amount: amt, method: note || 'mpesa', account_id: accId, note: note}));
        WPOS.util.hideLoader();
        if (r === false) { alert('Payment failed'); return; }
        $('#paydialog').dialog('close');
        alert('Paid ' + WPOS.util.money(amt) + (accId ? ' from account' : '') + ' to supplier');
        refreshSupplierPayable(id);
        reloadData();
    }
    function openSupplierPurchase(preId){
        var supSel = $('#pur-supplier'), itemSel = $('#pur-item');
        if (!supSel.length) return;
        if (!supSel.data('filled')) {
            supSel.html('');
            for (var k in suppliers) supSel.append('<option value="' + suppliers[k].id + '">' + suppliers[k].name + '</option>');
            var itemsData = WPOS.getJsonData('items/get') || {};
            itemSel.html('');
            for (var i in itemsData) itemSel.append('<option value="' + itemsData[i].id + '" data-name="' + (itemsData[i].name || '').replace(/"/g,'&quot;') + '">' + (itemsData[i].name || itemsData[i].id) + '</option>');
            var accSel2 = $('#pur-account');
            accSel2.html('<option value="">— no account movement (all on credit) —</option>');
            var accts2 = WPOS.sendJsonData('accounts/list', JSON.stringify({})) || [];
            accts2.forEach(function(a){ accSel2.append('<option value="' + a.id + '">' + a.name + ' (bal ' + WPOS.util.money(a.balance) + ')</option>'); });
            supSel.data('filled', 1);
        }
        if (preId) supSel.val(preId);
        var dlg = $('#purchasedialog');
        if (!dlg.hasClass('ui-dialog-content')) {
            dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '460px', modal: true, autoOpen: false, title: 'New Supplier Purchase' });
        }
        dlg.dialog('open');
    }
    function submitSupplierPurchase(){
        var sid = parseInt($('#pur-supplier').val(), 10);
        var itemOpt = $('#pur-item option:selected');
        var itemName = itemOpt.attr('data-name') || itemOpt.text();
        var itemId = parseInt(itemOpt.val(), 10) || 0;
        var qty = parseInt($('#pur-qty').val() || '0', 10);
        var cost = parseFloat($('#pur-cost').val() || '0');
        var paid = parseFloat($('#pur-paid').val() || '0');
        if (!sid || !qty || qty <= 0 || !cost || cost <= 0) { alert('Pick supplier, product, qty and cost'); return; }
        WPOS.util.showLoader();
        var accId2 = parseInt($('#pur-account').val() || '0', 10) || 0;
        if (paid > 0 && accId2 <= 0) { alert('Pick the account the money leaves from'); return; }
        var r = WPOS.sendJsonData('suppliers/purchase', JSON.stringify({supplierid: sid, items: [{name: itemName, storeditemid: itemId, qty: qty, cost: cost}], paid_immediate: paid, method: 'mpesa', account_id: accId2}));
        WPOS.util.hideLoader();
        if (r === false) { alert('Purchase failed — check credit approval/limit'); return; }
        $('#purchasedialog').dialog('close');
        alert('Purchase ' + r.ref + ' — ' + WPOS.util.money(r.total) + ' (' + WPOS.util.money(r.on_credit) + ' on credit)');
        reloadData();
    }
    function saveSupplier(isnewitem){
        // show loader
        WPOS.util.showLoader();
        var item = {}, result;
        if (isnewitem){
            // adding a new supplier
            var name_field = $("#newsuppliername");
            item.name = name_field.val();
            result = WPOS.sendJsonData("suppliers/add", JSON.stringify(item));
            if (result!==false){
                if (!result || !result.id) {
                    // Malformed response — refetch full list instead of inserting a bad row
                    suppliers = WPOS.getJsonData("suppliers/get") || suppliers;
                    reloadTable();
                    name_field.val('');
                    $("#addsupdialog").dialog("close");
                } else {
                    // Normalize so DataTables always sees an object row with id
                    var row = {};
                    for (var kk in result) row[kk] = result[kk];
                    if (row.open_balance === undefined) row.open_balance = 0;
                    suppliers[row.id] = row;
                    reloadTable();
                    name_field.val('');
                    $("#addsupdialog").dialog("close");
                }
            }
        } else {
            // updating an item
            item.id = $("#supplierid").val();
            item.name = $("#suppliername").val();
            try {
                item.phone = $("#supplierphone").val() || '';
                item.email = $("#supplieremail").val() || '';
                item.address = $("#supplieraddress").val() || '';
                item.credit_enabled = $("#supplier-credit-enabled").is(":checked") ? 1 : 0;
                item.credit_limit = parseFloat($("#supplier-credit-limit").val() || 0) || 0;
                item.credit_due_days = parseInt($("#supplier-credit-days").val() || 7, 10) || 7;
            } catch(e) {}
            result = WPOS.sendJsonData("suppliers/edit", JSON.stringify(item));
            if (result!==false){
                suppliers[result.id] = result;
                reloadTable();
                $("#editsupdialog").dialog("close");
            }
        }
        // hide loader
        WPOS.util.hideLoader();
    }
    function removeSupplier(id){

        var answer = confirm("Are you sure you want to delete this supplier?");
        if (answer){
            // show loader
            WPOS.util.hideLoader();
            if (WPOS.sendJsonData("suppliers/delete", '{"id":'+id+'}')){
                delete suppliers[id];
                reloadTable();
            }
            // hide loader
            WPOS.util.hideLoader();
        }
    }

    function removeSelectedSuppliers(){
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id });

        var answer = confirm("Are you sure you want to delete "+ids.length+" selected items?");
        if (answer){
            // show loader
            WPOS.util.hideLoader();
            if (WPOS.sendJsonData("suppliers/delete", '{"id":"'+ids.join(",")+'"}')){
                for (var i=0; i<ids.length; i++){
                    delete suppliers[ids[i]];
                }
                reloadTable();
            }
            // hide loader
            WPOS.util.hideLoader();
        }
    }

    function reloadData(){
        suppliers = WPOS.getJsonData("suppliers/get");
        try {
            WPOS.sendJsonDataAsync('suppliers/credit_report', JSON.stringify({}), function(res){
                if (res && res.length) {
                    res.forEach(function(r){ if (suppliers[r.id]) { suppliers[r.id].open_balance = r.open_balance; suppliers[r.id].credit_limit = r.credit_limit; } });
                }
                reloadTable();
            });
        } catch(e) { reloadTable(); }
        return;
    }
    function reloadTable(){
        var suparray = [];
        var tempsup;
        for (var key in suppliers){
            tempsup = suppliers[key];
            if (tempsup && typeof tempsup === 'object' && tempsup.id !== undefined) suparray.push(tempsup);
        }
        datatable.fnClearTable(false);
        datatable.fnAddData(suparray, false);
        datatable.api().draw(false);
    }
</script>
<style type="text/css">
    #supplierstable_processing {
        display: none;
    }
</style>