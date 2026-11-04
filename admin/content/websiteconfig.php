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
        <tr><td><strong>POS Webhook URL</strong></td><td>https://pos.cornerbites.co.ke/api/webhooks.php?platform=website</td></tr>
        <tr><td><strong>Webhook Method</strong></td><td>POST (JSON)</td></tr>
        <tr><td><strong>CORS Origin</strong></td><td>cornerbites.co.ke, www.cornerbites.co.ke</td></tr>
    </table>
</div>

<div class="well">
    <h4>WhatsApp Orders</h4>
    <p>WhatsApp orders are now handled via direct chat from the website.
       No server-side notification queue is needed.</p>
    <p>Customers initiate a WhatsApp chat with the business number after placing
       an order, with a pre-filled order message. The order is still pushed to
       the POS via the webhook as usual.</p>
    <table class="table table-bordered">
        <tr><td><strong>Business WhatsApp</strong></td><td>Configured in Website Content &rarr; General Settings</td></tr>
        <tr><td><strong>Status</strong></td><td><span class="label label-success">Active (direct chat)</span></td></tr>
    </table>
</div>

<div class="well">
    <h4>Quick Links</h4>
    <a href="https://cornerbites.co.ke/" target="_blank" class="btn btn-info btn-sm"><i class="icon-globe"></i> Open Website</a>
    <a href="https://cornerbites.co.ke/" target="_blank" class="btn btn-success btn-sm"><i class="icon-shopping-cart"></i> Test Order Flow</a>
</div>

</div>
</div>
