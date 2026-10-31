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
    Configure WhatsApp notifications for new orders. Uses <strong>CallMeBot</strong> (free).
    Get your API key at <a href="https://www.callmebot.com/blog/free-api-whatsapp-messages/" target="_blank">callmebot.com</a>
</div>

<div class="well">
    <form id="waconfigform" class="form-horizontal">
        <div class="control-group">
            <label class="control-label">Enabled</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" id="wa_enabled" /> Enable WhatsApp notifications
                </label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Provider</label>
            <div class="controls">
                <select id="wa_provider">
                    <option value="callmebot">CallMeBot</option>
                    <option value="webhook">Custom Webhook</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Phone Number</label>
            <div class="controls">
                <input type="text" id="wa_phone" placeholder="+2547XXXXXXXX" class="input-xlarge" />
                <span class="help-inline">Your WhatsApp number (with country code)</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">API Key</label>
            <div class="controls">
                <input type="text" id="wa_apikey" placeholder="CallMeBot API key" class="input-xlarge" />
                <span class="help-inline">From CallMeBot activation message</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Recipients</label>
            <div class="controls">
                <input type="text" id="wa_recipients" placeholder="+2547XXXXXXXX, +2547YYYYYYYY" class="input-xlarge" />
                <span class="help-inline">Comma-separated phone numbers to notify</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Max Attempts</label>
            <div class="controls">
                <input type="number" id="wa_max_attempts" value="5" class="input-small" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Retry Backoff (seconds)</label>
            <div class="controls">
                <input type="number" id="wa_backoff" value="60" class="input-small" />
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="button" class="btn btn-primary" onclick="saveWhatsAppConfig();"><i class="icon-save"></i> Save Configuration</button>
                <span id="wa_status" style="margin-left: 15px;"></span>
            </div>
        </div>
    </form>
</div>

<div class="well">
    <h4>Test Notification</h4>
    <p>Send a test WhatsApp message to verify your configuration.</p>
    <button class="btn btn-success" onclick="testWhatsApp();"><i class="icon-envelope"></i> Send Test Message</button>
    <span id="wa_test_status" style="margin-left: 15px;"></span>
</div>

</div>
</div>

<script>
function loadWhatsAppConfig(){
    $.get('api/get_whatsapp_config.php', function(data){
        if (data && !data.error) {
            $('#wa_enabled').prop('checked', !!data.enabled);
            $('#wa_provider').val(data.provider || 'callmebot');
            $('#wa_phone').val(data.callmebot ? data.callmebot.phone : '');
            $('#wa_apikey').val(data.callmebot ? data.callmebot.apikey : '');
            $('#wa_recipients').val((data.recipients || []).join(', '));
            $('#wa_max_attempts').val(data.max_attempts || 5);
            $('#wa_backoff').val(data.retry_backoff_seconds || 60);
        }
    }, 'json').fail(function(){});
}
function saveWhatsAppConfig(){
    var payload = {
        enabled: $('#wa_enabled').is(':checked'),
        provider: $('#wa_provider').val(),
        phone: $('#wa_phone').val().trim(),
        apikey: $('#wa_apikey').val().trim(),
        recipients: $('#wa_recipients').val().trim(),
        max_attempts: parseInt($('#wa_max_attempts').val()) || 5,
        retry_backoff_seconds: parseInt($('#wa_backoff').val()) || 60,
    };
    $('#wa_status').html('<i class="icon-spinner icon-spin"></i> Saving...');
    $.ajax({
        url: 'api/save_whatsapp_config.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(r){
            if (r.ok) {
                $('#wa_status').html('<span class="text-success"><i class="icon-ok"></i> ' + r.message + '</span>');
            } else {
                $('#wa_status').html('<span class="text-danger"><i class="icon-remove"></i> ' + (r.error || 'Failed') + '</span>');
            }
        },
        error: function(){
            $('#wa_status').html('<span class="text-danger"><i class="icon-remove"></i> Request failed</span>');
        }
    });
}
function testWhatsApp(){
    $('#wa_test_status').html('<i class="icon-spinner icon-spin"></i> Sending...');
    $.get('api/process_whatsapp.php', function(r){
        $('#wa_test_status').html('<span class="text-info">Sent: ' + r.sent + ', Failed: ' + r.failed + ', Skipped: ' + r.skipped + '</span>');
    }, 'json').fail(function(){
        $('#wa_test_status').html('<span class="text-danger">Request failed</span>');
    });
}
$(document).ready(function(){ loadWhatsAppConfig(); });
</script>
