<!-- Table QR Codes: print per-table dine-in plaques -->
<div class="page-header">
    <h1 style="display:inline-block;">Table QR Codes</h1>
    <small><i class="icon-double-angle-right"></i> scan → menu → order → dashboard as Table N</small>
    <span style="float:right;">
        <button class="btn btn-primary btn-sm" onclick="window.print();"><i class="icon-print"></i> Print plaques</button>
    </span>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="table-header">
            Generate table plaques — each QR is unique to its table (<code>…/website/?table=N#menu</code>)
        </div>
        <div style="background:#f7f7f5; padding:12px; margin-bottom:12px;">
            <label>Number of tables:&nbsp;
                <input id="tableCount" type="number" min="1" max="50" value="8" style="width:70px;" />&nbsp;
            </label>
            <button class="btn btn-sm btn-success" onclick="generateTableQRs();"><i class="icon-qrcode"></i> Generate</button>
            <span id="tableUrlPreview" style="font-size:12px; color:#666; margin-left:10px;"></span>
        </div>
        <div id="tableQRGrid" style="display:flex; flex-wrap:wrap; gap:16px;"></div>
        <p style="color:#999; font-size:12px; margin-top:10px;">Print, cut, and place one plaque per table. Orders scan as dine-in for that table and appear in Dashboard / Live Sales Tracker / Website Orders / Kitchen with the table number.</p>
    </div>
</div>

<style>
  @media print {
    .no-print { display:none !important; }
    body { background:#fff; }
    #tableQRGrid { page-break-inside:auto; }
    .table-plaque { page-break-inside:avoid; }
  }
  .table-plaque { width:220px; border:2px solid #84cc16; border-radius:16px; padding:14px; text-align:center; background:#fff; }
  .table-plaque h2 { margin:0; font-size:28px; font-weight:800; }
  .table-plaque img { width:180px; height:180px; image-rendering:pixelated; }
  .table-plaque .scan { font-size:12px; font-weight:700; color:#84cc16; }
  .table-plaque .url { font-size:10px; color:#888; word-break:break-all; }
</style>

<script>
function tableQRUrl(n){
    var origin = window.location.protocol + '//' + window.location.host;
    return origin + '/website/?table=' + n + '#menu';
}
function generateTableQRs(){
    var n = parseInt(($('#tableCount').val() || '8'), 10);
    if (isNaN(n) || n < 1) n = 8;
    if (n > 50) n = 50;
    var html = '';
    for (var i = 1; i <= n; i++) {
        var url = tableQRUrl(i);
        html += '<div class="table-plaque">'
            + '<h2>TABLE ' + i + '</h2>'
            + '<div class="scan">Scan to Order</div>'
            + '<img src="/api/table_qr.php?table=' + i + '&size=8" alt="QR for Table ' + i + '" />'
            + '<div class="url">' + url + '</div>'
            + '<div style="margin-top:8px;"><a class="btn btn-xs btn-default" href="/api/table_qr.php?table=' + i + '" target="_blank">Open QR</a> '
            + '<a class="btn btn-xs btn-default" href="/api/table_qr.php?table=' + i + '" download="table-' + i + '-qr.png">Download</a></div>'
            + '</div>';
    }
    $('#tableQRGrid').html(html);
    $('#tableUrlPreview').text('Example (Table 1): ' + tableQRUrl(1));
}
$(document).ready(function(){ generateTableQRs(); });
</script>
