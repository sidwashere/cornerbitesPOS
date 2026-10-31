<!-- WallacePOS: Copyright (c) 2024 Corner Bites <info@cornerbites.co.ke> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Website Menu Sync
    </h1>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="alert alert-info">
    <i class="icon-info-sign"></i>
    This syncs the menu from <strong>cornerbites.co.ke</strong> into the POS inventory.
    Categories and items are created if they don't exist; existing items are skipped (idempotent).
</div>

<div class="well">
    <button class="btn btn-primary" onclick="runMenuSync();"><i class="icon-refresh"></i> Sync Now</button>
    <span id="syncstatus" style="margin-left: 15px;"></span>
</div>

<div id="syncresult" style="margin-top: 20px;"></div>

</div>
</div>

<script>
function runMenuSync(){
    var btn = $('#syncstatus').prev('button');
    btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Syncing...');
    $('#syncresult').html('');
    $.get('api/import_items.php', function(data){
        btn.prop('disabled', false).html('<i class="icon-refresh"></i> Sync Now');
        if (data.ok) {
            $('#syncresult').html(
                '<div class="alert alert-success">'
                + '<strong>Sync complete!</strong><br>'
                + 'Categories: ' + data.categories_created + ' created, ' + data.categories_reused + ' reused<br>'
                + 'Items: ' + data.items_created + ' created, ' + data.items_skipped + ' skipped (already exist)'
                + '</div>'
            );
        } else {
            $('#syncresult').html('<div class="alert alert-danger">Sync failed: ' + (data.error || 'Unknown error') + '</div>');
        }
    }, 'json').fail(function(){
        btn.prop('disabled', false).html('<i class="icon-refresh"></i> Sync Now');
        $('#syncresult').html('<div class="alert alert-danger">Request failed. Check network.</div>');
    });
}
</script>
