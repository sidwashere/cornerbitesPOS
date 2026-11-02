<!-- WallacePOS: Copyright (c) 2024 Corner Bites <info@cornerbites.co.ke> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        WhatsApp Configuration
    </h1>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="alert alert-info">
    <i class="icon-info-sign"></i>
    <strong>WhatsApp orders now go directly to chat.</strong>
    When a customer places an order on the website, their browser opens a
    WhatsApp chat with the business number and a pre-filled order message.
    The customer sends it themselves — no server-side
    notification queue is needed. The order is still pushed to the POS via the
    webhook as usual.
</div>

<div class="well">
    <h4>Business WhatsApp Number</h4>
    <p>Set the WhatsApp number that customers chat with. This is configured in
       <strong>Website Content &rarr; General Settings &rarr; WhatsApp Number</strong>
       (international format, e.g. <code>254185112315</code>).</p>
    <p>Current number: <strong id="wa_current_number">254185112315</strong></p>
</div>

</div>
</div>

<script>
$(document).ready(function(){
    // Load the current WhatsApp number from website content config
    $.get('api/get_website_content.php', function(data){
        if (data && data.whatsapp) {
            $('#wa_current_number').text(data.whatsapp);
        }
    }, 'json').fail(function(){});
});
</script>