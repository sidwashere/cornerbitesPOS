<!-- WallacePOS: Copyright (c) 2024 Corner Bites <info@cornerbites.co.ke> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Website Content Manager
    </h1>
    <button class="btn btn-success btn-sm pull-right" onclick="saveWebsiteContent();"><i class="icon-save align-top bigger-125"></i> Save All Changes</button>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="alert alert-info">
    <i class="icon-info-sign"></i>
    Edit your website content below. Changes are saved to the server and reflected on <strong>cornerbites.co.ke</strong> immediately.
    Use the tabs to switch between sections.
</div>

<ul class="nav nav-tabs" id="contentTabs">
    <li class="active"><a href="#tab-offers" data-toggle="tab">Offers & Deals</a></li>
    <li><a href="#tab-prices" data-toggle="tab">Menu Prices</a></li>
    <li><a href="#tab-ads" data-toggle="tab">Sectional Ads</a></li>
    <li><a href="#tab-general" data-toggle="tab">General Settings</a></li>
</ul>

<div class="tab-content" style="padding: 20px; border: 1px solid #ddd; border-top: none;">

    <!-- Offers Tab -->
    <div class="tab-pane active" id="tab-offers">
        <h4>Launch Offers & Deals</h4>
        <p class="text-muted">Edit the special offers shown on the website. Each offer has a title, description, price, and badge.</p>
        <div id="offersList"></div>
        <button class="btn btn-primary btn-sm" onclick="addOffer();"><i class="icon-plus"></i> Add Offer</button>
    </div>

    <!-- Prices Tab -->
    <div class="tab-pane" id="tab-prices">
        <h4>Menu Item Prices</h4>
        <p class="text-muted">Update prices for menu items. These sync with the POS inventory.</p>
        <table class="table table-bordered table-striped" id="pricesTable">
            <thead><tr><th>Item Name</th><th>Price (KSh)</th><th>Category</th><th>Actions</th></tr></thead>
            <tbody id="pricesBody"></tbody>
        </table>
        <button class="btn btn-primary btn-sm" onclick="addPriceItem();"><i class="icon-plus"></i> Add Item</button>
    </div>

    <!-- Ads Tab -->
    <div class="tab-pane" id="tab-ads">
        <h4>Sectional Advertisements</h4>
        <p class="text-muted">Manage promotional banners and ads displayed on the website.</p>
        <div id="adsList"></div>
        <button class="btn btn-primary btn-sm" onclick="addAd();"><i class="icon-plus"></i> Add Ad</button>
    </div>

    <!-- General Tab -->
    <div class="tab-pane" id="tab-general">
        <h4>General Website Settings</h4>
        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label">Hero Title</label>
                <div class="controls">
                    <input type="text" id="gen_hero_title" class="input-xxlarge" placeholder="TASTY MEALS, EVERY TIME!" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Hero Subtitle</label>
                <div class="controls">
                    <input type="text" id="gen_hero_subtitle" class="input-xxlarge" placeholder="Freshly prepared Kenyan fast food..." />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">WhatsApp Number</label>
                <div class="controls">
                    <input type="text" id="gen_whatsapp" class="input-large" placeholder="254185112315" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Phone Number</label>
                <div class="controls">
                    <input type="text" id="gen_phone" class="input-large" placeholder="0185112315" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Delivery Area</label>
                <div class="controls">
                    <input type="text" id="gen_delivery" class="input-xxlarge" placeholder="Utawala, Kincar area" />
                </div>
            </div>
        </div>
    </div>

</div>

<div id="content_status" style="margin-top: 15px;"></div>

</div>
</div>

<script>
var websiteContent = { offers: [], prices: [], ads: [], general: {} };

function loadWebsiteContent(){
    $.get('api/website_content.php', function(data){
        if (data) {
            websiteContent = data;
            renderOffers();
            renderPrices();
            renderAds();
            renderGeneral();
        }
    }, 'json').fail(function(){});
}

function renderOffers(){
    var html = '';
    (websiteContent.offers || []).forEach(function(o, i){
        html += '<div class="well" style="margin-bottom: 10px;">'
            + '<div class="row">'
            + '<div class="col-sm-3"><label>Title</label><input type="text" class="form-control offer-title" data-idx="'+i+'" value="'+(o.title||'')+'" /></div>'
            + '<div class="col-sm-4"><label>Description</label><input type="text" class="form-control offer-desc" data-idx="'+i+'" value="'+(o.desc||'')+'" /></div>'
            + '<div class="col-sm-2"><label>Price</label><input type="text" class="form-control offer-price" data-idx="'+i+'" value="'+(o.price||'')+'" /></div>'
            + '<div class="col-sm-2"><label>Badge</label><input type="text" class="form-control offer-badge" data-idx="'+i+'" value="'+(o.badge||'')+'" /></div>'
            + '<div class="col-sm-1"><label>&nbsp;</label><button class="btn btn-danger btn-xs" onclick="removeOffer('+i+')"><i class="icon-trash"></i></button></div>'
            + '</div></div>';
    });
    $('#offersList').html(html || '<p class="text-muted">No offers yet. Click "Add Offer" to create one.</p>');
}

function addOffer(){
    websiteContent.offers = websiteContent.offers || [];
    websiteContent.offers.push({title: 'New Offer', desc: 'Description', price: '0', badge: 'NEW'});
    renderOffers();
}

function removeOffer(idx){
    websiteContent.offers.splice(idx, 1);
    renderOffers();
}

function renderPrices(){
    var html = '';
    (websiteContent.prices || []).forEach(function(p, i){
        html += '<tr>'
            + '<td><input type="text" class="form-control price-name" data-idx="'+i+'" value="'+(p.name||'')+'" /></td>'
            + '<td><input type="text" class="form-control price-val" data-idx="'+i+'" value="'+(p.price||'')+'" style="width:80px;" /></td>'
            + '<td><input type="text" class="form-control price-cat" data-idx="'+i+'" value="'+(p.category||'')+'" /></td>'
            + '<td><button class="btn btn-danger btn-xs" onclick="removePrice('+i+')"><i class="icon-trash"></i></button></td>'
            + '</tr>';
    });
    $('#pricesBody').html(html || '<tr><td colspan="4" class="center text-muted">No items. Click "Add Item".</td></tr>');
}

function addPriceItem(){
    websiteContent.prices = websiteContent.prices || [];
    websiteContent.prices.push({name: 'New Item', price: '0', category: 'General'});
    renderPrices();
}

function removePrice(idx){
    websiteContent.prices.splice(idx, 1);
    renderPrices();
}

function renderAds(){
    var html = '';
    (websiteContent.ads || []).forEach(function(a, i){
        html += '<div class="well" style="margin-bottom: 10px;">'
            + '<div class="row">'
            + '<div class="col-sm-3"><label>Title</label><input type="text" class="form-control ad-title" data-idx="'+i+'" value="'+(a.title||'')+'" /></div>'
            + '<div class="col-sm-5"><label>Content</label><textarea class="form-control ad-content" data-idx="'+i+'" rows="2">'+(a.content||'')+'</textarea></div>'
            + '<div class="col-sm-2"><label>Placement</label><select class="form-control ad-placement" data-idx="'+i+'">'
            + '<option value="hero"'+(a.placement=='hero'?' selected':'')+'>Hero</option>'
            + '<option value="sidebar"'+(a.placement=='sidebar'?' selected':'')+'>Sidebar</option>'
            + '<option value="footer"'+(a.placement=='footer'?' selected':'')+'>Footer</option>'
            + '<option value="popup"'+(a.placement=='popup'?' selected':'')+'>Popup</option>'
            + '</select></div>'
            + '<div class="col-sm-1"><label>Active</label><input type="checkbox" class="ad-active" data-idx="'+i+'" '+(a.active?'checked':'')+' /></div>'
            + '<div class="col-sm-1"><label>&nbsp;</label><button class="btn btn-danger btn-xs" onclick="removeAd('+i+')"><i class="icon-trash"></i></button></div>'
            + '</div></div>';
    });
    $('#adsList').html(html || '<p class="text-muted">No ads yet. Click "Add Ad" to create one.</p>');
}

function addAd(){
    websiteContent.ads = websiteContent.ads || [];
    websiteContent.ads.push({title: 'New Ad', content: 'Ad content here', placement: 'hero', active: true});
    renderAds();
}

function removeAd(idx){
    websiteContent.ads.splice(idx, 1);
    renderAds();
}

function renderGeneral(){
    var g = websiteContent.general || {};
    $('#gen_hero_title').val(g.hero_title || '');
    $('#gen_hero_subtitle').val(g.hero_subtitle || '');
    $('#gen_whatsapp').val(g.whatsapp || '');
    $('#gen_phone').val(g.phone || '');
    $('#gen_delivery').val(g.delivery || '');
}

function collectFormData(){
    // Collect offers
    var offers = [];
    $('#offersList .well').each(function(i){
        offers.push({
            title: $(this).find('.offer-title').val(),
            desc: $(this).find('.offer-desc').val(),
            price: $(this).find('.offer-price').val(),
            badge: $(this).find('.offer-badge').val()
        });
    });
    websiteContent.offers = offers;

    // Collect prices
    var prices = [];
    $('#pricesBody tr').each(function(){
        var name = $(this).find('.price-name').val();
        if (name) {
            prices.push({
                name: name,
                price: $(this).find('.price-val').val(),
                category: $(this).find('.price-cat').val()
            });
        }
    });
    websiteContent.prices = prices;

    // Collect ads
    var ads = [];
    $('#adsList .well').each(function(i){
        ads.push({
            title: $(this).find('.ad-title').val(),
            content: $(this).find('.ad-content').val(),
            placement: $(this).find('.ad-placement').val(),
            active: $(this).find('.ad-active').is(':checked')
        });
    });
    websiteContent.ads = ads;

    // Collect general
    websiteContent.general = {
        hero_title: $('#gen_hero_title').val(),
        hero_subtitle: $('#gen_hero_subtitle').val(),
        whatsapp: $('#gen_whatsapp').val(),
        phone: $('#gen_phone').val(),
        delivery: $('#gen_delivery').val()
    };
}

function saveWebsiteContent(){
    collectFormData();
    $('#content_status').html('<i class="icon-spinner icon-spin"></i> Saving...');
    $.ajax({
        url: 'api/save_website_content.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(websiteContent),
        success: function(r){
            if (r.ok) {
                $('#content_status').html('<span class="text-success"><i class="icon-ok"></i> ' + r.message + '</span>');
            } else {
                $('#content_status').html('<span class="text-danger"><i class="icon-remove"></i> ' + (r.error || 'Failed') + '</span>');
            }
        },
        error: function(){
            $('#content_status').html('<span class="text-danger"><i class="icon-remove"></i> Request failed</span>');
        }
    });
}

$(document).ready(function(){ loadWebsiteContent(); });
</script>
