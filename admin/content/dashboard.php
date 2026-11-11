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
<div id="dashCanvas" style="position:relative;">
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
                <tr><td colspan="3"><div class="cb-skel-row"></div></td></tr>
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

<div style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #e3e3d8;">
    <div style="background:#f7f8f5; border:1px solid #e3e3d8; border-radius:8px; padding:6px 10px;">
        <strong style="font-size:12px;">Quick tasks &amp; briefs</strong>
        <div id="quickTasks" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;"></div>
    </div>
</div>
    <div class="row">
    <div class="col-xs-12">
<!-- ALL ORDERS OVERVIEW -->
<div class="row" style="margin-bottom: 10px;">
    <div class="col-xs-12">
        <ul class="nav nav-tabs" style="margin-bottom: 0;">
            <li class="active"><a href="#dashAllOrders" data-toggle="tab" onclick="loadDashboardOrders('all');"><i class="icon-list"></i> All Orders</a></li>
            <li><a href="#dashKitchenOrders" data-toggle="tab" onclick="loadDashboardOrders('kitchen');"><i class="icon-fire"></i> Kitchen Orders</a></li>
            <li><a href="#dashWebsiteOrders" data-toggle="tab" onclick="loadDashboardOrders('website');"><i class="icon-globe"></i> Website Orders</a></li>
        </ul>
        <div class="table-header" style="background: #1a1a1a; color: #84cc16; padding: 6px 12px; font-weight: bold; font-size:13px;">
            <i class="icon-list"></i> <span id="dashOrderTabTitle">Queued Orders — All Sources (all open)</span>
            <span id="dashOrdersCount" style="float:right; font-weight:normal; font-size:11px; color:#999;"></span>
        </div>
        <div class="cb-scroll" style="max-height: 300px; overflow-y: auto; border:1px solid #ddd; border-top:none;">
        <table class="table table-striped table-bordered table-hover cb-compact cb-min-720" style="width:100%; margin-bottom: 0; font-size:12px;">
            <thead style="position: sticky; top:0; z-index:5;">
                <tr style="background:#f0f0f0;">
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">ID</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Source</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Customer</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Location</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Items</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Total</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Status</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Time</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Actions</th>
                </tr>
            </thead>
            <tbody id="dashOrdersBody">
                <tr><td colspan="9"><div class="cb-skel-row"></div></td></tr><tr><td colspan="9"><div class="cb-skel-row"></div></td></tr><tr><td colspan="9"><div class="cb-skel-row"></div></td></tr>
            </tbody>
        </table>
        </div>
        <style>
.cb-compact td{padding:2px 6px !important; vertical-align:middle;} .cb-compact .btn-xs{padding:1px 5px; font-size:11px;}
.cb-scroll{overflow:auto !important; -webkit-overflow-scrolling:touch;}
#cb-chart-tip{position:fixed; z-index:99999; background:#0a0a0a; color:#fff; border:1px solid #84cc16; border-radius:8px; padding:8px 10px; font-size:12px; line-height:1.45; pointer-events:none; display:none; max-width:260px; box-shadow:0 8px 24px rgba(0,0,0,.35);}
#cb-chart-tip .t{color:#84cc16; font-weight:800;}
#cb-chart-tip .v{font-weight:700;}
#cb-chart-tip .m{color:#bdbdbd;}
.cb-min-720{min-width:720px;} .cb-min-860{min-width:940px;}
/* Tracker and Queued share the same responsive treatment: never squeeze, scroll instead */
.cb-header{display:flex; flex-wrap:wrap; align-items:center; gap:6px;}
.cb-header .cb-controls{margin-left:auto; display:flex; flex-wrap:wrap; justify-content:flex-end; align-items:center; gap:4px; max-width:100%;}
.cb-header .cb-controls select{max-width:150px;}
.cb-header .cb-controls input#trackerSearch{flex:1 1 120px; width:auto; min-width:110px; max-width:180px;}
@media (max-width: 1024px){
  .cb-header .cb-controls{margin-left:0; width:100%; justify-content:flex-start;}
}
.cb-scroll .cb-compact th, .cb-scroll .cb-compact td{white-space:nowrap;}
.cb-scroll .cb-compact td:nth-child(6){white-space:normal; max-width:180px; overflow:hidden; text-overflow:ellipsis; vertical-align:top;}
/* Items: compact single line like Queued (ellipsis + full text on hover/View) */
.cb-scroll .cb-compact td.cb-items{white-space:nowrap; max-width:220px; overflow:hidden; text-overflow:ellipsis; vertical-align:middle;}
#liveTrackerBody td:nth-child(2){max-width:150px; overflow:hidden; text-overflow:ellipsis;}
#liveTrackerBody td:nth-child(4){max-width:70px; overflow:hidden; text-overflow:ellipsis;}
@media (max-width: 767px){
  .cb-min-720{min-width:680px;} .cb-min-860{min-width:860px;}
  .cb-compact td{padding:3px 4px !important; font-size:11px;}
  .cb-compact th{padding:4px 4px !important; font-size:11px;}
  /* Header controls stack under the title instead of crushing it */
  .cb-controls{float:none !important; display:flex; flex-wrap:wrap; gap:4px; margin-top:6px;}
  .cb-controls select, .cb-controls input{max-width:100%;}
  .cb-controls input#trackerSearch{flex:1 1 120px; width:auto; min-width:0;}
  /* Secondary columns tuck away on very small screens (full detail still on View) */
  #liveTrackerBody td:nth-child(2), #liveTrackerBody td:nth-child(4), #liveTrackerBody td:nth-child(10){max-width:90px; overflow:hidden; text-overflow:ellipsis;}
}
@media (max-width: 479px){
  .cb-min-720{min-width:640px;} .cb-min-860{min-width:800px;}
  /* Hide Type + Time on tiny phones; Location/Items/View carry the essentials */
  .cb-min-860 thead th:nth-child(4), .cb-min-860 tbody td:nth-child(4){display:none;}
  .cb-min-860 thead th:nth-child(10), .cb-min-860 tbody td:nth-child(10){display:none;}
}
@media (prefers-reduced-motion: reduce){ .cb-skel-row{animation:none;} }
</style>
    </div>
</div>
<!-- END ALL ORDERS OVERVIEW -->
<!-- LIVE SALES TRACKER: POS + website/other, live -> completed, with View -->
<div class="row" style="margin-bottom: 16px;">
    <div class="col-xs-12">
        <div class="table-header cb-header" style="background: #1a1a1a; color: #84cc16; padding: 6px 12px; font-weight: bold; font-size:13px;">
            <i class="icon-random"></i> Live Sales Tracker — POS &amp; Website (live → completed)
            <span class="cb-controls" style="float:right; font-weight:normal; font-size:12px;">
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
        <div class="cb-scroll" style="max-height: 320px; overflow-y: auto; border:1px solid #ddd; border-top:none;">
        <table class="table table-striped table-bordered table-hover cb-compact cb-min-860" style="width:100%; margin-bottom:0; font-size:12px;">
            <thead style="position:sticky; top:0; z-index:5;">
                <tr style="background:#f0f0f0;">
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">ID</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Ref</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Source</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Type</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Customer</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Location</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Items</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Total</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Status</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">Time</th>
                    <th style="position:sticky; top:0; background:#f0f0f0; padding:4px 6px;">View</th>
                </tr>
            </thead>
            <tbody id="liveTrackerBody">
                <tr><td colspan="11"><div class="cb-skel-row"></div></td></tr><tr><td colspan="11"><div class="cb-skel-row"></div></td></tr><tr><td colspan="11"><div class="cb-skel-row"></div></td></tr>
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
                <select id="dashRange" onchange="onDashRangeChange();" style="color:#333; padding:2px 6px;">
                    <option value="6h">Last 6 hrs</option>
                    <option value="12h">Last 12 hrs</option>
                    <option value="24h">Last 24 hrs</option>
                    <option value="today">Today</option>
                    <option value="week" selected>Last 7 days</option>
                    <option value="month">Last 30 days</option>
                    <option value="custom">Custom…</option>
                </select>
                <span id="dashCustom" style="display:none;">
                    <input type="datetime-local" id="dashFrom" style="color:#333; padding:2px 4px; max-width:170px;" /> → <input type="datetime-local" id="dashTo" style="color:#333; padding:2px 4px; max-width:170px;" />
                    <button class="btn btn-xs btn-primary" onclick="loadRichDashboard();loadLiveTracker();reloadLegacyStats();">Go</button>
                </span>
                <button class="btn btn-xs btn-default" onclick="loadRichDashboard();" title="Refresh"><i class="icon-refresh"></i></button>
                <button class="btn btn-xs btn-default" onclick="if(typeof setOrderSoundMuted==='function') setOrderSoundMuted(!isOrderSoundMuted());" data-order-sound-toggle title="Toggle new-order ding">🔊 Sound: On</button>
                <span id="dashUpdated" style="font-size:11px; color:#999;"></span>
            </span>
        </div>
        <div id="supplierPayables" style="padding:10px 12px; background:#fff8ec; border-bottom:1px solid #eee; font-size:12px;"></div>
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
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Source</th><th>Orders</th><th>Collected</th><th>Share</th></tr></thead><tbody id="richBySource"><tr><td colspan="4"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-map-marker"></i> By Order Type</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Type</th><th>Orders</th><th>Collected</th></tr></thead><tbody id="richByType"><tr><td colspan="3"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div>
    </div>
    <div class="col-sm-4">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-credit-card"></i> By Payment</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Method</th><th>Payments</th><th>Collected</th></tr></thead><tbody id="richByPayment"><tr><td colspan="3"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div>
    </div>
</div>
<div class="row" style="margin-bottom: 20px;">
    <div class="col-sm-7">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-time"></i> Sales by Hour</h4></div>
            <div class="widget-body"><div id="richByHour" style="display:flex; align-items:flex-end; gap:4px; height:140px; padding:8px; background:#fafafa; overflow-x:auto;"><span style="color:#999; font-size:12px;">Loading...</span></div></div></div>
    </div>
    <div class="col-sm-5">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-star orange"></i> Top Items (detailed)</h4></div>
            <div class="widget-body"><table class="table table-striped" style="margin:0; font-size:12px;"><thead><tr><th>Item</th><th>Qty</th><th>Revenue</th></tr></thead><tbody id="richTopItems"><tr><td colspan="3"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div>
</div>
<div class="row" style="margin-bottom: 20px;">
    <div class="col-xs-12">
        <div class="widget-box transparent"><div class="widget-header widget-header-flat"><h4 class="lighter"><i class="icon-list"></i> Recent Orders (delivery + items — View for full detail)</h4></div>
            <div class="widget-body"><table class="table table-striped table-bordered table-hover cb-compact" style="margin:0; font-size:12px;"><thead><tr><th>ID</th><th>Ref</th><th>Customer</th><th>Location</th><th>Total</th><th>View</th></tr></thead><tbody id="richRecent"><tr><td colspan="6"><div class="cb-skel-row"></div></td></tr></tbody></table></div></div>
    </div>
    </div>
</div>

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

</div><!-- /dashCanvas -->
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

        // Set onclicks (unbind first — rebinding each poll stacked duplicate handlers)
        $(".infobox-sales").off('click').on('click', function(){ WPOS.transactions.openTransactionList(totals.salerefs); });
        $(".infobox-refunds").off('click').on('click', function(){ WPOS.transactions.openTransactionList(totals.refundrefs); });
        $(".infobox-voids").off('click').on('click', function(){ WPOS.transactions.openTransactionList(totals.voidrefs); });
        $(".infobox-takings").off('click').on('click', function(){ WPOS.transactions.openTransactionList(totals.refs); });
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
        // Clear first — append-only caused duplicate rows on every refresh
        $("#popularitems").html('');

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

    // Shared neat hover card (hourly bars + flot graph + pie), following the cursor
    function cbTipShow(html, x, y){
        var tip = $('#cb-chart-tip');
        tip.html(html).show();
        var tw = tip.outerWidth(), th = tip.outerHeight();
        var lx = Math.min(x + 14, window.innerWidth - tw - 8);
        var ly = Math.min(y + 16, window.innerHeight - th - 8);
        tip.css({left: Math.max(8, lx), top: Math.max(8, ly)});
    }
    function cbTipHide(){ try { $('#cb-chart-tip').hide(); } catch(e){} }
    function bindHoverTip(sel, htmlFn){
        $(document).off('mouseenter.cbTip', sel).off('mouseleave.cbTip', sel).off('mousemove.cbTip', sel);
        $(document).on('mouseenter.cbTip', sel, function(e){
            var el = $(this);
            cbTipShow(htmlFn(el), e.clientX, e.clientY);
            el.on('mousemove.cbTipMove', function(ev){ cbTipShow(htmlFn(el), ev.clientX, ev.clientY); });
        }).on('mouseleave.cbTip', sel, function(){
            cbTipHide();
        });
    }
    if (!$('#cb-chart-tip').length) $('<div id="cb-chart-tip"></div>').appendTo('body');

    jQuery(function($) {

        // Chart hover Tooltip (kept for pie/graph fallback; rich cards via cb-tip below)
        var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
        var previousPoint = null;

        var tooltip = function (event, pos, item) {
            if(item) {
                // Neat card: date + series figures (pie keeps percent + amount)
                var d = new Date(item.datapoint[0]);
                var dStr = d.toLocaleDateString() + (d.getHours() || d.getMinutes() ? ' ' + d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '');
                if (item.series['percent']!=null){
                    var html = '<div class="t">' + (item.series['label'] || 'Slice') + '</div><div>Share: <span class="v">' + item.series['percent'].toFixed(2) + '%</span></div><div class="m">Amount: ' + WPOS.util.currencyFormat(item.series['data'][0][1]) + '</div>';
                    cbTipShow(html, pos.pageX, pos.pageY);
                } else {
                    var html2 = '<div class="t">' + dStr + '</div><div>' + (item.series['label'] || '') + ': <span class="v">' + WPOS.util.currencyFormat(item.datapoint[1]) + '</span></div>';
                    cbTipShow(html2, pos.pageX, pos.pageY);
                }
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
                cbTipHide();
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

        // All stats follow the shared range selector (6h/12h/24h/7d/30d/custom).
        // Today's Takings box keeps its own "today" window by label; everything else
        // (Popular, Graph, Pie, Sales-by-source) mirrors the selector so tallies agree.
        var rv = (typeof dashRangeVals === 'function') ? dashRangeVals() : getTimeVals("This Week");
        var interval = (rv[1] - rv[0]) <= 86400000 ? 3600000 : 86400000; // sub-day ranges → hourly points
        var req = {"stats/itemselling":{"stime":rv[0], "etime":rv[1]}, "stats/general":{"stime":rv[0], "etime":rv[1]}, "graph/general":{"stime":rv[0], "etime":rv[1], "interval":interval}};
        req["stats/takings"] = {"stime":rv[0], "etime":rv[1], "totals":true};
        var data = WPOS.sendJsonData("multi", JSON.stringify(req));
        // Load todays stats
        loadTodayStats(data['stats/general']);
        // load graph
        drawGraph(data['graph/general']);
        // initialize the initial pie chart (takings split for this range)
        generatePieChart(data["stats/takings"]);
        // load popular items
        loadPopularItems(data['stats/itemselling']);
        // hide loader
        WPOS.util.hideLoader();
    });

// --- Dashboard Orders Overview ---
var _dashFilter = 'all';
var _dashSeq = 0;
function loadDashboardOrders(filter){
    if (filter) _dashFilter = filter;
    var tbody = $('#dashOrdersBody');
    if (!tbody.length) return;
    // First paint only: skeleton (initial HTML already is skeleton; refreshes keep stale data)
    if (!tbody.attr('data-cb-sig') && tbody.text().indexOf('Loading') === -1) { /* keep current rows */ }
    var scroller = tbody.closest('div');
    var seq = (++_dashSeq);
    $.get('/api/poll.php?last=0' + (_dashFilter === 'website' ? '&source=website' : ''), function(data){
        if (seq !== _dashSeq) return; // stale response — drop (prevents out-of-order repaint / duplicates)
        if (data.error) { if (tbody.find('tr').length <= 1) tbody.html('<tr><td colspan="9" class="center text-danger">'+data.error+'</td></tr>'); return; }
        var sales = (data.result && data.result.sales) ? data.result.sales : [];
        // dedupe by ref (API should not duplicate, but overlapping polls could)
        try { var _seen = {}; sales = sales.filter(function(s){ var k = s.ref || s.id; if (_seen[k]) return false; _seen[k] = 1; return true; }); } catch(e) {}
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
                + '<td style="font-size:11px; max-width:140px; overflow:hidden; text-overflow:ellipsis;" title="'+(s.delivery_address ? s.delivery_address.replace(/"/g,'&quot;') : '')+'">'+(s.delivery_address || (s.table_number ? 'Table '+s.table_number : '-'))+'</td>'
                + '<td style="font-size:12px;" title="'+String(items).replace(/"/g,'&quot;')+'">'+items+'</td>'
                + '<td>'+s.total+'/=</td>'
                + '<td><span class="label label-'+statusClass+'">'+s.orderstatus+'</span></td>'
                + '<td style="font-size:12px;">'+s.received_at+'</td>'
                + '<td style="white-space:nowrap;">'
                + '<button class="btn btn-xs btn-info" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'preparing\')" title="Mark preparing"><i class="icon-fire"></i></button> '
                + '<button class="btn btn-xs btn-success" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'ready\')" title="Mark ready"><i class="icon-ok"></i></button> '
                + '<button class="btn btn-xs btn-default" onclick="updateDashboardOrderStatus(\''+s.ref+'\',\'completed\')" title="Mark completed"><i class="icon-check"></i></button> '
                + '<button class="btn btn-xs btn-primary" onclick="WPOS.transactions.openTransactionDialog(\''+s.ref+'\')" title="View delivery + items"><i class="icon-eye-open"></i> View</button> '
                + '<button class="btn btn-xs btn-danger" onclick="deleteDashboardOrder(\''+s.ref+'\')" title="Delete"><i class="icon-trash"></i></button>'
                + '</td>'
                + '</tr>';
        });
        if (!rows) rows = '<tr><td colspan="9" class="center">No orders yet.</td></tr>';
        // Signature-diffed patch: skip DOM write when unchanged (no flash), preserve scroll
        if (window.CBRefresh) {
            var st = scroller && scroller.length ? scroller.scrollTop() : 0;
            var sig = rows.length + ':' + (function(s){var h=0;for(var i=0;i<s.length;i++)h=(h*31+s.charCodeAt(i))|0;return h;})(rows);
            if (tbody.attr('data-cb-sig') !== sig) {
                tbody.attr('data-cb-sig', sig);
                requestAnimationFrame(function(){ tbody.html(rows); if (scroller && scroller.length) scroller.scrollTop(st); });
            }
        } else { tbody.html(rows); }
        try { var c = $('#dashOrdersCount'); if (c.length) c.text((sales||[]).length + ' shown'); } catch(e){}
    }, 'json').fail(function(){ /* keep stale rows on error — never blank the table */ });
}
function patchDashRowStatus(ref, status){
    // Instant, in-place status update — no full table repaint on every click
    try {
        var statusClass = {'queued':'warning','preparing':'info','ready':'success','completed':'default'}[status] || 'default';
        var rows = document.querySelectorAll('#dashOrdersBody tr');
        rows.forEach(function(tr){
            var btn = tr.querySelector('button[onclick*="' + ref + '"]');
            if (!btn) return;
            var cells = tr.querySelectorAll('td');
            if (cells.length >= 7) cells[6].innerHTML = '<span class="label label-' + statusClass + '">' + status + '</span>';
        });
    } catch(e) {}
}
function updateDashboardOrderStatus(ref, status){
    patchDashRowStatus(ref, status); // optimistic, in place
    WPOS.sendJsonData("orders/updatestatus", JSON.stringify({ref: ref, orderstatus: status}), function(r){
        if (r) setTimeout(function(){ loadDashboardOrders(_dashFilter); }, 400); // reconcile quietly (sig-diffed)
    });
}
function loadQuickTasks(){
    var wrap = $('#quickTasks');
    if (!wrap.length) return;
    var noop = function(){};
    var put = function(key, item){
        var html = wrap.html() || '';
        var warn = item.warn ? '#c00' : '#256029';
        var card = '<a data-qt="' + key + '" href="#!' + item.page + '" style="display:inline-block; min-width:150px; background:#fff; border:1px solid #ddd; border-radius:8px; padding:8px 10px; text-decoration:none;">'
            + '<div style="font-size:11px; color:#666;">' + item.label + '</div>'
            + '<div style="font-size:15px; font-weight:800; color:' + warn + ';">' + item.value + '</div>'
            + '<div style="font-size:11px; color:#888;">' + item.sub + '</div></a>';
        var el = wrap.find('[data-qt="' + key + '"]');
        if (el.length) { el.replaceWith(card); } else { wrap.append(card); }
    };
    try { WPOS.sendJsonDataAsync('suppliers/dashboard', JSON.stringify({}), function(d){
        if (d) put('supOver', { page: 'reports', label: 'Supplier payables overdue', value: WPOS.util.money(d.overdue || 0), sub: 'due in 7d ' + WPOS.util.money(d.due_7d || 0), warn: (d.overdue||0) > 0.005 });
    }, noop); } catch(e) {}
    try { WPOS.sendJsonDataAsync('stock/stats', JSON.stringify({}), function(s){
        if (s && s.kpis) put('lowStock', { page: 'stock', label: 'Low stock', value: (s.kpis.low_stock || 0) + ' items', sub: 'stock value ' + WPOS.util.money(s.kpis.stock_value || 0), warn: (s.kpis.low_stock || 0) > 0 });
    }, noop); } catch(e) {}
    try { WPOS.sendJsonDataAsync('customers/credit_report', JSON.stringify({}), function(res){
        if (!res) return;
        var od = 0, n = 0;
        (res || []).forEach(function(r){ n++; if (parseFloat(r.open_balance||0) > 0.005) od += parseFloat(r.open_balance||0); });
        put('custCredit', { page: 'customers', label: 'Customer credit owed', value: WPOS.util.money(od), sub: n + ' customers', warn: od > 0 });
    }, noop); } catch(e) {}
    try { $.get('/api/poll.php?last=0&source=website', function(data){
        var sales = (data.result && data.result.sales) ? data.result.sales : [];
        var open = sales.filter(function(s){ return (s.orderstatus || 'queued') !== 'completed'; }).length;
        put('webOpen', { page: 'websiteorders', label: 'Website orders open', value: open, sub: 'review & complete', warn: open > 0 });
    }, 'json'); } catch(e) {}
    try { WPOS.sendJsonDataAsync('suppliers/credit_report', JSON.stringify({}), function(res){
        if (!res) return;
        var n = 0; (res || []).forEach(function(r){ if (parseFloat(r.open_balance||0) > 0.005) n++; });
        put('supOwed', { page: 'suppliers', label: 'Suppliers owed', value: n + ' suppliers', sub: 'see payables', warn: n > 0 });
    }, noop); } catch(e) {}
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
        // dedupe by ref
        try { var _tseen = {}; list = list.filter(function(e){ var k = e.s.ref || e.s.id; if (_tseen[k]) return false; _tseen[k] = 1; return true; }); } catch(e) {}
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
            if (items.length > 40) items = items.substring(0, 40) + '...';
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
                + '<td style="font-size:11px; max-width:140px; overflow:hidden; text-overflow:ellipsis;" title="'+(s.delivery_address ? String(s.delivery_address).replace(/"/g,'&quot;') : '')+'">'+(s.delivery_address || (s.table_number ? 'Table '+s.table_number : '-'))+'</td>'
                + '<td class="cb-items" style="font-size:11px;" title="'+String(items).replace(/"/g,'&quot;')+'">'+items+'</td>'
                + '<td style="white-space:nowrap;">'+(s.total||0)+'/=</td>'
                + '<td><span class="label label-'+trackerStatusClass(st)+'">'+trackerStatusLabel(st)+'</span></td>'
                + '<td style="font-size:11px;">'+time+'</td>'
                + '<td><button class="btn btn-xs btn-primary" onclick="WPOS.transactions.openTransactionDialog(\''+s.ref+'\');">View</button></td>'
                + '</tr>';
        });
        if (!rows) rows = '<tr><td colspan="11" class="center">No sales in this view.</td></tr>';
        if (window.CBRefresh) {
            var tscroller = body.closest('div');
            var tst = tscroller && tscroller.length ? tscroller.scrollTop() : 0;
            var tsig = rows.length + ':' + (function(s){var h=0;for(var i=0;i<s.length;i++)h=(h*31+s.charCodeAt(i))|0;return h;})(rows);
            if (body.attr('data-cb-sig') !== tsig) {
                body.attr('data-cb-sig', tsig);
                requestAnimationFrame(function(){ body.html(rows); if (tscroller && tscroller.length) tscroller.scrollTop(tst); });
            }
        } else { body.html(rows); }
        try { $('#trackerCount').text(total + ' total · ' + list.length + ' shown'); } catch(e){}
    });
}

// --- Rich Reporting (POS + online) ---
// Shared range for Tracker + Rich + Graph + Pie + Popular (+ reports presets mirror this)
function dashRangeVals(){
    var r = ($('#dashRange').val() || 'week');
    var etime = new Date().getTime();
    var stime = etime;
    if (r === '6h') stime = etime - 6*3600*1000;
    else if (r === '12h') stime = etime - 12*3600*1000;
    else if (r === '24h') stime = etime - 24*3600*1000;
    else if (r === 'today') { var d = new Date(); d.setHours(0,0,0,0); stime = d.getTime(); }
    else if (r === 'month') stime = etime - 30*24*3600*1000;
    else if (r === 'custom') {
        var from = $('#dashFrom').val(), to = $('#dashTo').val();
        var f = from ? new Date(from).getTime() : (etime - 7*86400000);
        var tt = to ? new Date(to).getTime() : etime;
        if (!isNaN(f) && !isNaN(tt) && tt > f) { stime = f; etime = tt; }
        else { stime = etime - 7*86400000; }
    }
    else stime = etime - 7*24*3600*1000;
    return [stime, etime];
}
function reloadLegacyStats(){
    // Graph / Pie / Popular / Sales-by-source mirror the shared range selector
    try {
        var rv = dashRangeVals();
        var interval = (rv[1] - rv[0]) <= 86400000 ? 3600000 : 86400000;
        var req = {"stats/itemselling":{"stime":rv[0], "etime":rv[1]}, "stats/general":{"stime":rv[0], "etime":rv[1]}, "graph/general":{"stime":rv[0], "etime":rv[1], "interval":interval}, "stats/takings":{"stime":rv[0], "etime":rv[1], "totals":true}};
        var data = WPOS.sendJsonData("multi", JSON.stringify(req));
        if (!data) return;
        loadTodayStats(data['stats/general']);
        drawGraph(data['graph/general']);
        generatePieChart(data['stats/takings']);
        loadPopularItems(data['stats/itemselling']);
    } catch(e) {}
}
function onDashRangeChange(){
    $('#dashCustom').toggle($('#dashRange').val() === 'custom');
    if ($('#dashRange').val() === 'custom' && !$('#dashFrom').val()) {
        // default custom to last 7 days so Go works immediately
        var fmt = function(v){ return v.toISOString().slice(0,16); };
        var e = new Date(), s = new Date(Date.now() - 7*86400000);
        $('#dashFrom').val(fmt(s)); $('#dashTo').val(fmt(e));
    }
    loadRichDashboard(); loadLiveTracker(); reloadLegacyStats();
}
function renderSupplierPayables(){
    try {
        WPOS.sendJsonDataAsync('suppliers/dashboard', JSON.stringify({}), function(d){
            var el = $('#supplierPayables');
            if (!el.length || !d) return;
            var top = (d.top_suppliers || []).map(function(s){ return '<span style="display:inline-block;margin:2px 6px 2px 0;padding:3px 8px;background:#fff;border:1px solid #ddd;border-radius:12px;">' + (s.name || s.supplierid) + ': <b>' + WPOS.util.money(s.owed||0) + '</b></span>'; }).join('');
            el.html('<strong>Supplier payables — owed ' + WPOS.util.money(d.owed||0) + '</strong> ' +
                '· Overdue <b style="color:' + (parseFloat(d.overdue||0)>0.005?'#c00':'#2e7d32') + '">' + WPOS.util.money(d.overdue||0) + '</b>' +
                ' · Due in 7d ' + WPOS.util.money(d.due_7d||0) +
                ' · ' + (d.debtors||0) + ' suppliers owed' +
                ' · Purchases (30d) ' + WPOS.util.money(d.purchases_30d||0) +
                (top ? '<div style="margin-top:6px;">Top: ' + top + '</div>' : ''));
        });
    } catch(e) {}
}
function renderRichDashboard(d){
    if (!d || !d.kpis) return;
    // Skip re-render when the payload is identical (stale-while-revalidate)
    try {
        var _rsig = JSON.stringify(d);
        if (window._richSig === _rsig) return;
        window._richSig = _rsig;
    } catch(e) {}
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
    // Hourly bars — pixel heights (percent heights collapse inside flex wrappers → invisible bars)
    // Hover shows neat figures (revenue + orders + share) in a shared card
    var hourHtml = '';
    var maxRev = parseFloat(d.max_hour_revenue || 0) || 1;
    var barMaxPx = 110;
    var anyHour = false;
    for (var h = 0; h < 24; h++) {
        var rec = (d.by_hour && d.by_hour[String(h)]) ? d.by_hour[String(h)] : (d.by_hour && d.by_hour[h] ? d.by_hour[h] : null);
        var hh = rec ? parseFloat(rec.revenue || 0) : 0;
        var px = Math.max(hh > 0 ? 4 : 0, Math.round((hh / maxRev) * barMaxPx));
        if (hh > 0) anyHour = true;
        var label = (h < 10 ? '0' + h : h) + ':00';
        var share = maxRev > 0 && hh > 0 ? Math.round((hh / maxRev) * 100) : 0;
        hourHtml += '<div class="cb-hour" data-title="'+label+'" data-rev="'+hh+'" data-orders="'+(rec?rec.orders:0)+'" data-share="'+share+'" style="flex:1 0 28px; min-width:28px; height:110px; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; text-align:center; cursor:pointer;">'
            + '<div style="height:' + px + 'px; width:100%; max-width:22px; margin:0 auto; background:' + (hh > 0 ? '#84cc16' : '#e5e5e5') + '; border-radius:3px 3px 0 0;"></div>'
            + '<div style="font-size:9px; color:#888; margin-top:2px;">'+label+'</div></div>';
    }
    if (!anyHour) hourHtml = '<div style="color:#999;font-size:12px;">No completed sales in range.</div>';
    $('#richByHour').html(hourHtml);
    bindHoverTip('#richByHour .cb-hour', function(el){
        var rev = parseFloat(el.attr('data-rev') || 0);
        return '<div class="t">' + el.attr('data-title') + '</div><div>Revenue: <span class="v">' + fmt(rev) + '</span></div><div class="m">' + el.attr('data-orders') + ' orders · peak share ' + el.attr('data-share') + '%</div>';
    });
    var topHtml = '';
    (d.top_items || []).forEach(function(it){
        topHtml += '<tr><td>'+it.name+'</td><td>'+it.qty+'</td><td>'+fmt(it.revenue)+'</td></tr>';
    });
    $('#richTopItems').html(topHtml || '<tr><td colspan="3" class="center">No items sold in range.</td></tr>');
    // Order detail (delivery location + items on demand) — recent 10 from stats payload
    try {
        var rec = d.recent || [];
        var rh = '';
        if (!rec.length) rh = '<tr><td colspan="6" class="center">No orders in range.</td></tr>';
        rec.forEach(function(r){
            var loc = r.delivery_address || (r.table_number ? 'Table ' + r.table_number : '-');
            var cust = r.customer || '-';
            rh += '<tr><td>' + r.id + '</td><td>' + (r.ref || '-') + '</td><td>' + cust + '</td><td style="font-size:11px;" title="' + String(loc).replace(/"/g,'&quot;') + '">' + loc + '</td><td>' + (r.total || 0) + '/=</td><td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="WPOS.transactions.openTransactionDialog(\'' + r.ref + '\')">View</button></td></tr>';
        });
        $('#richRecent').html(rh);
    } catch(e) {}
    var now = new Date();
    $('#dashUpdated').text('Updated ' + now.toLocaleTimeString());
}
function loadRichDashboard(){
    renderSupplierPayables();
    var vals = dashRangeVals();
    var data = WPOS.sendJsonData('stats/dashboard', JSON.stringify({stime: vals[0], etime: vals[1]}));
    if (data) renderRichDashboard(data);
}
$(document).ready(function(){
    try { if (typeof initOrderSound === 'function') initOrderSound(); } catch(e){}
    // Flicker-free polling: keep stale data, pause when hidden, back off on error, cleanup on nav
    if (window.CBRefresh) {
        var p1 = CBRefresh.schedule(function(ok, fail){
            try { loadDashboardOrders(_dashFilter); } catch(e){ try{fail();return;}catch(_){} }
            ok();
            return { abort: function(){} };
        }, 15000);
        var p2 = CBRefresh.schedule(function(ok, fail){
            try { loadLiveTracker(); } catch(e){ try{fail();return;}catch(_){} }
            ok();
            return { abort: function(){} };
        }, 15000);
        var p3 = CBRefresh.schedule(function(ok, fail){
            try { loadRichDashboard(); } catch(e){ try{fail();return;}catch(_){} }
            ok();
            return { abort: function(){} };
        }, 30000);
        var p4 = CBRefresh.schedule(function(ok, fail){
            try { loadQuickTasks(); } catch(e){ try{fail();return;}catch(_){} }
            ok();
            return { abort: function(){} };
        }, 60000);
        // run immediately (skeleton already on screen — silent refresh fills in)
        setTimeout(loadRichDashboard, 800);
        setTimeout(loadQuickTasks, 1000);
        renderSupplierPayables();
        setTimeout(loadLiveTracker, 1200);
        setTimeout(function(){ loadDashboardOrders(_dashFilter); }, 500);
    } else {
        setTimeout(loadDashboardOrders, 500); window._p1 = setInterval(loadDashboardOrders, 15000);
        setTimeout(loadRichDashboard, 800); window._p2 = setInterval(loadRichDashboard, 30000);
        setTimeout(loadLiveTracker, 1200); window._p3 = setInterval(loadLiveTracker, 15000);
    }
});

// --- Dashboard drag/resize/grid layout (opt-in) ---
var DASH_LAYOUT_KEY = 'cb_dash_layout_v2';
var dashEditMode = false;
function dashCaptureLayout(){
    var canvas = $('#dashCanvas');
    var co = canvas.offset();
    var layout = {};
    canvas.find('.widget-box').each(function(i, el){
        var $el = $(el);
        var id = $el.attr('data-dw') || ('dw' + i);
        $el.attr('data-dw', id);
        var off = $el.offset();
        layout[id] = {
            left: Math.round((off.left - co.left)/20)*20,
            top: Math.round((off.top - co.top)/20)*20,
            width: Math.round($el.outerWidth()/20)*20,
            height: Math.round($el.outerHeight()/20)*20
        };
    });
    return layout;
}
function dashApplyLayout(layout){
    var canvas = $('#dashCanvas');
    for (var id in layout){
        var $el = canvas.find('[data-dw="' + id + '"]');
        if (!$el.length) continue;
        var l = layout[id];
        $el.css({ position: 'absolute', left: l.left + 'px', top: l.top + 'px', width: l.width + 'px', height: l.height + 'px' });
    }
    // canvas height
    var maxBottom = 0;
    canvas.find('.widget-box').each(function(){ maxBottom = Math.max(maxBottom, $(this).position().top + $(this).outerHeight()); });
    canvas.css('min-height', (maxBottom + 40) + 'px');
}
function dashEnableEdit(){
    if (dashEditMode) return;
    dashEditMode = true;
    var canvas = $('#dashCanvas');
    canvas.css('position','relative');
    var saved = null;
    try { saved = localStorage.getItem(DASH_LAYOUT_KEY); } catch(e){}
    if (!saved) {
        var init = dashCaptureLayout();
        try { localStorage.setItem(DASH_LAYOUT_KEY, JSON.stringify(init)); } catch(e){}
        saved = JSON.stringify(init);
    }
    dashApplyLayout(JSON.parse(saved));
    canvas.find('.widget-box').each(function(){
        var $el = $(this);
        if ($el.data('cb-drag')) return;
        $el.data('cb-drag', true);
        if ($.fn.draggable) $el.draggable({ grid: [20,20], containment: 'parent', handle: '.widget-header', cursor: 'move', opacity: 0.85 });
        if ($.fn.resizable) $el.resizable({ grid: [20,20], handles: 'se', minWidth: 220, minHeight: 80 });
        $el.on('dragstop.dash resizestop.dash', function(){
            try { localStorage.setItem(DASH_LAYOUT_KEY, JSON.stringify(dashCaptureLayout())); } catch(e){}
        });
    });
    $('#dashEditBtn').text('Done').removeClass('btn-primary').addClass('btn-success');
    $('#dashResetBtn').show();
}
function dashDisableEdit(){
    if (!dashEditMode) return;
    dashEditMode = false;
    var canvas = $('#dashCanvas');
    canvas.find('.widget-box').each(function(){
        var $el = $(this);
        if ($el.data('ui-draggable')) try { $el.draggable('destroy'); } catch(e){}
        if ($el.data('ui-resizable')) try { $el.resizable('destroy'); } catch(e){}
        $el.removeData('cb-drag');
        $el.off('.dash');
    });
    // keep applied layout (don't revert) so it persists visually
    $('#dashEditBtn').text('Customize layout').removeClass('btn-success').addClass('btn-primary');
}
function dashResetLayout(){
    try { localStorage.removeItem(DASH_LAYOUT_KEY); } catch(e){}
    location.reload();
}
// auto-apply saved layout on load (without edit mode handles)
$(function(){
    var saved = null;
    try { saved = localStorage.getItem(DASH_LAYOUT_KEY); } catch(e){}
    if (saved) {
        try { dashApplyLayout(JSON.parse(saved)); } catch(e) { console.error('dash layout apply failed', e); }
    }
    // inject toggle buttons into page header area
    var btns = '<div style="margin:8px 0; text-align:right;">'
        + '<button id="dashEditBtn" class="btn btn-primary btn-xs" onclick="dashToggleEdit();">Customize layout</button> '
        + '<button id="dashResetBtn" class="btn btn-default btn-xs" onclick="dashResetLayout();" style="display:none;">Reset layout</button>'
        + '</div>';
    $('#dashCanvas').before(btns);
});
function dashToggleEdit(){ if (dashEditMode) dashDisableEdit(); else dashEnableEdit(); }

// --- End Dashboard Orders Overview ---

</script>