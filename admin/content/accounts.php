<!-- Accounts / Cash Book -->
<div class="page-header">
    <h1>
        Accounts — Cash Book
        <small><i class="icon-double-angle-right"></i> every money movement, tracked</small>
    </h1>
    <div class="pull-right" style="white-space:nowrap;">
        <button class="btn btn-success btn-sm" onclick="openEntryDialog('deposit');"><i class="icon-plus align-top bigger-125"></i>Deposit</button>
        <button class="btn btn-warning btn-sm" onclick="openEntryDialog('withdrawal');"><i class="icon-minus align-top bigger-125"></i>Withdrawal</button>
        <button class="btn btn-info btn-sm" onclick="openEntryDialog('transfer');"><i class="icon-exchange align-top bigger-125"></i>Sweep / Transfer</button>
        <button class="btn btn-primary btn-sm" onclick="openAccountDialog();"><i class="icon-pencil align-top bigger-125"></i>Add Account</button>
    </div>
</div>

<div style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #e3e3d8;">
    <div id="accountCards"></div>
</div>

<div style="margin-bottom:10px; padding:0 0 10px; border-bottom:1px solid #e3e3d8;">
    <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:10px 12px; font-size:13px;">
        <strong>Money overview (current filter):</strong>
        In <b id="stIn" style="color:#256029;">KSh 0.00</b> · Out <b id="stOut" style="color:#c00;">KSh 0.00</b> · Net <b id="stNet">KSh 0.00</b> · <b id="stMoves">0</b> movements
        <span style="float:right;">
            <button class="btn btn-xs btn-success" onclick="exportCsv();"><i class="icon-cloud-download"></i> CSV</button>
            <button class="btn btn-xs btn-primary" onclick="exportPdf();"><i class="icon-print"></i> PDF / Print</button>
        </span>
    </div>
</div>

<div class="row" style="margin-bottom:14px;">
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-bar-chart"></i> Per account</h4></div>
            <div class="widget-body"><div class="cb-scroll"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:420px;"><thead><tr><th>Account</th><th style="text-align:right;">In</th><th style="text-align:right;">Out</th><th style="text-align:right;">Balance</th><th style="text-align:center;">Moves</th></tr></thead><tbody id="perAccountBody"><tr><td colspan="5"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-tags"></i> By source</h4></div>
            <div class="widget-body"><div class="cb-scroll"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:420px;"><thead><tr><th>Source</th><th style="text-align:right;">In</th><th style="text-align:right;">Out</th><th style="text-align:right;">Net</th><th style="text-align:center;">Moves</th></tr></thead><tbody id="bySourceBody"><tr><td colspan="5"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-calendar"></i> Daily flow · last 14 days</h4></div>
            <div class="widget-body"><div class="cb-scroll"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:420px;"><thead><tr><th>Date</th><th style="text-align:right;">In</th><th style="text-align:right;">Out</th><th style="text-align:right;">Net</th></tr></thead><tbody id="dailyBody"><tr><td colspan="4"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div></div>
    </div>
</div>

<div class="row">
<div class="col-sm-12">
    <div class="widget-box collapsed" id="outflowsBox">
        <div class="widget-header widget-header-flat">
            <h4 class="lighter"><i class="icon-signout"></i> Out &amp; Recoveries <small style="color:#888;">money out that doesn't come back, refunds, customer cash, marketing — <a href="#" onclick="$('#outflowsBox').removeClass('collapsed'); return false;">show panel</a></small></h4>
            <div class="widget-toolbar"><a href="#" data-action="collapse"><i class="icon-chevron-up"></i></a></div>
        </div>
        <div class="widget-body">
            <div class="cb-scroll"><table class="table table-striped cb-compact" style="margin:0; font-size:12px; min-width:760px;">
                <thead><tr><th>Category</th><th>Direction</th><th style="text-align:right;">Amount</th><th style="text-align:center;">Moves</th><th>Latest</th><th></th></tr></thead>
                <tbody id="outflowsBody"><tr><td colspan="6"><div class="cb-skel-row"></div></td></tr></tbody>
            </table></div>
            <div style="margin-top:6px; font-size:12px; color:#666;">Click <b>View</b> to jump to that category in the movements list. Refunds from suppliers land here as money-in recoveries.</div>
        </div>
    </div>
</div>
</div>

<div class="row">
<div class="col-sm-12">
    <div class="widget-box transparent">
        <div class="widget-header widget-header-flat">
            <h4 class="lighter"><i class="icon-list"></i> Movements</h4>
            <div class="widget-toolbar">
                <button class="btn btn-xs btn-default" onclick="loadMovements();"><i class="icon-refresh"></i> Refresh</button>
            </div>
            <div style="float:right; margin-right:8px;">
                <select id="mv-account" onchange="loadMovements(); loadStats();" style="font-size:12px; padding:2px;"></select>
                <select id="mv-source" onchange="loadMovements(); loadStats();" style="font-size:12px; padding:2px;">
                    <option value="">All sources</option>
                    <option value="purchase">Stock purchases</option>
                    <option value="supplierpay">Supplier payments</option>
                    <option value="transfer">Sweeps / Transfers</option>
                    <option value="manual">External / Manual</option>
                    <option value="external">External (not recoverable)</option>
                    <option value="marketing">Marketing</option>
                    <option value="customer_refund">Cash to customer</option>
                    <option value="supplier_refund">Refund from supplier</option>
                    <option value="sale">Sales</option>
                </select>
                <select id="mv-dir" onchange="loadMovements(); loadStats();" style="font-size:12px; padding:2px;">
                    <option value="">In + Out</option>
                    <option value="in">In only</option>
                    <option value="out">Out only</option>
                </select>
                <input id="mv-from" type="date" onchange="loadMovements(); loadStats();" style="font-size:12px; padding:2px;" title="From date"/>
                <input id="mv-to" type="date" onchange="loadMovements(); loadStats();" style="font-size:12px; padding:2px;" title="To date"/>
            </div>
        </div>
        <div class="widget-body">
            <div class="cb-scroll"><table id="movestable" class="table table-striped table-bordered table-hover cb-compact" style="margin:0; font-size:12px; min-width:860px;">
                <thead><tr><th>Date</th><th>Account</th><th>Dir</th><th>Amount (KSh)</th><th>Source</th><th>Ref</th><th>Description</th></tr></thead>
                <tbody id="movestableBody"><tr><td colspan="7"><div class="cb-skel-row"></div></td></tr></tbody>
            </table></div>
            <div style="margin-top:8px; font-size:12px; color:#666;">Balances = Σ(in) − Σ(out) per account. External entries (bank slips, supplier refunds, floating costs) are recorded via Deposit / Withdrawal and appear here with <b>External / Manual</b> source.</div>
        </div>
    </div>
</div>
</div>

<div id="entrydialog" class="hide" style="padding:14px; min-width:340px;">
    <div id="entry-title" style="font-weight:700; margin-bottom:10px;">Deposit</div>
    <div id="entry-from-wrap"><label>From account:&nbsp;<select id="entry-from" style="width:200px;"></select></label><br/></div>
    <div id="entry-to-wrap"><label>To account:&nbsp;<select id="entry-to" style="width:200px;"></select></label><br/></div>
    <label>Account:&nbsp;<select id="entry-account" style="width:200px;"></select></label><br/>
    <label>Amount (KSh):&nbsp;<input id="entry-amount" type="text" style="width:120px;"/></label><br/>
    <label>Description:&nbsp;<input id="entry-desc" type="text" style="width:260px;" placeholder="e.g. Bank deposit slip, rent, float top-up"/></label><br/>
    <label>Category:&nbsp;<select id="entry-category" style="width:260px;">
        <option value="manual">General / Floating</option>
        <option value="external">External account (not recoverable)</option>
        <option value="marketing">Marketing</option>
        <option value="customer_refund">Cash to customer (refund / cashback)</option>
        <option value="supplier_refund">Refund from supplier</option>
    </select></label><br/>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="submitEntry();">Save</button>
        <button class="btn btn-default btn-sm" onclick="$('#entrydialog').dialog('close')">Cancel</button>
    </div>
</div>

<div id="accountdialog" class="hide" style="padding:14px; min-width:320px;">
    <label>Name:&nbsp;<input id="newaccount-name" type="text" style="width:220px;" placeholder="e.g. KCB Bank"/></label><br/>
    <label>Type:&nbsp;<select id="newaccount-type" style="width:220px;"><option value="cash">Cash</option><option value="mobile">Mobile money</option><option value="bank">Bank</option><option value="other">Other</option></select></label><br/>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="submitAccount();">Save</button>
        <button class="btn btn-default btn-sm" onclick="$('#accountdialog').dialog('close')">Cancel</button>
    </div>
</div>

<script type="text/javascript">
    var ACCTS = [];
    var curEntryKind = 'deposit';
    $(function() {
        WPOS.util.showLoader();
        loadAccounts();
        if (!$('#entrydialog').hasClass('ui-dialog-content')) {
            $('#entrydialog').removeClass('hide').dialog({ width: 'auto', maxWidth: '460px', modal: true, autoOpen: false, title: 'New Entry' });
        }
        if (!$('#accountdialog').hasClass('ui-dialog-content')) {
            $('#accountdialog').removeClass('hide').dialog({ width: 'auto', maxWidth: '420px', modal: true, autoOpen: false, title: 'Add Account' });
        }
        WPOS.util.hideLoader();
    });
    function fmt(n){ return String(WPOS.util.money(parseFloat(n || 0))).replace('KSh', ''); } // number with commas; cards already carry KSh label
    function renderStats(s){
        if (!s) return;
        var tot = s.totals || {};
        $('#stIn').text(WPOS.util.money(tot.in)); $('#stOut').text(WPOS.util.money(tot.out)); $('#stNet').text(WPOS.util.money(tot.net)); $('#stMoves').text(tot.moves || 0);
        var labels = {purchase:'Stock purchase', supplierpay:'Supplier payment', transfer:'Sweep / Transfer', manual:'External / Manual', sale:'Sale', opening:'Opening balance', external:'External (not recoverable)', marketing:'Marketing', customer_refund:'Cash to customer', supplier_refund:'Refund from supplier'};
        var bs = '';
        (s.by_source || []).forEach(function(b){
            var netCol = parseFloat(b.net) >= 0 ? '#256029' : '#c00';
            bs += '<tr><td>' + (labels[b.source] || b.source) + '</td><td style="text-align:right;">' + fmt(b.in) + '</td><td style="text-align:right;">' + fmt(b.out) + '</td><td style="text-align:right; color:' + netCol + '; font-weight:700;">' + fmt(b.net) + '</td><td style="text-align:center;">' + b.moves + '</td></tr>';
        });
        $('#bySourceBody').html(bs || '<tr><td colspan="5" class="center">No movements in this filter.</td></tr>');
        var pa = '';
        (s.accounts || []).forEach(function(a){
            var col = parseFloat(a.balance || 0) >= 0 ? '#256029' : '#c00';
            pa += '<tr><td><strong>' + a.name + '</strong> <span style="color:#888;">(' + a.type + ')</span></td><td style="text-align:right;">' + fmt(a.total_in) + '</td><td style="text-align:right;">' + fmt(a.total_out) + '</td><td style="text-align:right; color:' + col + '; font-weight:800;">' + fmt(a.balance) + '</td><td style="text-align:center;">' + (a.moves || 0) + '</td></tr>';
        });
        $('#perAccountBody').html(pa || '<tr><td colspan="5" class="center">No accounts yet.</td></tr>');
        var dy = '';
        (s.daily_14d || []).forEach(function(d){
            dy += '<tr><td>' + d.d + '</td><td style="text-align:right;">' + fmt(d.in) + '</td><td style="text-align:right;">' + fmt(d.out) + '</td><td style="text-align:right; font-weight:700;">' + fmt(d.in - d.out) + '</td></tr>';
        });
        $('#dailyBody').html(dy || '<tr><td colspan="4" class="center">No activity in the last 14 days.</td></tr>');
    }
    function pickOutflow(key){
        $('#mv-source').val(key);
        loadMovements();
        loadStats();
        var el = document.querySelector('#movestable');
        if (el) el.scrollIntoView();
    }
    function renderOutflows(s){
        if (!s) return;
        var defs = [
            { key: 'external', label: 'External account (not recoverable)', dir: 'out' },
            { key: 'marketing', label: 'Marketing', dir: 'out' },
            { key: 'customer_refund', label: 'Cash to customer (refund / cashback)', dir: 'out' },
            { key: 'supplier_refund', label: 'Refund from supplier (recovery)', dir: 'in' },
            { key: 'manual', label: 'General external / floating', dir: 'any' }
        ];
        var map = {};
        (s.by_source || []).forEach(function(b){ map[b.source] = b; });
        var totalOut = 0; defs.forEach(function(d){ if (map[d.key]) totalOut += parseFloat(map[d.key].out || 0); });
        var html = '';
        defs.forEach(function(d){
            var b = map[d.key];
            var amt = b ? ((d.dir === 'in' ? parseFloat(b.in || 0) : parseFloat(b.out || 0))) : 0;
            html += '<tr><td>' + d.label + '</td><td>' + (d.dir === 'in' ? '<span style="color:#256029;font-weight:700;">RECOVERY (IN)</span>' : (d.dir === 'out' ? '<span style="color:#c00;font-weight:700;">OUT</span>' : 'mixed')) + '</td>'
                + '<td style="text-align:right; font-weight:700;">' + fmt(amt) + '</td><td style="text-align:center;">' + (b ? b.moves : 0) + '</td><td style="font-size:11px;">—</td>'
                + '<td><button class="btn btn-xs btn-primary" onclick="pickOutflow(\'' + d.key + '\')">View</button></td></tr>';
        });
        html += '<tr class="tot"><td colspan="2">Total non-recoverable / tracked outflows (excl. stock &amp; supplier payables)</td><td style="text-align:right;">' + fmt(totalOut) + '</td><td colspan="3"></td></tr>';
        $('#outflowsBody').html(html);
    }
    function loadStats(){
        var payload = currentFilters();
        var s = WPOS.sendJsonData('accounts/stats', JSON.stringify(payload));
        renderStats(s);
        renderOutflows(s);
    }
    function currentFilters(){
        var payload = {};
        var av = $('#mv-account').val(), sv = $('#mv-source').val(), dv = $('#mv-dir').val();
        if (av) payload.accountid = parseInt(av, 10);
        if (sv) payload.source = sv;
        if (dv) payload.direction = dv;
        var fromD = $('#mv-from').val(), toD = $('#mv-to').val();
        if (fromD) payload.from = new Date(fromD + 'T00:00:00').getTime();
        if (toD) payload.to = new Date(toD + 'T23:59:59').getTime();
        return payload;
    }
    function exportCsv(){
        var qs = new URLSearchParams();
        var f = currentFilters();
        Object.keys(f).forEach(function(k){ qs.set(k, f[k]); });
        window.open('/api/accounts_export.php?' + qs.toString(), '_blank');
    }
    function exportPdf(){
        var qs = new URLSearchParams();
        var f = currentFilters();
        Object.keys(f).forEach(function(k){ qs.set(k, f[k]); });
        window.open('/api/accounts_report.php?' + qs.toString(), '_blank');
    }
    function loadAccounts(){
        WPOS.util.showLoader();
        ACCTS = WPOS.sendJsonData('accounts/list', JSON.stringify({})) || [];
        var cards = '';
        var tot = 0;
        ACCTS.forEach(function(a){
            tot += parseFloat(a.balance || 0);
            var col = parseFloat(a.balance || 0) >= 0 ? '#256029' : '#c00';
            cards += '<div style="display:inline-block; min-width:170px; margin:0 10px 10px 0; background:#fff; border:1px solid #ddd; border-radius:8px; padding:12px;">'
                + '<div style="font-size:12px; color:#666;">' + a.name + ' <span style="color:#999;">(' + a.type + ')</span></div>'
                + '<div style="font-size:20px; font-weight:800; color:' + col + ';">' + WPOS.util.money(a.balance) + '</div>'
                + '<div style="font-size:11px; color:#888;">In ' + fmt(a.total_in) + ' · Out ' + fmt(a.total_out) + ' · ' + (a.moves || 0) + ' moves</div></div>';
        });
        cards += '<div style="display:inline-block; min-width:170px; margin:0 10px 10px 0; background:#1a1a1a; color:#fff; border-radius:8px; padding:12px;"><div style="font-size:12px; color:#ccc;">All accounts</div><div style="font-size:20px; font-weight:800; color:#84cc16;">' + WPOS.util.money(tot) + '</div><div style="font-size:11px; color:#aaa;">combined balance</div></div>';
        $('#accountCards').html(cards);
        // filters
        var sel = $('#mv-account');
        var keep = sel.val();
        sel.html('<option value="">All accounts</option>');
        ACCTS.forEach(function(a){ sel.append('<option value="' + a.id + '">' + a.name + '</option>'); });
        if (keep) sel.val(keep);
        fillAccountSelects();
        loadMovements();
        loadStats();
        WPOS.util.hideLoader();
    }
    function fillAccountSelects(){
        var f = $('#entry-from'), t = $('#entry-to'), a = $('#entry-account');
        [f, t, a].forEach(function(s){ s.html(''); ACCTS.forEach(function(x){ s.append('<option value="' + x.id + '">' + x.name + '</option>'); }); });
        // sensible defaults for transfer: Cash -> Bank
        var cash = ACCTS.filter(function(x){ return /cash/i.test(x.name); })[0], bank = ACCTS.filter(function(x){ return x.type === 'bank'; })[0];
        if (cash) f.val(cash.id);
        if (bank) t.val(bank.id);
        if (bank) a.val(bank.id);
    }
    function loadMovements(){
        var payload = currentFilters();
        var rows = WPOS.sendJsonData('accounts/movements', JSON.stringify(payload)) || [];
        var html = '';
        if (!rows.length) html = '<tr><td colspan="7" class="center">No movements recorded yet.</td></tr>';
        rows.forEach(function(r){
            var date = r.processdt ? new Date(r.processdt).toLocaleString('en-KE', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : (r.dt || '—');
            var dir = r.direction === 'in'
                ? '<span style="color:#256029; font-weight:700;">IN</span>'
                : '<span style="color:#c00; font-weight:700;">OUT</span>';
            var amt = '<span style="font-weight:700;">' + fmt(r.amount) + '</span>';
            var src = {purchase:'Stock purchase', supplierpay:'Supplier payment', transfer:'Sweep / Transfer', manual:'External / Manual', sale:'Sale', external:'External (not recoverable)', marketing:'Marketing', customer_refund:'Cash to customer', supplier_refund:'Refund from supplier'}[r.source] || r.source;
            html += '<tr><td>' + date + '</td><td>' + r.account + '</td><td>' + dir + '</td><td>' + amt + '</td><td>' + src + '</td><td>' + (r.ref || '-') + '</td><td style="font-size:11px;">' + (r.description || '-') + '</td></tr>';
        });
        $('#movestableBody').html(html);
    }
    function openEntryDialog(kind){
        curEntryKind = kind;
        var title = kind === 'deposit' ? 'Deposit (money in)' : (kind === 'withdrawal' ? 'Withdrawal (money out)' : 'Sweep / Transfer (move between accounts)');
        $('#entry-title').text(title);
        var isTrf = kind === 'transfer';
        $('#entry-from-wrap').toggle(isTrf);
        $('#entry-to-wrap').toggle(isTrf);
        $('#entry-account').closest('label').toggle(!isTrf);
        fillAccountSelects();
        if (!$('#entrydialog').hasClass('ui-dialog-content')) {
            $('#entrydialog').removeClass('hide').dialog({ width: 'auto', maxWidth: '460px', modal: true, autoOpen: false, title: 'New Entry' });
        }
        $('#entrydialog').dialog('open');
    }
    function submitEntry(){
        var amount = parseFloat($('#entry-amount').val() || 0);
        var desc = $('#entry-desc').val() || '';
        if (!amount || amount <= 0) { alert('Enter amount > 0'); return; }
        var payload = { kind: curEntryKind, amount: amount, description: desc, category: $('#entry-category').val() || 'manual' };
        if (curEntryKind === 'transfer') {
            payload.from_accountid = parseInt($('#entry-from').val(), 10);
            payload.to_accountid = parseInt($('#entry-to').val(), 10);
        } else {
            payload.accountid = parseInt($('#entry-account').val(), 10);
        }
        WPOS.util.showLoader();
        var r = WPOS.sendJsonData('accounts/entry', JSON.stringify(payload));
        WPOS.util.hideLoader();
        if (r === false) return;
        $('#entrydialog').dialog('close');
        $('#entry-amount').val('');
        $('#entry-desc').val('');
        loadAccounts();
    }
    function openAccountDialog(){
        if (!$('#accountdialog').hasClass('ui-dialog-content')) {
            $('#accountdialog').removeClass('hide').dialog({ width: 'auto', maxWidth: '420px', modal: true, autoOpen: false, title: 'Add Account' });
        }
        $('#accountdialog').dialog('open');
    }
    function submitAccount(){
        var name = $('#newaccount-name').val() || '';
        var type = $('#newaccount-type').val() || 'cash';
        if (!name.trim()) { alert('Account name required'); return; }
        WPOS.util.showLoader();
        var r = WPOS.sendJsonData('accounts/add', JSON.stringify({ name: name.trim(), type: type }));
        WPOS.util.hideLoader();
        if (r === false) return;
        $('#accountdialog').dialog('close');
        $('#newaccount-name').val('');
        loadAccounts();
    }
</script>
