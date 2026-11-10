<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 class="inline">
        Item Inventory
    </h1>
    <button onclick="openAddStockDialog();" id="addbtn" class="btn btn-primary btn-sm pull-right"><i class="icon-pencil align-top bigger-125"></i>Add</button>
    <button class="btn btn-info btn-sm pull-right" style="margin-right: 10px;" onclick="openBulkSupplier();"><i class="icon-truck align-top bigger-125"></i>Assign supplier to selected</button>
    <button class="btn btn-success btn-sm pull-right" style="margin-right: 10px;" onclick="exportStock();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
</div><!-- /.page-header -->

<div style="margin-bottom:10px;">
    <div class="table-header">Manage your product inventory</div>
    <table id="stocktable" class="table table-striped table-bordered table-hover dt-responsive" style="width:100%;">
        <thead>
            <tr>
                <th data-priority="0" class="center">
                    <label>
                        <input type="checkbox" class="ace" />
                        <span class="lbl"></span>
                    </label>
                </th>
                <th data-priority="2">Name</th>
                <th data-priority="5">Supplier</th>
                <th data-priority="6">Unit cost</th>
                <th data-priority="3">Location</th>
                <th data-priority="4">Qty</th>
                <th data-priority="1" class="noexport"></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div style="margin:10px 0 8px; border-top:1px solid #e3e3d8; padding-top:8px;"><strong style="font-size:12px;">Inventory overview</strong> <span style="font-size:11px; color:#888;">value · movements · alerts</span></div>
<div style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #e3e3d8;">
    <div id="stockKpis" style="margin-bottom:2px;"></div>
</div>
<div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #e3e3d8;">
    <div style="flex:1 1 280px; min-width:280px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-bar-chart"></i> Stock value by supplier</h4></div>
            <div class="widget-body"><div class="cb-scroll" style="max-height:210px;"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:400px;"><thead><tr><th>Supplier</th><th style="text-align:right;">Value</th><th style="text-align:right;">Units</th><th style="text-align:center;">SKUs</th></tr></thead><tbody id="bySupplierBody"><tr><td colspan="4"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
    <div style="flex:1 1 280px; min-width:280px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-exchange"></i> Stock movements</h4></div>
            <div class="widget-body"><div class="cb-scroll" style="max-height:210px;"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:400px;"><thead><tr><th>Period</th><th style="text-align:right;">Units in</th><th style="text-align:right;">Units out</th><th style="text-align:center;">Moves</th></tr></thead><tbody id="movesBody"><tr><td colspan="4"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
    <div style="flex:1 1 280px; min-width:280px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-warning-sign orange"></i> Low stock (&le; alert level)</h4></div>
            <div class="widget-body"><div class="cb-scroll" style="max-height:210px;"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:420px;"><thead><tr><th>Item</th><th>Supplier</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Alert &le;</th><th></th></tr></thead><tbody id="lowStockBody"><tr><td colspan="5"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
</div>

<div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #e3e3d8;">
    <div style="flex:1 1 420px; min-width:280px; max-width:520px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-time"></i> Recent movements</h4></div>
            <div class="widget-body"><div class="cb-scroll" style="max-height:210px;"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:420px;"><thead><tr><th>When</th><th>Item</th><th>Type</th><th style="text-align:right;">Qty</th><th>Loc</th></tr></thead><tbody id="recentMovesBody"><tr><td colspan="5"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
</div>
<div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #e3e3d8;">
    <div style="flex:1 1 420px; min-width:280px; max-width:520px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-trophy"></i> Top 5 items by stock value</h4></div>
            <div class="widget-body"><div id="topValueChart" style="padding:8px;"></div></div></div>
    </div>
    <div style="flex:1 1 420px; min-width:280px; max-width:520px;">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-map-marker"></i> Value by location</h4></div>
            <div class="widget-body"><div id="byLocChart" style="padding:8px;"></div></div></div>
    </div>
</div>

<div id="editstockdialog" class="hide">
    <table>
        <tr>
            <input type="hidden" id="setstockitemid" />
            <input type="hidden" id="setstocklocid" />
            <td style="text-align: right;"><label>Qty:&nbsp;</label></td>
            <td><input id="setstockqty" type="text" value="1" style="width:80px; display:inline-block;"/> <select id="setstockunit" style="width:90px; display:inline-block;"><option>Pcs</option><option>Kgs</option><option>Grams</option></select> <button type="button" class="btn btn-xs btn-default" onclick="addCustomStockUnit('setstockunit');">+</button></td>
        </tr>
    </table>
</div>
<div id="transferstockdialog" class="hide">
    <table>
        <tr>
            <input type="hidden" id="tstockitemid" />
            <input type="hidden" id="tstocklocid" />
            <td style="text-align: right;"><label>Transfer to:&nbsp;</label></td>
            <td><select id="tstocknewlocid" class="locselect">
                </select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Qty:&nbsp;</label></td>
            <td><input id="tstockqty" type="text" value="1" style="width:80px; display:inline-block;"/> <select id="tstockunit" style="width:90px; display:inline-block;"><option>Pcs</option><option>Kgs</option><option>Grams</option></select> <button type="button" class="btn btn-xs btn-default" onclick="addCustomStockUnit('tstockunit');">+</button></td>
        </tr>
    </table>
</div>
<div id="addstockdialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Item:</label></td>
            <td><select id="addstockitemid" class="itemselect" onchange="onAddStockItemChange();">
                </select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Location:</label></td>
            <td><select id="addstocklocid" class="locselect">
            </select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Qty:&nbsp;</label></td>
            <td><input id="addstockqty" type="text" value="1" style="width:80px; display:inline-block;"/> <select id="addstockunit" style="width:90px; display:inline-block;"><option>Pcs</option><option>Kgs</option><option>Grams</option></select> <button type="button" class="btn btn-xs btn-default" onclick="addCustomStockUnit('addstockunit');">+</button></td>
        </tr>
        <tr><td colspan="2" style="border-top:1px solid #ddd; padding-top:6px;"><strong>Purchase &amp; money (optional)</strong></td></tr>
        <tr>
            <td style="text-align: right;"><label>Supplier:&nbsp;</label></td>
            <td><select id="addstocksupplier" style="width:220px;"></select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Unit cost (KSh):&nbsp;</label></td>
            <td><input id="addstockcost" type="text" value="0" style="width:90px;"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Pay now (KSh):&nbsp;</label></td>
            <td><input id="addstockpaid" type="text" value="0" style="width:90px;"/> <small>0 = all on supplier credit</small></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>From account:&nbsp;</label></td>
            <td><select id="addstockaccount" style="width:220px;"></select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Notes:&nbsp;</label></td>
            <td><input id="addstocknotes" type="text" style="width:220px;" placeholder="Invoice no / LPO"/></td>
        </tr>
    </table>
</div>
<div id="bulksupplierdialog" class="hide" style="padding:14px; min-width:320px;">
    <label>Supplier:&nbsp;<select id="bulk-supplier" style="width:220px;"></select></label><br/>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="submitBulkSupplier();">Assign</button>
        <button class="btn btn-default btn-sm" onclick="$('#bulksupplierdialog').dialog('close')">Cancel</button>
    </div>
</div>
<div id="stockhistdialog" class="hide">

    <div style="width: 100%; overflow-x: auto;">
    <table class="table table-responsive table-stripped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Location</th>
                <th>Type</th>
                <th>Amount</th>
                <th>DT</th>
            </tr>
        </thead>
        <tbody id="stockhisttable">

        </tbody>
    </table>
    </div>
</div>

<!-- page specific plugin scripts; migrated to index.php due to heavy use -->

<!-- inline scripts related to this page -->
<script type="text/javascript">
    var stock = null;
    var items = null;
    function ensureStockUnitOption(selectId, unit){
        if (!unit) return;
        var sel = document.getElementById(selectId);
        if (!sel) return;
        var opts = Array.prototype.map.call(sel.options, function(o){ return o.value; });
        if (opts.indexOf(unit) === -1) { var o = document.createElement('option'); o.value = unit; o.textContent = unit; sel.appendChild(o); }
    }
    function addCustomStockUnit(selectId){
        var u = prompt('Enter a new unit of measurement (e.g. Litres, Boxes):');
        if (!u) return;
        u = u.trim();
        if (!u) return;
        ensureStockUnitOption(selectId, u);
        document.getElementById(selectId).value = u;
    }
    var datatable;
    var STOCK_STATS = null;
    // Clearfix for the custom DataTables layout (float containment without the theme's .row class)
    try {
        var _cbSt = document.createElement('style');
        _cbSt.textContent = "#stocktable{clear:none !important;} .cb-dt-bar{display:block;} .cb-dt-len,.cb-dt-filter{display:inline-block;width:49%;vertical-align:top;} .cb-dt-filter{text-align:right;} .cb-dt-foot{display:block;margin-top:6px;} .cb-dt-info,.cb-dt-pag{display:inline-block;vertical-align:top;} .cb-dt-pag{float:right;} .cb-dt-info{float:left;} .cb-dt-foot:after{content:'';display:table;clear:both;}";
        (document.head || document.getElementsByTagName('head')[0] || document.documentElement).appendChild(_cbSt);
    } catch(e) {}
    var SUPPLIERS_LIST = {};
    function itemExtra(itemid){
        if (STOCK_STATS && STOCK_STATS.items) {
            for (var i = 0; i < STOCK_STATS.items.length; i++) if (STOCK_STATS.items[i].id == itemid) return STOCK_STATS.items[i];
        }
        return { supplierid: 0, cost: 0, lowlevel: 10 };
    }
    function supplierSelect(data){
        var itemid = data.storeditemid;
        var cur = itemExtra(itemid).supplierid || 0;
        var opts = '<option value="0">— none —</option>';
        for (var k in SUPPLIERS_LIST) {
            opts += '<option value="' + SUPPLIERS_LIST[k].id + '"' + (SUPPLIERS_LIST[k].id == cur ? ' selected' : '') + '>' + (SUPPLIERS_LIST[k].name || SUPPLIERS_LIST[k].id) + '</option>';
        }
        return '<select style="width:150px; font-size:12px;" onchange="saveItemSupplier(' + itemid + ', this.value);">' + opts + '</select>';
    }
    function saveItemSupplier(itemid, supplierid){
        WPOS.util.showLoader();
        var r = WPOS.sendJsonData('items/setSupplier', JSON.stringify({ itemid: itemid, supplierid: parseInt(supplierid, 10) || 0 }));
        WPOS.util.hideLoader();
        if (r === false) { reloadTable(); return; }
        loadStockStats();
    }
    function editCost(itemid){
        var ex = itemExtra(itemid);
        var c = prompt('Unit cost (KSh) — updates stock value. Current: ' + ex.cost.toFixed(2), ex.cost);
        if (c === null) return;
        var ll = prompt('Low-stock alert level (units). Current: ' + ex.lowlevel, ex.lowlevel);
        if (ll === null) return;
        WPOS.util.showLoader();
        var r = WPOS.sendJsonData('items/setCost', JSON.stringify({ itemid: itemid, cost: parseFloat(c || 0) || 0, lowlevel: parseInt(ll || '10', 10) || 10 }));
        WPOS.util.hideLoader();
        if (r !== false) { loadStockStats(); reloadTable(); }
    }
    function gotoAddStock(itemid){
        openAddStockDialog();
        $('#addstockitemid').val(String(itemid));
    }
    function renderStockStats(s){
        if (!s) return;
        var k = s.kpis || {};
        var cards = ''
          + '<div style="display:inline-block; min-width:140px; margin:0 10px 10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#666;">SKUs</div><div style="font-size:20px; font-weight:800;">' + (k.skus || 0) + '</div></div>'
          + '<div style="display:inline-block; min-width:140px; margin:0 10px 10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#666;">Units on hand</div><div style="font-size:20px; font-weight:800;">' + (k.units || 0) + '</div></div>'
          + '<div style="display:inline-block; min-width:170px; margin:0 10px 10px 0; background:#1a1a1a; color:#fff; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#ccc;">Stock value</div><div style="font-size:20px; font-weight:800; color:#84cc16;">' + WPOS.util.money(k.stock_value) + '</div><div style="font-size:11px; color:#aaa;">Σ qty × unit cost</div></div>'
          + '<div style="display:inline-block; min-width:140px; margin:0 10px 10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#666;">Out of stock</div><div style="font-size:20px; font-weight:800; color:' + ((k.out_of_stock||0) > 0 ? '#c00' : '#256029') + ';">' + (k.out_of_stock || 0) + '</div></div>'
          + '<div style="display:inline-block; min-width:140px; margin:0 10px 10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#666;">Low stock</div><div style="font-size:20px; font-weight:800; color:' + ((k.low_stock||0) > 0 ? '#a35c00' : '#256029') + ';">' + (k.low_stock || 0) + '</div></div>';
        $('#stockKpis').html(cards);
        var bs = '';
        (s.by_supplier || []).forEach(function(b){
            bs += '<tr><td>' + (b.supplier || '-') + '</td><td style="text-align:right; font-weight:700;">' + WPOS.util.money(b.value) + '</td><td style="text-align:right;">' + b.units + '</td><td style="text-align:center;">' + b.skus + '</td></tr>';
        });
        $('#bySupplierBody').html(bs || '<tr><td colspan="4" class="center">No stock yet.</td></tr>');
        var m7 = s.moves_7d || {}, m30 = s.moves_30d || {};
        var mv = ''
          + '<tr><td><strong>Last 7 days</strong></td><td style="text-align:right;">' + (m7.total_in || 0) + '</td><td style="text-align:right;">' + (m7.total_out || 0) + '</td><td style="text-align:center;">' + (m7.moves || 0) + '</td></tr>'
          + '<tr><td><strong>Last 30 days</strong></td><td style="text-align:right;">' + (m30.total_in || 0) + '</td><td style="text-align:right;">' + (m30.total_out || 0) + '</td><td style="text-align:center;">' + (m30.moves || 0) + '</td></tr>';
        (m7.by_type || []).forEach(function(b){ mv += '<tr><td style="color:#666;">· ' + (b.type || '-') + '</td><td style="text-align:right;">' + (b.in_units || 0) + '</td><td style="text-align:right;">' + (b.out_units || 0) + '</td><td style="text-align:center;">' + (b.moves || 0) + '</td></tr>'; });
        $('#movesBody').html(mv);
        var ls = '';
        (s.low_stock_list || []).forEach(function(b){
            ls += '<tr><td>' + (b.name || ('#' + b.itemid)) + '</td><td style="font-size:11px;">' + (b.supplier || '-') + '</td><td style="text-align:right; font-weight:800; color:#c00;">' + b.qty + '</td><td style="text-align:right;">' + b.lowlevel + '</td><td><button class="btn btn-xs btn-success" onclick="gotoAddStock(' + b.itemid + ');">Restock</button></td></tr>';
        });
        $('#lowStockBody').html(ls || '<tr><td colspan="5" class="center">All stocked above alert levels.</td></tr>');
        renderBarChart('#byLocChart', s.by_location || [], function(b){ return b.location; }, function(b){ return b.value; }, function(b){ return WPOS.util.money(b.value); });
        renderBarChart('#topValueChart', s.top_value || [], function(b){ return b.name; }, function(b){ return b.value; }, function(b){ return WPOS.util.money(b.value); });
        var rm = '';
        (s.recent_moves || []).forEach(function(m){
            rm += '<tr><td style="font-size:11px;">' + (m.dt || '—') + '</td><td>' + (m.item || '-') + '</td><td>' + (m.type || '-') + '</td><td style="text-align:right; font-weight:700;">' + (m.auxdir == 2 ? '-' : '+') + m.amount + '</td><td style="font-size:11px;">' + (m.location || '-') + '</td></tr>';
        });
        $('#recentMovesBody').html(rm || '<tr><td colspan="5" class="center">No movements yet.</td></tr>');
    }
    function renderBarChart(sel, data, labelFn, valFn, tipFn){
        var el = $(sel);
        if (!data || !data.length) { el.html('<div style="color:#999; font-size:12px;">No data.</div>'); return; }
        var max = 0; data.forEach(function(d){ max = Math.max(max, parseFloat(valFn(d)) || 0); });
        var html = '';
        data.forEach(function(d){
            var v = parseFloat(valFn(d)) || 0;
            var pct = max > 0 ? Math.max(2, Math.round(v / max * 100)) : 0;
            html += '<div class="cb-bar-row" style="margin:3px 0 5px;"><div style="font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><strong>' + labelFn(d) + '</strong> <span style="color:#888;">· ' + tipFn(d) + '</span></div>'
                + '<div style="background:#eee; border-radius:4px; height:10px; position:relative; margin-top:2px;"><div style="width:' + pct + '%; background:#84cc16; border-radius:4px; height:10px;"></div></div></div>';
        });
        el.html(html);
    }
    function loadStockStats(){
        STOCK_STATS = WPOS.sendJsonData('stock/stats', JSON.stringify({})) || null;
        renderStockStats(STOCK_STATS);
        if (datatable) datatable.api().draw(false);
    }
    function openBulkSupplier(){
        if (!(SUPPLIERS_LIST && Object.keys(SUPPLIERS_LIST).length)) {
            SUPPLIERS_LIST = WPOS.getJsonData('suppliers/get') || {};
        }
        var dlg = $('#bulksupplierdialog');
        var sel = $('#bulk-supplier');
        sel.html('<option value="">— pick supplier —</option>');
        for (var k in SUPPLIERS_LIST) sel.append('<option value="' + SUPPLIERS_LIST[k].id + '">' + (SUPPLIERS_LIST[k].name || SUPPLIERS_LIST[k].id) + '</option>');
        if (!dlg.hasClass('ui-dialog-content')) dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '420px', modal: true, autoOpen: false, title: 'Assign supplier to selected rows' });
        dlg.dialog('open');
    }
    function submitBulkSupplier(){
        var supId = parseInt($('#bulk-supplier').val() || '0', 10);
        if (!supId) { alert('Pick a supplier'); return; }
        var rows = datatable.api().rows('.selected').data().toArray();
        if (!rows.length) { alert('Tick some rows first'); return; }
        WPOS.util.showLoader();
        var failed = 0;
        rows.forEach(function(r){
            var res = WPOS.sendJsonData('items/setSupplier', JSON.stringify({ itemid: r.storeditemid, supplierid: supId }));
            if (res === false) failed++;
        });
        WPOS.util.hideLoader();
        $('#bulksupplierdialog').dialog('close');
        alert('Assigned supplier to ' + rows.length + ' item(s)' + (failed ? (' — ' + failed + ' failed') : ''));
        loadStockStats();
        reloadTable();
    }
    $(function() {
        SUPPLIERS_LIST = WPOS.getJsonData("suppliers/get") || {};
        loadStockStats();
        stock = WPOS.getJsonData("stock/get");
        items = WPOS.getJsonData("items/get");
        var stockarray = [];
        var tempstock;
        for (var key in stock){
            tempstock = stock[key];
            stockarray.push(tempstock);
        }
        datatable = $('#stocktable').dataTable({"bProcessing": true,
            // Float-free custom layout (the theme's grid rows inflate/compress unpredictably)
            "dom": "<'cb-dt-bar'<'cb-dt-len'l><'cb-dt-filter'f>>t<'cb-dt-foot'<'cb-dt-info'i><'cb-dt-pag'p>>",
            "aaData": stockarray,
            "aaSorting": [[ 2, "asc" ]],
            "aLengthMenu": [ 10, 25, 50, 100, 200],
            "aoColumns": [
                { mData:null, sDefaultContent:'<div style="text-align: center"><label><input class="ace dt-select-cb" type="checkbox"><span class="lbl"></span></label><div>', bSortable: false },
                { mData:function(data,type,val){return (data.name==null?"Unknown":data.name) } },
                { mData:function(data,type,val){return supplierSelect(data);}, bSortable: false },
                { mData:function(data,type,val){ var c = itemExtra(data.storeditemid); return '<span>' + WPOS.util.money(c.cost) + '</span> <a class="green" onclick="editCost(' + data.storeditemid + ');"><i class="icon-pencil bigger-120"></i></a>'; }, bSortable: false },
                { mData:function(data,type,val){return (data.locationid!=='0'?(WPOS.locations.hasOwnProperty(data.locationid)?WPOS.locations[data.locationid].name:'Unknown'):'Warehouse');} },
                { mData:function(data,type,val){ var it = (typeof items !== 'undefined' && items) ? items[data.storeditemid] : null; var u = (it && it.unit) ? it.unit : ''; return data.stocklevel + (u ? ' ' + u : ''); }, },
                { mData:function(data,type,val){return '<div class="action-buttons"><a class="green" onclick="openEditStockDialog('+data.id+');"><i class="icon-pencil bigger-130"></i></a><a class="blue" onclick="openTransferStockDialog('+data.id+')"><i class="icon-arrow-right bigger-130"></i></a><a class="red" onclick="getStockHistory('+data.storeditemid+', '+data.locationid+');"><i class="icon-time bigger-130"></i></a></div>'; }, "bSortable": false }
            ],
            "columns": [
                {},
                {type: "string"},
                {type: "string"},
                {type: "currency"},
                {type: "string"},
                {type: "numeric"},
                {}
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Add selected row count to footer
                var selected = this.api().rows('.selected').count();
                return sPre+(selected>0 ? '<br/>'+selected+' row(s) selected':'');
            }
        });

        // row selection checkboxes
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
        $( "#addstockdialog" ).removeClass('hide').dialog({
                resizable: false,
                width: 'auto',
                modal: true,
                autoOpen: false,
                title: "Add Stock",
                title_html: true,
                buttons: [
                    {
                        html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                        "class" : "btn btn-success btn-xs",
                        click: function() {
                            saveItem(1);
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
        $( "#editstockdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Edit Stock",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Update",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveItem(2);
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
        $( "#transferstockdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Transfer Stock",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Update",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveItem(3);
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
        $( "#stockhistdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            maxWidth: '700px',
            modal: true,
            autoOpen: false,
            title: "Stock History",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Close",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "700px");
            }
        });
        // fill location selects
        var locselect = $(".locselect");
        locselect.html('');
        for (key in WPOS.locations){
            if (key == 0){
                locselect.append('<option class="locid-0" value="0">Warehouse</option>');
            } else {
                locselect.append('<option class="locid-'+WPOS.locations[key].id+'" value="'+WPOS.locations[key].id+'">'+WPOS.locations[key].name+'</option>');
            }
        }

        // hide loader
        WPOS.util.hideLoader();
    });
    // updating records
    function getStockHistory(id, locationid){
        WPOS.util.showLoader();
        var stockhist = WPOS.sendJsonData("stock/history", JSON.stringify({storeditemid: id, locationid: locationid}));
        // populate stock dialog with list
        $("#stockhisttable").html("");
        var hist;
        for (var i in stockhist){
            hist = stockhist[i];
            $("#stockhisttable").append('<tr><td>'+hist.name+'</td><td>'+hist.location+'</td><td>'+hist.type+(hist.auxid!=-1?(hist.auxdir==1?" from ":" to ")+(hist.auxid==0?"Warehouse":WPOS.locations[hist.auxid].name):"")+'</td><td>'+hist.amount+' '+(hist.unit||'')+'</td><td>'+hist.dt+'</td></tr>');
        }
        WPOS.util.hideLoader();
        $("#stockhistdialog").dialog('open');
    }
    function openEditStockDialog(id){
        var item = stock[id];
        $("#setstockitemid").val(item.storeditemid);
        $("#setstocklocid").val(item.locationid);
        $("#setstockqty").val(item.stocklevel);
        // default unit from the item
        var it = (typeof items !== 'undefined' && items) ? items[item.storeditemid] : null;
        var u = (it && it.unit) ? it.unit : 'Pcs';
        ensureStockUnitOption('setstockunit', u);
        $("#setstockunit").val(u);
        $("#editstockdialog").dialog("open");
    }
    function openAddStockDialog(){
        populateItems();
        try {
            var supSel = $('#addstocksupplier');
            supSel.html('<option value="">— manual adjustment (no purchase) —</option>');
            var suppliersList = (typeof suppliers !== 'undefined' && suppliers) ? suppliers : (WPOS.getJsonData('suppliers/get') || {});
            for (var k in suppliersList) supSel.append('<option value="' + suppliersList[k].id + '">' + (suppliersList[k].name || suppliersList[k].id) + '</option>');
            var accSel = $('#addstockaccount');
            accSel.html('<option value="">— no account movement —</option>');
            var accts = WPOS.sendJsonData('accounts/list', JSON.stringify({})) || [];
            accts.forEach(function(a){ accSel.append('<option value="' + a.id + '">' + a.name + ' (bal ' + WPOS.util.money(a.balance) + ')</option>'); });
        } catch(e) {}
        $("#addstockdialog").dialog("open");
    }
    function openTransferStockDialog(id){
        var item = stock[id];
        $("#tstockitemid").val(item.storeditemid);
        $("#tstocklocid").val(item.locationid);
        var it = (typeof items !== 'undefined' && items) ? items[item.storeditemid] : null;
        var u = (it && it.unit) ? it.unit : 'Pcs';
        ensureStockUnitOption('tstockunit', u);
        $("#tstockunit").val(u);
        $("#transferstockdialog").dialog("open");
    }
    function onAddStockItemChange(){
        var id = $('#addstockitemid').val();
        var it = (typeof items !== 'undefined' && items) ? items[id] : null;
        var u = (it && it.unit) ? it.unit : 'Pcs';
        ensureStockUnitOption('addstockunit', u);
        $('#addstockunit').val(u);
    }
    function populateItems(){
        if (items == null){
            WPOS.util.showLoader();
            items = WPOS.sendJsonData("items/get");
            var itemselect = $(".itemselect");
            itemselect.html('');
            for (var i in items){
                itemselect.append('<option class="itemid-'+items[i].id+'" value="'+items[i].id+'">'+items[i].name+'</option>');
            }
            WPOS.util.hideLoader();
            onAddStockItemChange();
        }
    }
    function saveItem(type){
        // show loader
        WPOS.util.showLoader();
        var item = {};
        switch (type){
        case 1:
            // adding new stock
            item.storeditemid = $("#addstockitemid option:selected").val();
            item.locationid = $("#addstocklocid option:selected").val();
            item.amount = $("#addstockqty").val();
            item.unit = $("#addstockunit").val() || 'Pcs';
            var supId = parseInt($('#addstocksupplier').val() || '0', 10);
            var unitCost = parseFloat($('#addstockcost').val() || 0);
            var paidNow = parseFloat($('#addstockpaid').val() || 0);
            var accId = parseInt($('#addstockaccount').val() || '0', 10);
            var notes = $('#addstocknotes').val() || '';
            if (supId > 0 && unitCost > 0) {
                // Purchase path: supplier credit for the unpaid remainder + account movement for paid-now
                var itemOpt = $("#addstockitemid option:selected");
                var itemName = itemOpt.attr('data-name') || itemOpt.text();
                var qty = parseInt(item.amount || '0', 10);
                if (!qty || qty <= 0) { alert('Enter qty > 0'); break; }
                var total = qty * unitCost;
                if (paidNow < 0 || paidNow > total) { alert('Pay now cannot exceed total (' + WPOS.util.money(total) + ')'); break; }
                if (paidNow > 0 && accId <= 0) { alert('Pick the account the money leaves from'); break; }
                var pur = WPOS.sendJsonData('suppliers/purchase', JSON.stringify({supplierid: supId, locationid: parseInt(item.locationid || '1', 10), items: [{name: itemName, storeditemid: parseInt(item.storeditemid, 10), qty: qty, unit: item.unit, cost: unitCost}], paid_immediate: paidNow, account_id: accId, notes: notes}));
                if (pur !== false) {
                    alert('Purchase ' + pur.ref + ' — total KSh ' + pur.total + ' (' + (pur.on_credit > 0 ? 'KSh ' + pur.on_credit + ' on supplier credit' : 'paid in full') + (paidNow > 0 ? ' from account' : '') + ')');
                    reloadTable();
                    $("#addstockdialog").dialog("close");
                }
            } else {
                if (WPOS.sendJsonData("stock/add", JSON.stringify(item))!==false){
                    reloadTable();
                    $("#addstockdialog").dialog("close");
                }
            }
            break;
        case 2:
            // set stock level
            item.storeditemid = $("#setstockitemid").val();
            item.locationid = $("#setstocklocid").val();
            item.amount = $("#setstockqty").val();
            item.unit = $("#setstockunit").val() || 'Pcs';
            if (WPOS.sendJsonData("stock/set", JSON.stringify(item))!==false){
                reloadTable();
                $("#editstockdialog").dialog("close");
            }
            break;
        case 3:
            // transfer stock
            item.storeditemid = $("#tstockitemid").val();
            item.locationid = $("#tstocklocid").val();
            item.newlocationid = $("#tstocknewlocid").val();
            item.amount = $("#tstockqty").val();
            item.unit = $("#tstockunit").val() || 'Pcs';
            if (WPOS.sendJsonData("stock/transfer", JSON.stringify(item))!==false){
               reloadTable();
               $("#transferstockdialog").dialog("close");
            }
            break;
        }
        // hide loader
        WPOS.util.hideLoader();
    }
    function reloadTable(){
        stock = WPOS.getJsonData("stock/get");
        loadStockStats();
        var stockarray = [];
        var tempstock;
        for (var key in stock){
            tempstock = stock[key];
            stockarray.push(tempstock);
        }
        datatable.fnClearTable(false);
        datatable.fnAddData(stockarray, false);
        datatable.api().draw(false);
    }
    function exportStock(){
        //var data  = WPOS.table2CSV($("#stocktable"));
        var filename = "stock-"+WPOS.util.getDateFromTimestamp(new Date());
        filename = filename.replace(" ", "");

        var data = {};
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id }).join(',').split(',');

        if (ids && ids.length > 0 && ids[0]!='') {
            for (var i = 0; i < ids.length; i++) {
                var id = ids[i];
                if (stock.hasOwnProperty(id))
                    data[id] = stock[id];
            }
        } else {
            data = stock;
        }

        var csv = WPOS.data2CSV(
            ['ID', 'Name', 'Supplier', 'Location', 'Qty'],
            ['id', 'name', 'supplier', {key:'locationid', func: function(value){ return WPOS.locations.hasOwnProperty(value) ? WPOS.locations[value].name : 'Unknown'; }}, 'stocklevel'],
            data
        );

        WPOS.initSave(filename, csv);
    }
</script>
<style type="text/css">
    #itemstable_processing {
        display: none;
    }
</style>