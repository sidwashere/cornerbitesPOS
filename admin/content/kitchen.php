<div class="row">
    <div class="col-xs-12">
        <h3 class="header blue lighter bigger">
            <i class="icon-fire"></i>&nbsp;Kitchen Display
        </h3>
        <p>The kitchen display runs as its own full-screen terminal. Open it in a new tab for the best experience, or view it embedded below.</p>
        <p>
            <a class="btn btn-sm btn-primary" href="/kitchen/" target="_blank"><i class="icon-external-link"></i>&nbsp;Open Kitchen Display (full screen)</a>
            <button class="btn btn-sm btn-default" onclick="$('#kitchen-frame').attr('src','/kitchen/');"><i class="icon-refresh"></i>&nbsp;Reload</button>
        </p>
        <hr/>
        <iframe id="kitchen-frame" src="/kitchen/" style="width:100%; height:70vh; border:1px solid #ccc; border-radius:4px;"></iframe>
    </div>
</div>
