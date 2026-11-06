<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1>
        Dashboard
        <small>
            <i class="icon-double-angle-right"></i>
            overview &amp; stats
        </small>
    </h1>
</div><!-- /.page-header -->
    <div class="row">
    <div class="col-xs-12">
<!-- ALL ORDERS OVERVIEW -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-xs-12">
        <ul class="nav nav-tabs" style="margin-bottom: 0;">
            <li class="active"><a href="#dashAllOrders" data-toggle="tab" onclick="loadDashboardOrders('all');"><i class="icon-list"></i> All Orders</a></li>
            <li><a href="#dashKitchenOrders" data-toggle="tab" onclick="loadDashboardOrders('kitchen');"><i class="icon-fire"></i> Kitchen Orders</a></li>
            <li><a href="#dashWebsiteOrders" data-toggle="tab" onclick="loadDashboardOrders('website');"><i class="icon-globe"></i> Website Orders</a></li>
        </ul>
        <div class="table-header" style="background: #1a1a1a; color: #84cc16; padding: 6px 12px; font-weight: bold; font-size:13px;">
            <i class="icon-list"></i> <span id="dashOrderTabTitle">Recent Orders (All Sources)</span>
            <span id="dashOrdersCount" style="float:right; font-weight:normal; font-size:11px; color:#999;"></span>
        </div>
        <div style="max-height: 300px; overflow-y: auto; border:1px solid #ddd; border-top:none;">
        <table class="table table-striped table-bordered table-hover cb-compact" style="width:100%; margin-bottom: 0; font-size:12px;">
            <thead style="position: sticky; top:0; z-index:5;">
                <tr style="background:#f0f0f0;">
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">ID</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Source</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Customer</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Items</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Total</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Status</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Time</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Actions</th>
                </tr>
            </thead>
            <tbody id="dashOrdersBody">
                <tr><td colspan="8" class="center">Loading orders...</td></tr>
            </tbody>
        </table>
        </div>
        <style>.cb-compact td{padding:2px 6px !important; vertical-align:middle;} .cb-compact .btn-xs{padding:1px 5px; font-size:11px;}</style>
    </div>
</div>
<!-- END ALL ORDERS OVERVIEW -->
<!-- LIVE SALES TRACKER: POS + website/other, live -> completed, with View -->
<div class="row" style="margin-bottom: 16px;">
    <div class="col-xs-12">
        <div class="table-header" style="background: #1a1a1a; color: #84cc16; padding: 6px 12px; font-weight: bold; font-size:13px;">
            <i class="icon-random"></i> Live Sales Tracker — POS &amp; Website (live → completed)
            <span style="float:right; font-weight:normal; font-size:12px;">
                <select id="trackerStatus" onchange="loadLiveTracker();" style="color:#333; padding:2px 4px;">
                    <option value="all">All statuses</option>
                    <option value="live">Live (queued/preparing/ready)</option>
                    <option value="completed">Completed</option>
                </select>
                <select id="trackerSource" onchange="loadLiveTracker();" style="color:#333; padding:2px 4px;">
                    <option value="all">All sources</option>
                    <option value="pos">POS</option>
                    <option value="website">Website</option>
                    <option value="glovo">Glovo</option>
                    <option value="ubereats">Uber Eats</option>
                    <option value="phone">Phone</option>
                </select>
                <input id="trackerSearch" type="text" placeholder="Ref / customer…" onkeypress="if(event.keyCode==13){loadLiveTracker();}" style="width:130px; color:#333; padding:2px 6px;" />
                <button class="btn btn-xs btn-default" onclick="loadLiveTracker();" title="Refresh tracker"><i class="icon-refresh"></i></button>
                <span id="trackerCount" style="font-size:11px; color:#999;"></span>
            </span>
        </div>
        <div style="max-height: 320px; overflow-y: auto; border:1px solid #ddd; border-top:none;">
        <table class="table table-striped table-bordered table-hover cb-compact" style="width:100%; margin-bottom:0; font-size:12px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f0f0f0;">
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">ID</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Ref</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Source</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Type</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Customer</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Items</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Total</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Status</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Time</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">View</th>
                </tr>
            </thead>
            <tbody id="liveTrackerBody">
                <tr><td colspan="10" class="center">Loading live sales...</td></tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
<!-- RICH REPORTING: POS + external orders (website / Glovo / Uber Eats / phone) -->
<div class="row" style="margin-bottom: 16px;">
    <div class="col-xs-12">
        <div class="table-header" style="background: #1a1a1a; color: #84cc16; padding: 8px 12px; font-weight: bold;">
            <i class="icon-bar-chart"></i> Rich Reporting — POS &amp; Online Orders
            <span style="float:right; font-weight:normal;">
                <select id="dashRange" onchange="loadRichDashboard();" style="color:#333; padding:2px 6px;">
                    <option value="today">Today</option>
                    <option value="week" selected>Last 7 days</option>
                    <option value="month">Last 30 days</option>
                </select>
                <button class="btn btn-xs btn-default" onclick="loadRichDashboard();" title="Refresh"><i class="icon-refresh"></i></button>
                <button class="btn btn-xs btn-default" onclick="if(typeof setOrderSoundMuted==='function') setOrderSoundMuted(!isOrderSoundMuted());" data-order-sound-toggle title="Toggle new-order ding">🔊 Sound: On</button>
                <span id="dashUpdated" style="font-size:11px; color:#999;"></span>
            </span>
        </div>
        <div id="richKpis" style="display:flex; flex-wrap:wrap; gap:10px; padding:12px; background:#f7f7f5;">
            <div style="flex:1; min-width:150px; background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:10px;">
                <div style="color:#777; font-size:11px; text-transform:uppercase;">Collected (completed)</div>
                <div id="kpiRevenue" style="font-size:20px; font-weight:800;">-</div>
                <div style="font-size:11px; color:#999;">AOV <span id="kpiAov">-</span></div>
            </div>
            <div style="flex:1; min-width:150px; background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:10px;">
                <div style="color:#777; font-size:11px; text-transform:uppercase;">Orders</div>
                <div id="kpiOrders" style="font-size:20px; font-weight:800;">-</div>
                <div style="font-size:11px; color:#999;">Completed <span id="kpiCompleted">-</span> · Queued <span id="kpiQueued">-</span></div>
            </div>
            <div style="flex:1; min-width:150px; background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:10px;">
                <div style="color:#777; font-size:11px; text-transform:uppercase;">Completion rate</div>
                <div id="kpiCompletion" style="font-size:20px; font-weight:800;">-</div>
                <div style="font-size:11px; color:#999;">completed ÷ all</div>
            </div>
            <div style="flex:1; min-width:150px; background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:10px;">
                <div style="color:#777; font-size:11px; text-transform:uppercase;">Online pipeline (queued gross)</div>
                <div id="kpiPipeline" style="font-size:20px; font-weight:800;">-</div>
                <div style="font-size:11px; color:#999;">website / Glovo / Uber Eats / phone</div>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-bottom: 20px;">
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-signal"></i> By Source</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Source</th><th>Orders</th><th>Collected</th><th>Share</th></tr></thead><tbody id="richBySource"><tr><td colspan="4" class="center">Loading...</td></tr></tbody></table></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-map-marker"></i> By Order Type</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Type</th><th>Orders</th><th>Collected</th></tr></thead><tbody id="richByType"><tr><td colspan="3" class="center">Loading...</td></tr></tbody></table></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-credit-card"></i> By Payment</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Method</th><th>Payments</th><th>Collected</th></tr></thead><tbody id="richByPayment"><tr><td colspan="3" class="center">Loading...</td></tr></tbody></table></div></div>
    </div>
</div>
<div class="row" style="margin-bottom: 20px;">
    <div class="col-sm-7">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-time"></i> Sales by Hour</h4></div>
            <div class="widget-body"><div id="richByHour" style="display:flex; align-items:flex-end; gap:4px; height:140px; padding:8px; background:#fafafa; overflow-x:auto;"><span style="color:#999; font-size:12px;">Loading...</span></div></div></div>
    </div>
    <div class="col-sm-5">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-star orange"></i> Top Items (detailed)</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Item</th><th>Qty</th><th>Revenue</th></tr></thead><tbody id="richTopItems"><tr><td colspan="3" class="center">Loading...</td></tr></tbody></table></div></div>
    </div>
</div>
<div class="row">
    <div class="space-6"></div>

<div class="col-sm-5">
    <div class="widget-box transparent">
    <div class="widget-header widget-header-flat">
        <h4 class="lighter">
            <i class="icon-dollar"></i>
            Today's Takings
        </h4>
    </div>
    <div class="widget-body" style="padding-top: 10px; text-align: center;">
    <div class="infobox infobox-green infobox-sales">
        <div class="infobox-icon">
            <i class="icon-shopping-cart"></i>
        </div>

        <div class="infobox-data">
            <span id="salenum" class="infobox-data-number">-</span>
            <div class="infobox-content">Sales</div>
        </div>
        <div id="saletotal" class="stat stat-success">-</div>
    </div>

    <div class="infobox infobox-orange infobox-refunds">
        <div class="infobox-icon">
            <i class="icon-backward"></i>
        </div>

        <div class="infobox-data">
            <span id="refundnum" class="infobox-data-number">-</span>
            <div class="infobox-content">Refunds</div>
        </div>

        <div id="refundtotal" class="stat stat-important">-</div>
    </div><br/>

    <div class="infobox infobox-red infobox-voids">
        <div class="infobox-icon">
            <i class="icon-ban-circle"></i>
        </div>

        <div class="infobox-data">
            <span id="voidnum" class="infobox-data-number">-</span>
            <div class="infobox-content">Voids</div>
        </div>
        <div id="voidtotal" class="stat stat-important">-</div>
    </div>

    <div class="infobox infobox-blue2 infobox-takings">
        <div class="infobox-icon">
            <i class="icon-dollar"></i>
        </div>

        <div class="infobox-data">
            <span id="takings" class="infobox-data-number">-</span>
            <div class="infobox-content">Revenue</div>
        </div>
    </div><br/>

    <div class="infobox infobox-orange infobox-cost">
        <div class="infobox-icon">
            <i class="icon-dollar"></i>
        </div>

        <div class="infobox-data">
            <span id="cost" class="infobox-data-number">-</span>
            <div class="infobox-content">Cost</div>
        </div>
    </div>

    <div class="infobox infobox-green infobox-profit">
        <div class="infobox-icon">
            <i class="icon-dollar"></i>
        </div>

        <div class="infobox-data">
            <span id="profit" class="infobox-data-number">-</span>
            <div class="infobox-content">Profit</div>
        </div>
    </div>

    <div style="clear: both; margin-top: 18px; padding-top: 12px; border-top: 1px solid #e5e5e5;">
        <div style="font-weight: bold; color: #333; margin-bottom: 8px;">
            <i class="icon-signal"></i> Sales by Source
        </div>
        <table class="table table-striped table-bordered table-hover" style="margin-bottom: 0; font-size: 12px;">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Orders</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="salesBySourceBody">
                <tr><td colspan="3" class="center">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    </div>
    </div>
</div>

<div class="vspace-sm"></div>

<div class="col-sm-7">
    <div class="widget-box transparent">
        <div class="widget-header widget-header-flat">
            <h4 class="lighter">
                <i class="icon-signal"></i>
                Sale Graph
            </h4>
            <div class="widget-toolbar no-border">
                <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                    <span id="grange">This Week</span>
                    <i class="icon-angle-down icon-on-right bigger-110"></i>
                </button>

                <ul id="grangevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                    <li onclick="setGraph($(this));" class="active" >
                        <a class="blue">
                            <i class="icon-caret-right bigger-110">&nbsp;</i>
                            <span class="grangeval">This Week</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">Last Week</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">This Month</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">Last Month</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-4">
                <div id="sales-charts"></div>
            </div><!-- /widget-main -->
        </div><!-- /widget-body -->
    </div><!-- /widget-box -->
</div><!-- /span -->

</div><!-- /row -->

<div class="vspace-sm"></div>
<div class="hr hr32 hr-dotted hidden-480 hidden-320 hidden-xs"></div>


<div class="row">
    <div class="col-sm-5">
        <div class="widget-box transparent">
            <div class="widget-header widget-header-flat">
                <h4 class="lighter">
                    <i class="icon-star orange"></i>
                    Popular Items This Month
                </h4>
            </div>

            <div class="widget-body">
                <div class="widget-main no-padding">
                    <table class="table table-bordered table-striped">
                        <thead class="thin-border-bottom">
                        <tr>
                            <th>
                                <i class="icon-caret-right blue"></i>
                                Name
                            </th>

                            <th>
                                <i class="icon-caret-right blue"></i>
                                Qty Sold
                            </th>

                            <th>
                                <i class="icon-caret-right blue"></i>
                                Total
                            </th>
                        </tr>
                        </thead>

                        <tbody id="popularitems">

                        </tbody>
                    </table>
                </div><!-- /widget-main -->
            </div><!-- /widget-body -->
        </div><!-- /widget-box -->
    </div>

    <div class="vspace-sm"></div>

    <div class="col-sm-7">
        <div class="widget-box transparent">
            <div class="widget-header widget-header-flat">
                <h4 class="lighter">
                    <i class="icon-plus-sign"></i>
                    Sale Stats
                </h4>
            </div>
        </div>
        <div class="widget-box">
            <div class="widget-header widget-header-flat widget-header-small">
                <i class="icon-signal"></i>
                <div class="widget-toolbar no-border" style="float: none; display: inline-block; vertical-align: top;">
                    <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span id="pietype">Payments</span>
                        <i class="icon-angle-down icon-on-right bigger-110"></i>
                    </button>

                    <ul id="pietypevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                        <li onclick="setPieType($(this));" class="active">
                            <a class="blue">
                                <i class="icon-caret-right bigger-110">&nbsp;</i>
                                <span class="pietypeval">Payments</span>
                            </a>
                        </li>

                        <li onclick="setPieType($(this));">
                            <a>
                                <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                <span class="pietypeval">Devices</span>
                            </a>
                        </li>

                        <li onclick="setPieType($(this));">
                            <a>
                                <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                <span class="pietypeval">Locations</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <h5 style="display: inline-block; margin-top: 0;"></h5>
                <div class="widget-toolbar no-border">
                    <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span id="pierange">This Week</span>
                        <i class="icon-angle-down icon-on-right bigger-110"></i>
                    </button>

                    <ul id="pierangevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                        <li onclick="setPie($(this));" class="active" >
                            <a class="blue">
                                <i class="icon-caret-right bigger-110">&nbsp;</i>
                                <span class="pierangeval">This Week</span>
                            </a>
                        </li>

                        <li onclick="setPie($(this));">
                            <a>
                                <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                <span class="pierangeval">Last Week</span>
                            </a>
                        </li>

                        <li onclick="setPie($(this));">
                            <a>
                                <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                <span class="pierangeval">This Month</span>
                            </a>
                        </li>

                        <li onclick="setPie($(this));">
                            <a>
                                <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                <span class="pierangeval">Last Month</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="widget-body">
                <div class="widget-main">
                    <div id="piechart-placeholder"></div>

                    <div class="hr hr8 hr-double"></div>

                    <div class="clearfix">
                        <div class="grid3">
															<span class="grey">
																<i class="icon-shopping-cart icon-2x green"></i>
																&nbsp;<span id="piesalenum">-</span> sales
															</span>
                            <h4 id="piesaletotal" class="bigger pull-right">-</h4>
                        </div>

                        <div class="grid3">
															<span class="grey">
																<i class="icon-ban-circle icon-2x orange"></i>
																&nbsp;<span id="pierefundnum">-</span> refunds
															</span>
                            <h4 id="pierefundtotal" class="bigger pull-right">-</h4>
                        </div>

                        <div class="grid3">
															<span class="grey">
																<i class="icon-dollar icon-2x blue"></i>
																&nbsp; total
															</span>
                            <h4 id="piebalance" class="bigger pull-right">-</h4>
                        </div>
                    </div>
                </div><!-- /widget-main -->
            </div><!-- /widget-body -->
        </div><!-- /widget-box -->
    </div>
    </div>
</div>
</div>

<div class="hr hr32 hr-dotted"></div>
<!-- inline scripts related to this page -->

<script type="text/javascript">

    var placeholder = $('#piechart-placeholder').css({'width':'90%' , 'min-height':'150px'});
    var piedata = [];

    function loadTodayStats(totals){
        if (!totals){
            return false;
        }
        // populate the fields
        $("#salenum").text(totals.salenum);
        $("#saletotal").text(WPOS.util.currencyFormat(totals.saletotal));
        $("#refundnum").text(totals.refundnum);
        $("#refundtotal").text(WPOS.util.currencyFormat(totals.refundtotal));
        $("#voidnum").text(totals.voidnum);
        $("#voidtotal").text(WPOS.util.currencyFormat(totals.voidtotal));
        $("#takings").text(WPOS.util.currencyFormat(totals.totaltakings, true));
        $("#cost").text(WPOS.util.currencyFormat(totals.cost, true));
        $("#profit").text(WPOS.util.currencyFormat(totals.profit, true));

        var sourceBody = $('#salesBySourceBody');
        var sourceBreakdown = totals.sourceBreakdown || {};
        var sourceRows = '';
        var sourceNames = {website:'Website', pos:'POS', glovo:'Glovo', ubereats:'Uber Eats', phone:'Phone', dinein:'Dine In', delivery:'Delivery', pickup:'Pickup'};
        var sourceOrder = [];
        for (var key in sourceBreakdown) {
            if (sourceBreakdown.hasOwnProperty(key)) sourceOrder.push(key);
        }
        sourceOrder.sort(function(a,b){
            var av = parseFloat(sourceBreakdown[a].stotal || 0), bv = parseFloat(sourceBreakdown[b].stotal || 0);
            return bv - av;
        });
        if (sourceOrder.length === 0) {
            sourceRows = '<tr><td colspan="3" class="center">No sales yet.</td></tr>';
        } else {
            sourceOrder.forEach(function(key){
                var item = sourceBreakdown[key] || {};
                var label = sourceNames[key] || key.charAt(0).toUpperCase() + key.slice(1);
                var total = WPOS.util.currencyFormat(item.stotal || 0, true);
                sourceRows += '<tr>'
                    + '<td>' + label + '</td>'
                    + '<td>' + (item.snum || 0) + '</td>'
                    + '<td>' + total + '</td>'
                    + '</tr>';
            });
        }
        sourceBody.html(sourceRows);

        // Set onclicks
        $(".infobox-sales").on('click', function(){ WPOS.transactions.openTransactionList(totals.salerefs); });
        $(".infobox-refunds").on('click', function(){ WPOS.transactions.openTransactionList(totals.refundrefs); });
        $(".infobox-voids").on('click', function(){ WPOS.transactions.openTransactionList(totals.voidrefs); });
        $(".infobox-takings").on('click', function(){ WPOS.transactions.openTransactionList(totals.refs); });
        return true;
    }
    function loadPopularItems(items){
        if (!items){
            return false;
        }
        var sort = [];
        // put indexes into array and sort
        for (var i in items){
            sort.push([i, items[i].sold]);
        }
        sort.sort(function(a, b){ return b[1] - a[1]; });

        for (i=0; (i<6 && i<sort.length); i++){
            $("#popularitems").append('<tr><td><b>'+items[sort[i][0]].name+'</b></td><td><b class="blue">'+items[sort[i][0]].soldqty+'</b></td><td><b class="green">'+WPOS.util.currencyFormat(items[sort[i][0]].soldtotal)+'</b></td></tr>');
        }
        return true;
    }

    function getTodayTimeVals(){
        var stime = new Date();
        stime.setHours(0); stime.setMinutes(0); stime.setSeconds(0);
        var etime = new Date();
        etime.setHours(23); etime.setMinutes(59); etime.setSeconds(59);
        return [stime.getTime(), etime.getTime()];
    }

    function getTimeVals(text) {
        var etime = new Date();
        var stime = new Date();
        stime.setHours(0);
        stime.setMinutes(0);
        stime.setSeconds(0);
        switch (text) {
            case "This Week":
                stime = stime.getTime() - 604800000;
                etime = etime.getTime();
                break;
            case "Last Week":
                etime.setHours(23);
                etime.setMinutes(59);
                etime.setSeconds(59);
                stime = stime.getTime() - 1209600000;
                etime = etime.getTime() - 604800000;
                break;
            case "This Month":
                stime = stime.getTime() - 2419200000;
                etime = etime.getTime();
                break;
            case "Last Month":
                etime.setHours(23);
                etime.setMinutes(59);
                etime.setSeconds(59);
                stime = stime.getTime() - 4838400000;
                etime = etime.getTime() - 2419200000;
                break;
        }
        return [stime, etime];
    }

    function getPieValues(){
        // get range
        var vals = getTimeVals($("#pierange").text());
        var stime = vals[0]; var etime = vals[1]; var type;
        // get type
        switch ($("#pietype").text()){
            case "Locations": type = "stats/locations"; break;
            case "Devices": type = "stats/devices"; break;
            case "Payments": type = "stats/takings"; break;
        }
        return [type, stime, etime];
    }

    function reloadPieChart(){
        // show loader
        WPOS.util.showLoader();
        var vals = getPieValues();
        // fetch the data
        var data = WPOS.sendJsonData(vals[0], JSON.stringify({"stime":vals[1], "etime":vals[2], "totals":true}));
        generatePieChart(data);
        // hide loader
        WPOS.util.hideLoader();
    }

    function generatePieChart(data){
        if (!data){
            return false;
        }
        piedata = [];
        // generate pie chart data
        for (var i in data){
            if (i != "Totals"){
                piedata.push({ label:(data[i].hasOwnProperty('name')?data[i].name:i), refs:data[i].refs,  data:data[i].balance});
            }
        }
        $.plot(placeholder, piedata, {
            series: {
                pie: {
                    show: true,
                    tilt:0.8,
                    highlight: {
                        opacity: 0.25
                    },
                    stroke: {
                        color: '#fff',
                        width: 2
                    },
                    startAngle: 2
                }
            },
            legend: {
                show: true,
                position: "ne",
                labelBoxBorderColor: null,
                margin:[-30,15]
            }
            ,
            grid: {
                hoverable: true,
                clickable: true
            }
        });

        var totals = data['Totals'];
        // Fill total fields
        $("#piesaletotal").text(WPOS.util.currencyFormat(totals.saletotal));
        $("#piesalenum").text(totals.salenum);
        $("#pierefundtotal").text(WPOS.util.currencyFormat(totals.refundtotal));
        $("#pierefundnum").text(totals.refundnum);
        $("#piebalance").text(WPOS.util.currencyFormat(totals.totaltakings));

        return true;
    }

    function setPie(element){
        $("#pierange").text($(element).children().children('.pierangeval').text());
        $("#pierangevalues li").removeClass("active");
        $("#pierangevalues li i").addClass("invisible");
        $("#pierangevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadPieChart();
    }

    function setPieType(element){
        $("#pietype").text($(element).children().children('.pietypeval').text());
        $("#pietypevalues li").removeClass("active");
        $("#pietypevalues li i").addClass("invisible");
        $("#pietypevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadPieChart();
    }

    function reloadGraph(){
        // show loader
        WPOS.util.showLoader();
        var vals = getTimeVals($("#grange").text());
        // fetch the data
        var jdata = WPOS.sendJsonData("graph/general", JSON.stringify({"stime":vals[0], "etime":vals[1], "interval":86400000}));
        drawGraph(jdata);
        // hide loader
        WPOS.util.hideLoader();
    }

    // Graph functions
    function drawGraph(jdata){
        if (!jdata){
            return false;
        }
        // generate plot data
        var tempdate;
        var sales = [], refunds = [], takings = [],  cost = [],  profit = [], salerefs = [], refundrefs = [], takingrefs = [];
        // create the data object
        for (var i in jdata) {
            tempdate = new Date();
            tempdate.setTime(i);
            tempdate.setHours(0);
            tempdate.setMinutes(0);
            tempdate.setSeconds(0);
            tempdate = tempdate.getTime();
            salerefs.push(jdata[i].salerefs);
            sales.push([ tempdate, jdata[i].saletotal]);
            refundrefs.push(jdata[i].refundrefs);
            refunds.push([ tempdate, jdata[i].refundtotal]);
            takingrefs.push(jdata[i].refs);
            takings.push([ tempdate, jdata[i].totaltakings]);
            cost.push([ tempdate, jdata[i].cost]);
            profit.push([ tempdate, jdata[i].profit]);
        }
        var data = [{ label: "Profit", refs: takingrefs, data: profit, color: "#29AB87" },{ label: "Cost", refs: takingrefs, data: cost, color: "#EA3C53" },{ label: "Sales", refs:salerefs, data: sales, color: "#9ABC32" },{ label: "Refunds", refs:refundrefs, data: refunds, color: "#EDC240" },{ label: "Revenue", refs: takingrefs, data: takings, color: "#3983C2" }];
        // render the graph
        $.plot("#sales-charts", data, {
            hoverable: true,
            shadowSize: 0,
            series: {
                lines: { show: true },
                points: { show: true }
            },
            xaxis: {
                mode: "time",
                minTickSize: [1, "day"],
                timeformat: "%d/%m",
                timezone: "browser"
            },
            yaxis: {
                ticks: 10
            },
            grid: {
                backgroundColor: { colors: [ "#fff", "#fff" ] },
                borderWidth: 1,
                borderColor: '#555',
                hoverable: true,
                clickable: true
            }
        });

        return true;
    }

    function setGraph(element){
        $("#grange").text($(element).children().children('.grangeval').text());
        $("#grangevalues li").removeClass("active");
        $("#grangevalues li i").addClass("invisible");
        $("#grangevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadGraph();
    }

    jQuery(function($) {

        // Chart hover Tooltip
        var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
        var previousPoint = null;

        var tooltip = function (event, pos, item) {
            if(item) {
                if (previousPoint != item.seriesIndex) {
                    previousPoint = item.seriesIndex;
                    var tip;
                    if (item.series['percent']!=null){
                        tip = item.series['label'] + " : " + item.series['percent'].toFixed(2)+'% ('+ WPOS.util.currencyFormat(item.series['data'][0][1])+')';
                    } else {
                        tip = item.series['label'] + " : "+WPOS.util.currencyFormat(item.datapoint[1]);
                    }
                    $tooltip.show().children(0).text(tip);
                }
                var left, right;
                if ((pos.pageX + 10 + $tooltip.width())>window.innerWidth){
                    left = ""; right = 0;
                } else {
                    right = ""; left = pos.pageX + 10;
                }
                $tooltip.css({top:pos.pageY + 10, left: left, right: right});
            } else {
                $tooltip.hide();
                previousPoint = null;
            }
        };
        var clickpie = function(event, pos, item){
            if (item==null) return;
            WPOS.transactions.openTransactionList(item.series['refs']);
        };
        var clickgraph = function(event, pos, item){
            if (item==null) return;
            WPOS.transactions.openTransactionList(item.series['refs'][item.dataIndex]);
        };
        // graph tooltips
        placeholder.on('plothover', tooltip);
        placeholder.on('plotclick', clickpie);
        var salechart = $('#sales-charts');
        salechart.on('plothover', tooltip);
        salechart.on('plotclick', clickgraph);
        salechart.css({'width':'100%' , 'height':'220px'});

        var tmonth = getTimeVals("This Month");
        var ttoday = getTodayTimeVals();
        var pvals = getPieValues();
        var gvals = getTimeVals($("#grange").text());
        var req = {"stats/itemselling":{"stime":tmonth[0], "etime":tmonth[1]}, "stats/general":{"stime":ttoday[0], "etime":ttoday[1]}, "graph/general":{"stime":gvals[0], "etime":gvals[1], "interval":86400000}};
        req[pvals[0]] = {"stime":pvals[1], "etime":pvals[2], "totals":true};
        var data = WPOS.sendJsonData("multi", JSON.stringify(req));
        // Load todays stats
        loadTodayStats(data['stats/general']);
        // load graph
        drawGraph(data['graph/general']);
        // initialize the initial pie chart
        generatePieChart(data[pvals[0]]);
        // load popular items
        loadPopularItems(data['stats/itemselling']);
        // hide loader
        WPOS.util.hideLoader();
    });

// --- Dashboard Orders Overview ---
var _dashFilter = 'all';
function loadDashboardOrders(filter){
    if (filter) _dashFilter = filter;
    var tbody = $('#dashOrdersBody');
    tbody.html('<tr><td colspan="8" class="center">Loading...</td></tr>');
    $.get('/api/poll.php?last=0' + (_dashFilter === 'website' ? '&source=website' : ''), function(data){
        if (data.error) { tbody.html('<tr><td colspan="8" class="center text-danger">'+data.error+'</td></tr>'); return; }
        var sales = (data.result && data.result.sales) ? data.result.sales : [];
        try { if (typeof checkNewOrders === 'function') checkNewOrders(sales, _dashFilter === 'website' ? 'website' : 'new'); } catch(e){}
        if (_dashFilter === 'kitchen') sales = sales.filter(function(s){ return (s.source || 'pos') !== 'website'; });
        var rows = '';
        var sourceColors = {website:'success', pos:'primary', glovo:'warning', ubereats:'info', phone:'default', dinein:'success'};
        var sourceLabels = {website:'Website', pos:'POS', glovo:'Glovo', ubereats:'Uber Eats', phone:'Phone', dinein:'Dine In'};
        sales.slice(0, 20).forEach(function(s){
            var items = '';
            for (var k in s.items) { items += s.items[k].qty + 'x ' + s.items[k].name; if (k < Object.keys(s.items).length - 1) items += ', '; }
            if (items.length > 40) items = items.substring(0, 40) + '...';
            if (s.table_number) items = '[T' + s.table_number + '] ' + items;
            var statusClass = {'queued':'warning','preparing':'info','ready':'success','completed':'default'}[s.orderstatus] || 'default';
            var sourceKey = String(s.source || s.channel || 'pos').toLowerCase();
            var srcColor = sourceColors[sourceKey] || 'default';
            var srcLabel = sourceLabels[sourceKey] || sourceKey.charAt(0).toUpperCase() + sourceKey.slice(1);
            rows += '<tr>'
                + '<td>'+s.id+'</td>'
                + '<td><span class="label label-'+srcColor+'">'+srcLabel+'</span></td>'
                + '<td>'+(s.customer ? s.customer.name : '-')+'</td>'
                + '<td style="font-size:12px;">'+items+'</td>'
                + '<td>'+s.total+'/=</td>'
                + '<td><span class="label label-'+statusClass+'">'+s.orderstatus+'</span></td>'
                + '<td style="font-size:12px;">'+s.received_at+'</td>'
                + '<td style="white-space:nowrap;">'
                + '<button class="btn btn-xs btn-info" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'preparing\')" title="Mark preparing"><i class="icon-fire"></i></button> '
                + '<button class="btn btn-xs btn-success" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'ready\')" title="Mark ready"><i class="icon-ok"></i></button> '
                + '<button class="btn btn-xs btn-default" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'completed\')" title="Mark completed"><i class="icon-check"></i></button> '
                + '<button class="btn btn-xs btn-danger" onclick="deleteDashboardOrder(\''+s.ref+'\')" title="Delete"><i class="icon-trash"></i></button>'
                + '</td>'
                + '</tr>';
        });
        if (!rows) rows = '<tr><td colspan="8" class="center">No orders yet.</td></tr>';
        tbody.html(rows);
        try { var c = $('#dashOrdersCount'); if (c.length) c.text((sales||[]).length + ' shown'); } catch(e){}
    }, 'json').fail(function(){ tbody.html('<tr><td colspan="8" class="center text-danger">Failed to load orders.</td></tr>'); });
}
function updateDashboardOrderStatus(ref, status){
    WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref: ref, orderstatus: status}), function(r){
        if (r) loadDashboardOrders(_dashFilter);
    });
}
function deleteDashboardOrder(ref){
    if (!confirm('Delete this order permanently?')) return;
    WPOS.sendJsonData("orders/remove", JSON.stringify({ref: ref}), function(r){
        if (r !== false) { loadDashboardOrders(_dashFilter); loadLiveTracker(); }
    });
}

// --- Live Sales Tracker: POS + website/other, live -> completed ---
var _trackerCache = {};
function trackerStatusOf(s){
    if (s.orderstatus) return String(s.orderstatus).toLowerCase();
    if (s.hasOwnProperty('voiddata') && s.voiddata) return 'void';
    if (s.hasOwnProperty('refunddata') && s.refunddata) return 'refunded';
    if (s.hasOwnProperty('isorder') && s.isorder) return 'queued';
    return 'completed';
}
function trackerStatusLabel(st){
    var map = {'queued':'Queued','preparing':'Preparing','ready':'Ready','completed':'Completed','void':'Void','refunded':'Refunded','cancelled':'Cancelled'};
    return map[st] || (st ? st.charAt(0).toUpperCase()+st.slice(1) : 'Unknown');
}
function trackerStatusClass(st){
    var map = {'queued':'warning','preparing':'info','ready':'success','completed':'default','void':'danger','refunded':'warning','cancelled':'danger'};
    return map[st] || 'default';
}
function loadLiveTracker(){
    var body = $('#liveTrackerBody');
    if (!body.length) return;
    var vals = dashRangeVals();
    var statusFilter = ($('#trackerStatus').val() || 'all');
    var sourceFilter = ($('#trackerSource').val() || 'all');
    var search = ($('#trackerSearch').val() || '').toLowerCase().trim();
    WPOS.sendJsonDataAsync('sales/get', JSON.stringify({stime: vals[0], etime: vals[1]}), function(data){
        var sales = data || {};
        if (WPOS.transactions && typeof WPOS.transactions.setTransactions === 'function') {
            try { WPOS.transactions.setTransactions(sales); } catch(e){}
        }
        _trackerCache = sales || {};
        var list = [];
        for (var ref in sales) {
            var s = sales[ref];
            if (!s) continue;
            s.ref = s.ref || ref;
            var st = trackerStatusOf(s);
            var src = String(s.source || s.channel || 'pos').toLowerCase();
            var isLive = ['queued','preparing','ready'].indexOf(st) !== -1;
            if (statusFilter === 'live' && !isLive) continue;
            if (statusFilter === 'completed' && st !== 'completed') continue;
            if (sourceFilter !== 'all' && src !== sourceFilter) continue;
            if (search) {
                var hay = (String(s.ref) + ' ' + String(s.external_ref || '') + ' ' + (s.customer && s.customer.name ? s.customer.name : '') + ' ' + (s.customer && s.customer.phone ? s.customer.phone : '')).toLowerCase();
                if (hay.indexOf(search) === -1) continue;
            }
            list.push({s: s, st: st, src: src});
        }
        list.sort(function(a,b){
            var ta = parseInt(a.s.processdt || a.s.dt || 0), tb = parseInt(b.s.processdt || b.s.dt || 0);
            // processdt may be ms (POS) or mixed; fall back to id (higher = newer)
            if (ta && tb && Math.abs(ta - tb) > 1000) return tb - ta;
            return (b.s.id || 0) - (a.s.id || 0);
        });
        var total = list.length;
        list = list.slice(0, 50);
        var sourceLabels = {website:'Website', pos:'POS', glovo:'Glovo', ubereats:'Uber Eats', phone:'Phone'};
        var sourceColors = {website:'success', pos:'primary', glovo:'warning', ubereats:'info', phone:'default'};
        var rows = '';
        list.forEach(function(entry){
            var s = entry.s, st = entry.st;
            var items = '';
            if (s.items) {
                if (Array.isArray(s.items)) items = s.items.map(function(it){ return (it.qty||1)+'x '+it.name; }).join(', ');
                else { var parts = []; for (var k in s.items) parts.push((s.items[k].qty||1)+'x '+(s.items[k].name||'')); items = parts.join(', '); }
            }
            if (items.length > 48) items = items.substring(0, 48) + '...';
            if (s.table_number) items = '[T' + s.table_number + '] ' + items;
            var cust = s.customer ? (s.customer.name || '-') : '-';
            var time = s.received_at || s.dt || (s.processdt ? new Date(s.processdt < 1e11 ? s.processdt : s.processdt).toLocaleString() : '');
            var srcLabel = sourceLabels[entry.src] || entry.src;
            var srcColor = sourceColors[entry.src] || 'default';
            rows += '<tr>'
                + '<td>'+(s.id||'-')+'</td>'
                + '<td title="'+String(s.ref).replace(/"/g,'&quot;')+'">'+String(s.ref).substring(0,22)+'</td>'
                + '<td><span class="label label-'+srcColor+'">'+srcLabel+'</span></td>'
                + '<td>'+(s.order_type||'-')+'</td>'
                + '<td>'+cust+'</td>'
                + '<td style="font-size:11px;">'+items+'</td>'
                + '<td>'+(s.total||0)+'/=</td>'
                + '<td><span class="label label-'+trackerStatusClass(st)+'">'+trackerStatusLabel(st)+'</span></td>'
                + '<td style="font-size:11px;">'+time+'</td>'
                + '<td><button class="btn btn-xs btn-primary" onclick="WPOS.transactions.openTransactionDialog(\''+s.ref+'\');">View</button></td>'
                + '</tr>';
        });
        if (!rows) rows = '<tr><td colspan="10" class="center">No sales in this view.</td></tr>';
        body.html(rows);
        try { $('#trackerCount').text(total + ' total · ' + list.length + ' shown'); } catch(e){}
    });
}

// --- Rich Reporting (POS + online) ---
function dashRangeVals(){
    var r = ($('#dashRange').val() || 'week');
    var etime = new Date().getTime();
    var stime = etime;
    if (r === 'today') {
        var d = new Date(); d.setHours(0,0,0,0); stime = d.getTime();
    } else if (r === 'month') {
        stime = etime - 30*24*3600*1000;
    } else {
        stime = etime - 7*24*3600*1000;
    }
    return [stime, etime];
}
function renderRichDashboard(d){
    if (!d || !d.kpis) return;
    var fmt = function(v){ return WPOS.util.currencyFormat(v, true); };
    var rev = parseFloat(d.kpis.revenue || 0);
    $('#kpiRevenue').text(fmt(rev));
    $('#kpiAov').text(fmt(d.kpis.avg_order || 0));
    $('#kpiOrders').text(d.kpis.orders || 0);
    $('#kpiCompleted').text(d.kpis.completed || 0);
    $('#kpiQueued').text(d.kpis.queued || 0);
    $('#kpiCompletion').text((d.kpis.completion_rate || 0) + '%');
    // Online pipeline = queued gross (website + glovo + ubereats + phone)
    var pipeline = 0;
    var bsHtml = '', btHtml = '', bpHtml = '';
    var totalRev = rev || 0;
    var srcKeys = Object.keys(d.by_source || {});
    srcKeys.sort(function(a,b){ return (d.by_source[b].revenue||0) - (d.by_source[a].revenue||0); });
    if (!srcKeys.length) bsHtml = '<tr><td colspan="4" class="center">No sales in range.</td></tr>';
    srcKeys.forEach(function(k){
        var row = d.by_source[k];
        var share = totalRev > 0 ? Math.round((row.revenue / totalRev) * 100) : 0;
        if (k !== 'pos' && row.gross) pipeline += row.gross;
        bsHtml += '<tr><td>'+k+'</td><td>'+row.orders+'</td><td>'+fmt(row.revenue)+'</td><td>'+share+'%</td></tr>';
    });
    $('#richBySource').html(bsHtml);
    $('#kpiPipeline').text(fmt(pipeline));
    var typeKeys = Object.keys(d.by_type || {});
    if (!typeKeys.length) btHtml = '<tr><td colspan="3" class="center">No sales in range.</td></tr>';
    typeKeys.forEach(function(k){ var r = d.by_type[k]; btHtml += '<tr><td>'+k+'</td><td>'+r.orders+'</td><td>'+fmt(r.revenue)+'</td></tr>'; });
    $('#richByType').html(btHtml);
    var payKeys = Object.keys(d.by_payment || {});
    if (!payKeys.length) bpHtml = '<tr><td colspan="3" class="center">No completed payments in range.</td></tr>';
    payKeys.forEach(function(k){ var r = d.by_payment[k]; bpHtml += '<tr><td>'+k+'</td><td>'+r.payments+'</td><td>'+fmt(r.revenue)+'</td></tr>'; });
    $('#richByPayment').html(bpHtml);
    // Hourly bars
    var hourHtml = '';
    var maxRev = parseFloat(d.max_hour_revenue || 0) || 1;
    for (var h = 0; h < 24; h++) {
        var rec = (d.by_hour && d.by_hour[String(h)]) ? d.by_hour[String(h)] : (d.by_hour && d.by_hour[h] ? d.by_hour[h] : null);
        var hh = rec ? parseFloat(rec.revenue || 0) : 0;
        var pct = Math.round((hh / maxRev) * 100);
        var label = (h < 10 ? '0' + h : h) + ':00';
        hourHtml += '<div style="flex:1 0 28px; text-align:center;" title="'+label+' — '+fmt(hh)+' ('+(rec?rec.orders:0)+' orders)">'
            + '<div style="height:' + Math.max(4, pct) + '%; max-width:22px; margin:0 auto; background:#84cc16; border-radius:3px 3px 0 0; min-height:4px;"></div>'
            + '<div style="font-size:9px; color:#888;">'+label+'</div></div>';
    }
    $('#richByHour').html(hourHtml);
    var topHtml = '';
    (d.top_items || []).forEach(function(it){
        topHtml += '<tr><td>'+it.name+'</td><td>'+it.qty+'</td><td>'+fmt(it.revenue)+'</td></tr>';
    });
    $('#richTopItems').html(topHtml || '<tr><td colspan="3" class="center">No items sold in range.</td></tr>');
    var now = new Date();
    $('#dashUpdated').text('Updated ' + now.toLocaleTimeString());
}
function loadRichDashboard(){
    var vals = dashRangeVals();
    var data = WPOS.sendJsonData('stats/dashboard', JSON.stringify({stime: vals[0], etime: vals[1]}));
    if (data) renderRichDashboard(data);
}
$(document).ready(function(){ setTimeout(loadDashboardOrders, 500); setInterval(loadDashboardOrders, 15000); });
$(document).ready(function(){ try { if (typeof initOrderSound === 'function') initOrderSound(); } catch(e){} setTimeout(loadRichDashboard, 800); setInterval(loadRichDashboard, 30000); setTimeout(loadLiveTracker, 1200); setInterval(loadLiveTracker, 15000); });
// --- End Dashboard Orders Overview ---

</script>