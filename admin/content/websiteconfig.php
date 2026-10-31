<!-- WallacePOS: Copyright (c) 2024 Corner Bites <info@cornerbites.co.ke> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Website Settings
    </h1>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="well">
    <h4>Website Integration</h4>
    <table class="table table-bordered">
        <tr><td><strong>Website URL</strong></td><td>https://cornerbites.co.ke/</td></tr>
        <tr><td><strong>POS Webhook URL</strong></td><td>https://pos.cornerbites.co.ke/webhooks/website</td></tr>
        <tr><td><strong>Webhook Method</strong></td><td>POST (JSON)</td></tr>
        <tr><td><strong>CORS Origin</strong></td><td>cornerbites.co.ke, www.cornerbites.co.ke</td></tr>
    </table>
</div>

<div class="well">
    <h4>WhatsApp Notifications</h4>
    <p>Configure WhatsApp notifications in <code>docs/whatsapp.json</code> on the server.</p>
    <p>Provider: CallMeBot (free). Get your API key at <a href="https://www.callmebot.com/blog/free-api-whatsapp-messages/" target="_blank">callmebot.com</a></p>
    <table class="table table-bordered">
        <tr><td><strong>Config File</strong></td><td>/docs/whatsapp.json</td></tr>
        <tr><td><strong>Status</strong></td><td><span class="label label-warning">Disabled (needs setup)</span></td></tr>
        <tr><td><strong>Processor</strong></td><td>api/process_whatsapp.php (cron every 2 min)</td></tr>
    </table>
</div>

<div class="well">
    <h4>Quick Links</h4>
    <a href="https://cornerbites.co.ke/" target="_blank" class="btn btn-info btn-sm"><i class="icon-globe"></i> Open Website</a>
    <a href="https://cornerbites.co.ke/" target="_blank" class="btn btn-success btn-sm"><i class="icon-shopping-cart"></i> Test Order Flow</a>
</div>

</div>
</div>
