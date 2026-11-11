<!-- Website CMS — mature content manager for the public website -->
<div class="page-header">
    <h1 style="margin-right:20px; display:inline-block;"><i class="icon-globe"></i> Website CMS</h1>
    <span class="btn btn-default btn-sm pull-right" onclick="openLiveSite();" style="margin-right:6px;"><i class="icon-eye-open"></i> View Site</span>
    <button class="btn btn-success btn-sm pull-right" onclick="saveCms();"><i class="icon-save align-top bigger-125"></i> Save &amp; Publish</button>
    <span id="cms-status" style="float:right; margin-right:10px; font-size:12px; color:#666;"></span>
</div>

<style>
.cb-tabs{display:flex; flex-wrap:wrap; gap:4px; padding:8px 4px 0; margin:0 0 0; border-bottom:2px solid #e3e3d8;}
.cb-tabs li{list-style:none;}
.cb-tabs a{display:inline-block; padding:7px 16px; border-radius:8px 8px 0 0; color:#555; font-weight:600; font-size:13px; text-decoration:none; border:1px solid transparent; border-bottom:none;}
.cb-tabs a:hover{background:#f2f4f0; color:#222;}
.cb-tabs a.active{background:#fff; border-color:#e3e3d8; color:#0f5a2e;}
.cb-pane{display:none; padding:14px; background:#fff; border:1px solid #ddd; border-top:none; border-radius:0 0 8px 8px; margin:0 0 16px;}
.cb-pane.active{display:block;}
</style>
<ul class="cb-tabs">
    <li><a href="#cms-tab-general" data-toggle="tab" class="active">General</a></li>
    <li><a href="#cms-tab-menu" data-toggle="tab">Products &amp; Menu</a></li>
    <li><a href="#cms-tab-cats" data-toggle="tab">Categories</a></li>
    <li><a href="#cms-tab-offers" data-toggle="tab">Offers</a></li>
</ul>
<style>#cms-tab-general label,#cmsproductdialog label{display:block; font-size:12px;} #cms-tab-general input.form-control,#cmsproductdialog input.form-control{margin-bottom:8px;}</style>

    <div class="cb-pane active" id="cms-tab-general">
        <div class="row">
            <div class="col-sm-6">
                <h4>Business identity</h4>
                <label>Site name <input type="text" id="cms-site-name" class="form-control" style="width:100%; display:block;" /></label>
                <label>Hero title <input type="text" id="cms-site-hero-title" class="form-control" style="width:100%; display:block;" /></label>
                <label>Hero subtitle <textarea id="cms-site-hero-subtitle" class="form-control" style="width:100%; height:60px;"></textarea></label>
                <label>Tagline <input type="text" id="cms-site-tagline" class="form-control" style="width:100%; display:block;" /></label>
            </div>
            <div class="col-sm-6">
                <h4>Contact &amp; ordering</h4>
                <label>Phone <input type="text" id="cms-site-phone" class="form-control" style="width:100%; display:block;" /> WhatsApp <input type="text" id="cms-site-whatsapp" class="form-control" style="width:100%; display:block;" /></label>
                <label>Address <input type="text" id="cms-site-address" class="form-control" style="width:100%; display:block;" /></label>
                <label>Email <input type="text" id="cms-site-email" class="form-control" style="width:100%; display:block;" /> Hours <input type="text" id="cms-site-hours" class="form-control" style="width:100%; display:block;" /></label>
                <label>Delivery area <input type="text" id="cms-site-delivery-area" class="form-control" style="width:100%; display:block;" /> Delivery time <input type="text" id="cms-site-delivery-time" class="form-control" style="width:100%; display:block;" /></label>
                <label>Payment note <input type="text" id="cms-site-payment-note" class="form-control" style="width:100%; display:block;" /></label>
                <label>M-Pesa Buy Goods Till <input type="text" id="cms-site-mpesa-till" class="form-control" style="width:100%; display:block;" placeholder="1860556" /></label>
                <label>Delivery fee (KSh, every online order) <input type="text" id="cms-site-delivery-fee" class="form-control" style="width:100%; display:block;" /></label>
                <label>Live website URL (root of your domain) <input type="text" id="cms-site-website-url" class="form-control" style="width:100%; display:block;" placeholder="https://cornerbites.co.ke/" /></label>
                            </div>
        </div>
        <div class="row" style="margin-top:10px;">
            <div class="col-sm-6">
                <h4>Announcement bar</h4>
                <label><input type="checkbox" id="cms-site-announcement-enabled" /> Show announcement</label>
                <label>Text <input type="text" id="cms-site-announcement" class="form-control" style="width:100%; display:block;" /></label>
                <h4 style="margin-top:14px;">SEO</h4>
                <label>Title <input type="text" id="cms-site-seo-title" class="form-control" style="width:100%; display:block;" /></label>
                <label>Description <textarea id="cms-site-seo-desc" class="form-control" style="width:100%; height:50px;"></textarea></label>
            </div>
            <div class="col-sm-6">
                <h4>Images (logo, hero gallery)</h4>
                <label>Logo URL <input type="text" id="cms-site-logo" class="form-control" style="width:100%; display:block;" onchange="$('#cms-logo-preview').attr('src', this.value);"/></label>
                <img id="cms-logo-preview" style="max-height:60px; border:1px solid #ddd; border-radius:6px; margin:4px 0;" alt=""/>
                <label>Gallery images (one URL per line)</label>
                <textarea id="cms-site-gallery" class="form-control" style="width:100%; height:70px;"></textarea>
            </div>
        </div>
    </div>

    <div class="cb-pane" id="cms-tab-menu">
        <div style="margin-bottom:8px;">
            <button class="btn btn-primary btn-sm" onclick="openProductDialog();"><i class="icon-plus"></i> Add product</button>
            <button class="btn btn-success btn-sm" onclick="exportProducts();"><i class="icon-cloud-download align-top bigger-125"></i> Export CSV</button>
            <button class="btn btn-success btn-sm" onclick="openImportDialog();"><i class="icon-cloud-upload align-top bigger-125"></i> Import CSV</button>
            <button class="btn btn-danger btn-sm" onclick="deleteSelectedProducts();" style="display:none;" id="cms-del-sel-btn"><i class="icon-trash"></i> Delete selected</button>
            <input type="text" id="cms-prod-search" placeholder="Search products..." style="width:160px; display:inline-block; margin-left:8px;" oninput="filterProducts(this.value)" />
            <span style="font-size:12px; color:#666; margin-left:8px;">One editor for everything — saving updates the till, kitchen and website at once.</span>
        </div>
        <p class="text-muted" style="font-size:11px; margin:0 0 6px;">POS categories (used on the till) are managed under <strong>Products → Categories</strong>. Website categories (pills on the public site) are managed in the Categories tab below.</p>
        <div class="cb-scroll" style="max-height:460px;"><table id="cms-products-table" class="table table-striped table-bordered" style="font-size:12px; min-width:1080px;">
            <thead><tr><th style="width:28px;"><input type="checkbox" id="cms-prod-checkall"/></th><th>Image</th><th>ID</th><th>Name</th><th>Price</th><th>POS category</th><th>Supplier</th><th>Tax</th><th>Stockcode</th><th>Website</th><th>Actions</th></tr></thead>
            <tbody id="cms-products-body"></tbody>
        </table></div>
    </div>

    <div class="cb-pane" id="cms-tab-cats">
        <h4 style="margin-top:0;">Website categories (pills on the public site)</h4>
        <p class="text-muted" style="font-size:12px;">These drive the menu category pills on the website.</p>
        <div id="cms-cats-list"></div>
        <button class="btn btn-primary btn-sm" onclick="addCategory();"><i class="icon-plus"></i> Add website category</button>

        <hr style="margin:16px 0;"/>
        <h4>POS categories (till tray &amp; kitchen)</h4>
        <p class="text-muted" style="font-size:12px;">Managed here so the till, kitchen and website stay in sync. Images set here show on the till tray and the website menu.</p>
        <div class="cb-scroll" style="max-height:320px;"><table id="cms-poscats-table" class="table table-striped table-bordered" style="font-size:12px; min-width:600px;">
            <thead><tr><th>ID</th><th>Name</th><th># Items</th><th>Image</th><th>Actions</th></tr></thead>
            <tbody id="cms-poscats-body"></tbody>
        </table></div>
        <button class="btn btn-primary btn-sm" style="margin-top:6px;" onclick="openPosCategoryDialog();"><i class="icon-plus"></i> Add POS category</button>
    </div>

    <div class="cb-pane" id="cms-tab-offers">
        <div id="cms-offers-list"></div>
        <button class="btn btn-primary btn-sm" onclick="addOffer();"><i class="icon-plus"></i> Add offer</button>
    </div>

<div id="cmsproductdialog" class="hide" style="padding:14px; min-width:520px;">
    <input type="hidden" id="cms-item-id" />
    <input type="hidden" id="cms-item-webid" />
    <div class="tabbable" style="min-width: 480px; min-height: 420px;">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#cmsitem-details" data-toggle="tab">Details</a></li>
            <li><a href="#cmsitem-web" data-toggle="tab">Website</a></li>
            <li><a href="#cmsitem-options" data-toggle="tab">Options</a></li>
        </ul>
        <div class="tab-content" style="min-height: 380px;">
            <div class="tab-pane active in" id="cmsitem-details">
                <table>
                    <tr><td style="text-align:right;"><label>Name:&nbsp;</label></td><td><input id="cms-item-name" type="text" class="form-control" style="width:100%;"/></td></tr>
                    <tr><td style="text-align:right;"><label>Alternate Name:&nbsp;</label></td><td><input id="cms-item-altname" type="text" class="form-control" style="width:100%;"/><br/><small>Alternate language name</small></td></tr>
                    <tr><td style="text-align:right;"><label>Description:&nbsp;</label></td><td><textarea id="cms-item-description" class="form-control" style="width:100%; height:55px;"></textarea></td></tr>
                    <tr><td style="text-align:right;"><label>Image URL:&nbsp;</label></td><td><input id="cms-item-image" type="text" class="form-control" style="width:100%;" onchange="$('#cms-item-preview').attr('src', this.value);"/></td></tr>
                    <tr><td></td><td>
                        <img id="cms-item-preview" style="max-height:80px; max-width:120px; border:1px solid #ddd; border-radius:6px; margin:4px 0;" alt=""/>
                        <input type="file" id="cms-item-file" accept="image/*" style="display:inline-block;" />
                        <button class="btn btn-xs btn-info" onclick="uploadItemImage();"><i class="icon-cloud-upload"></i> Upload</button>
                        <div id="cms-upload-progress-wrap" style="display:none; margin:6px 0 2px;">
                            <div style="height:10px; background:#e5e5e5; border-radius:5px; overflow:hidden;">
                                <div id="cms-upload-bar" style="height:100%; width:0%; background:linear-gradient(90deg,#3a87ad,#6fb3d2); transition:width .12s linear;"></div>
                            </div>
                            <div id="cms-upload-status" style="font-size:11px; color:#555; margin-top:3px;"></div>
                        </div>
                    </td></tr>
                    <tr><td style="text-align:right;"><label>Unit Cost:&nbsp;</label></td><td><input id="cms-item-cost" type="text" value="0" class="form-control" style="width:100%;"/></td></tr>
                    <tr><td style="text-align:right;"><label>Unit Price:&nbsp;</label></td><td><input id="cms-item-price" type="text" value="0" class="form-control" style="width:100%;"/></td></tr>
                    <tr><td style="text-align:right;"><label>Tax:&nbsp;</label></td><td><select id="cms-item-tax" class="form-control" style="width:100%;"></select></td></tr>
                    <tr><td style="text-align:right;"><label>Default Qty:&nbsp;</label></td><td><input id="cms-item-qty" type="text" value="1" class="form-control" style="width:40%; display:inline-block;" /> <select id="cms-item-unit" class="form-control" style="width:55%; display:inline-block;"><option>Pcs</option><option>Kgs</option><option>Grams</option></select> <button type="button" class="btn btn-xs btn-default" onclick="addCustomUnit('cms-item-unit');" title="Add your own unit">+</button></td></tr>
                    <tr><td style="text-align:right;"><label>Stockcode:&nbsp;</label></td><td><input id="cms-item-code" type="text" class="form-control" style="width:100%;"/></td></tr>
                    <tr><td style="text-align:right;"><label>POS Category:&nbsp;</label></td><td><select id="cms-item-poscategory" class="form-control" style="width:100%;"></select></td></tr>
                    <tr><td style="text-align:right;"><label>Supplier:&nbsp;</label></td><td><select id="cms-item-supplier" class="form-control" style="width:100%;"></select></td></tr>
                </table>
            </div>
            <div class="tab-pane" id="cmsitem-web">
                <div id="cms-item-linked-wrap" style="display:none;">
                    <label>Link to existing POS product <select id="cms-item-linked" class="form-control" style="width:100%;" onchange="onLinkedChange(this.value);"></select></label>
                    <small class="text-muted">Pick an existing till product to reuse it, or leave on "— create new —" to add a new one.</small>
                </div>
                <div id="cms-item-linked-static" style="display:none; font-size:12px; color:#666; margin:4px 0;"></div>
                <label>Website category <select id="cms-item-category" class="form-control" style="width:100%;"></select></label>
                <label>Badge <input type="text" id="cms-item-badge" class="form-control" style="width:100%; display:block;" /></label>
                <label>Tag <input type="text" id="cms-item-tag" class="form-control" style="width:100%; display:block;" /></label>
                <label>Sort order <input type="text" id="cms-item-order" class="form-control" style="width:30%; display:inline-block;" /> <label style="display:inline-block;"><input type="checkbox" id="cms-item-visible" /> Visible on site</label></label>
                <p class="text-muted" style="font-size:11px; margin-top:8px;">Name, price, image and description are pushed to the POS (till + kitchen) when you save. The website reads name/price/image/description straight from the POS for linked items.</p>
            </div>
            <div class="tab-pane" id="cmsitem-options">
                <div class="form-group">
                    <div class="col-sm-4"><label>Item Type:</label></div>
                    <div class="col-sm-8">
                        <select id="cms-item-type" class="form-control" style="width:100%;">
                            <option value="general">General</option>
                            <option value="food">Food</option>
                            <option value="beverage">Beverage</option>
                        </select>
                        <br/><small>Used for kitchen terminal dispatch</small>
                    </div>
                </div>
                <div class="space-4"></div>
                <div class="form-group">
                    <div class="col-sm-12"><label>Simple Modifiers:</label></div>
                    <table class="table table-stripped table-responsive" style="margin-bottom:0; padding-left:10px; margin-right:10px;">
                        <thead class="table-header smaller"><tr><th><small>Qty</small></th><th><small>Min Qty</small></th><th><small>Max Qty</small></th><th><small>Name</small></th><th><small>Price</small></th><th></th></tr></thead>
                        <tbody id="cms-modtable"></tbody>
                    </table>
                    <button style="float:right; margin-right:8px;" class="btn btn-primary btn-xs" onclick="addItemModifier();">Add</button>
                    <div class="col-sm-12" style="margin-top:8px;"><label>Select Modifiers:</label></div>
                    <table class="table table-stripped table-responsive" style="margin-bottom:0; padding-left:10px; margin-right:10px;">
                        <tbody id="cms-selmodtable"></tbody>
                    </table>
                    <button style="float:right; margin-right:8px;" class="btn btn-primary btn-xs" onclick="addSelectItemModifier();">Add</button>
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="saveProductDialog();">Save &amp; publish everywhere</button>
        <button class="btn btn-default btn-sm" onclick="$('#cmsproductdialog').dialog('close')">Cancel</button>
    </div>
</div>

<div id="cmsposcatdialog" class="hide" style="padding:14px; min-width:420px;">
    <input type="hidden" id="cms-poscat-id" />
    <label>Name <input type="text" id="cms-poscat-name" class="form-control" style="width:100%; display:block;" /></label>
    <label>Image URL <input type="text" id="cms-poscat-image" class="form-control" style="width:100%; display:block;" onchange="$('#cms-poscat-preview').attr('src', this.value);"/></label>
    <div style="margin:4px 0;">
        <img id="cms-poscat-preview" style="max-height:60px; border:1px solid #ddd; border-radius:6px; margin:4px 0;" alt=""/>
        <input type="file" id="cms-poscat-file" accept="image/*" style="display:inline-block;" />
        <button class="btn btn-xs btn-info" onclick="uploadPosCategoryImage();"><i class="icon-cloud-upload"></i> Upload</button>
        <button class="btn btn-xs btn-default" onclick="useTopSellerPosImage();">Use top seller</button>
        <button class="btn btn-xs btn-default" onclick="$('#cms-poscat-image').val(''); $('#cms-poscat-preview').attr('src','');">Clear</button>
    </div>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="savePosCategory();">Save</button>
        <button class="btn btn-default btn-sm" onclick="$('#cmsposcatdialog').dialog('close')">Cancel</button>
    </div>
</div>

<div id="cms-modalloader" class="hide" style="width: 360px; height: 320px; text-align: center;">
    <img id="cms-modalloader_img" style="width: 128px; height: auto;" src="/admin/assets/images/cloud_loader.gif"/>
    <div id="cms-modalloader_prog" class="progress progress-striped active">
        <div class="progress-bar" id="cms-modalloader_progbar" style="width: 100%;"></div>
    </div>
    <h4 id="cms-modalloader_status">Initializing...</h4>
    <h5 id="cms-modalloader_substatus"></h5>
    <button id="cms-modalloader_cbtn" class="btn btn-primary" style="display: none; margin-top:40px;" onclick="$('#cms-modalloader').dialog('close');">Close</button>
</div>

<!-- page specific plugin scripts -->
<link rel="stylesheet" href="/admin/assets/js/csv-import/lib/jquery.ezdz.min.css"/>
<script type="text/javascript" src="/admin/assets/js/csv-import/lib/jquery.ezdz.min.js"></script>
<script type="text/javascript" src="/admin/assets/js/csv-import/lib/jquery-sortable-min.js"></script>
<script type="text/javascript" src="/admin/assets/js/csv-import/lib/jquery.csv-0.71.min.js"></script>
<script type="text/javascript" src="/admin/assets/js/csv-import/csv.import.tool.js"></script>

<script type="text/javascript">
var CMS = null, CMS_ITEMS = [], POS_ITEMS = {}, POS_CATS = {}, POS_SUPS = {};
var CMS_WEB_BY_POS = {};
var cmsProdSearch = '';

function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escId(s){ return String(s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'"); }
function cmsGet(id){ var el = document.getElementById(id); return el ? el.value : ''; }
function cmsSet(id, v){ var el = document.getElementById(id); if (el) el.value = v || ''; }
function taxName(id){ try { var r = WPOS.getTaxTable().rules[id]; return r ? r.name : 'Not Defined'; } catch(e){ return '-'; } }
function ensureUnitOption(selectId, unit){
    if (!unit) return;
    var sel = document.getElementById(selectId);
    if (!sel) return;
    var opts = Array.prototype.map.call(sel.options, function(o){ return o.value; });
    if (opts.indexOf(unit) === -1) {
        var o = document.createElement('option'); o.value = unit; o.textContent = unit; sel.appendChild(o);
    }
}
function addCustomUnit(selectId){
    var u = prompt('Enter a new unit of measurement (e.g. Litres, Boxes):');
    if (!u) return;
    u = u.trim();
    if (!u) return;
    ensureUnitOption(selectId, u);
    document.getElementById(selectId).value = u;
}

function loadPosTables(){
    try {
        var data = WPOS.sendJsonData("multi", JSON.stringify({"items/get":"", "suppliers/get":"", "categories/get":""}));
        if (!data) return false;
        POS_ITEMS = data['items/get'] || {};
        POS_SUPS = data['suppliers/get'] || {};
        POS_CATS = data['categories/get'] || {};
        // normalise id-keyed objects (in case arrays slipped through)
        if (Array.isArray(POS_ITEMS)) { var o={}; POS_ITEMS.forEach(function(x){ o[x.id]=x; }); POS_ITEMS=o; }
        if (Array.isArray(POS_SUPS)) { var o={}; POS_SUPS.forEach(function(x){ o[x.id]=x; }); POS_SUPS=o; }
        if (Array.isArray(POS_CATS)) { var o={}; POS_CATS.forEach(function(x){ o[x.id]=x; }); POS_CATS=o; }
        return true;
    } catch(e){ return false; }
}

function webByPos(){ CMS_WEB_BY_POS = {}; CMS_ITEMS.forEach(function(it){ if (it.linked_itemid) CMS_WEB_BY_POS[it.linked_itemid] = it; }); }

function loadCms(){
    WPOS.util.showLoader();
    var _posOk = loadPosTables();
    if (!_posOk) { console.warn('POS tables could not be loaded (items/suppliers/categories).'); }
    // Enrich POS categories with top-seller images
    try { WPOS.sendJsonDataAsync('categories/images', JSON.stringify({}), function(res){ if (res && res.length) { res.forEach(function(c){ POS_CAT_TOP[c.id] = c; }); } }, function(){}); } catch(e) {}
    $.get('/api/website_content.php', function(data){
        try {
            CMS = data || {};
            if (!CMS.site) CMS.site = {};
            if (!CMS.menu) CMS.menu = { items: [] };
            if (!Array.isArray(CMS.menu.items)) CMS.menu.items = [];
            if (!CMS.categories) CMS.categories = [];
            if (!CMS.offers) CMS.offers = [];
            CMS_ITEMS = CMS.menu.items.map(function(it){ return Object.assign({}, it); });
            webByPos();
            fillGeneral(); fillProducts(); fillCategories(); fillPosCategories(); fillOffers(); fillCatSelects(); fillLinkedSelect(); fillPosSelects();
        if (WPOS.isAdmin()) { $('#cms-del-sel-btn').show(); }
        } catch (e) {
            console.error('CMS load failed:', e && e.message, e);
            alert('Could not load the CMS data: ' + (e && e.message ? e.message : e));
        }
        WPOS.util.hideLoader();
    }).fail(function(xhr){ WPOS.util.hideLoader(); alert('Could not reach the CMS API (HTTP ' + (xhr && xhr.status) + ').'); });
}

// ---------- General ----------
function fillGeneral(){
    var s = CMS.site;
    cmsSet('cms-site-name', s.name); cmsSet('cms-site-hero-title', s.hero_title); cmsSet('cms-site-hero-subtitle', s.hero_subtitle);
    cmsSet('cms-site-tagline', s.tagline); cmsSet('cms-site-phone', s.phone); cmsSet('cms-site-whatsapp', s.whatsapp);
    cmsSet('cms-site-address', s.address); cmsSet('cms-site-email', s.email); cmsSet('cms-site-hours', s.hours);
    cmsSet('cms-site-delivery-area', s.delivery_area); cmsSet('cms-site-delivery-time', s.delivery_time);
    cmsSet('cms-site-delivery-fee', s.delivery_fee || 0);
    cmsSet('cms-site-payment-note', s.payment_note); cmsSet('cms-site-mpesa-till', s.mpesa_till || '1860556'); cmsSet('cms-site-website-url', s.website_url || '');
    $('#cms-site-announcement-enabled').prop('checked', !!s.announcement_enabled);
    cmsSet('cms-site-announcement', s.announcement);
    cmsSet('cms-site-seo-title', s.seo_title); cmsSet('cms-site-seo-desc', s.seo_desc);
    cmsSet('cms-site-logo', s.logo); $('#cms-logo-preview').attr('src', s.logo || '');
    cmsSet('cms-site-gallery', (s.gallery || []).join('\n'));
}
function collectGeneral(){
    var s = (CMS.site && typeof CMS.site === 'object') ? JSON.parse(JSON.stringify(CMS.site)) : {};
    s.name = cmsGet('cms-site-name'); s.hero_title = cmsGet('cms-site-hero-title'); s.hero_subtitle = cmsGet('cms-site-hero-subtitle');
    s.tagline = cmsGet('cms-site-tagline'); s.phone = cmsGet('cms-site-phone'); s.whatsapp = cmsGet('cms-site-whatsapp');
    s.address = cmsGet('cms-site-address'); s.email = cmsGet('cms-site-email'); s.hours = cmsGet('cms-site-hours');
    s.delivery_area = cmsGet('cms-site-delivery-area'); s.delivery_time = cmsGet('cms-site-delivery-time');
    s.delivery_fee = parseFloat(cmsGet('cms-site-delivery-fee')) || 0;
    s.payment_note = cmsGet('cms-site-payment-note'); s.mpesa_till = cmsGet('cms-site-mpesa-till'); s.website_url = cmsGet('cms-site-website-url');
    s.announcement_enabled = $('#cms-site-announcement-enabled').is(':checked');
    s.announcement = cmsGet('cms-site-announcement');
    s.seo_title = cmsGet('cms-site-seo-title'); s.seo_desc = cmsGet('cms-site-seo-desc');
    s.logo = cmsGet('cms-site-logo');
    s.gallery = cmsGet('cms-site-gallery').split('\n').map(function(x){ return x.trim(); }).filter(Boolean);
    if (CMS.site && CMS.site.carousels) s.carousels = CMS.site.carousels;
    return s;
}

// ---------- Products & Menu (merged) ----------
function fillPosSelects(){
    // tax
    var taxsel = $('#cms-item-tax');
    taxsel.html('');
    try { var rules = WPOS.getTaxTable().rules; for (var k in rules) taxsel.append('<option class="taxid-'+rules[k].id+'" value="'+rules[k].id+'">'+rules[k].name+'</option>'); } catch(e){}
    // suppliers
    var supsel = $('#cms-item-supplier');
    supsel.html('<option class="supid-0" value="0">None</option>');
    for (var k in POS_SUPS) supsel.append('<option class="supid-'+POS_SUPS[k].id+'" value="'+POS_SUPS[k].id+'">'+POS_SUPS[k].name+'</option>');
    // pos categories
    var catsel = $('#cms-item-poscategory');
    catsel.html('<option class="catid-0" value="0">None</option>');
    for (var k in POS_CATS) catsel.append('<option class="catid-'+POS_CATS[k].id+'" value="'+POS_CATS[k].id+'">'+POS_CATS[k].name+'</option>');
}
function fillCatSelects(){
    var c = $('#cms-item-category');
    c.html('<option value="">— none —</option>');
    (CMS.categories || []).forEach(function(cat){ c.append('<option value="' + cat.id + '">' + cat.label + '</option>'); });
}
function fillLinkedSelect(){
    var sel = $('#cms-item-linked');
    sel.html('<option value="">— create new POS product —</option>');
    var sorted = Object.keys(POS_ITEMS).sort(function(a,b){ return String(POS_ITEMS[a].name).localeCompare(String(POS_ITEMS[b].name)); });
    sorted.forEach(function(id){
        var p = POS_ITEMS[id];
        var tag = (CMS_WEB_BY_POS[id] ? ' (already on website)' : '');
        sel.append('<option value="' + id + '">' + (p.name || id) + tag + '</option>');
    });
}

function productOrder(p, w){
    if (w && w.order) return w.order;
    if (w) return 1000 + parseInt(p.id, 10);
    return 9999 + parseInt(p.id, 10);
}

function fillProducts(){
    webByPos();
    var rows = '';
    var ids = Object.keys(POS_ITEMS).sort(function(a,b){
        var wa = CMS_WEB_BY_POS[a], wb = CMS_WEB_BY_POS[b];
        return productOrder(POS_ITEMS[a], wa) - productOrder(POS_ITEMS[b], wb);
    });
    ids.forEach(function(id){
        var p = POS_ITEMS[id];
        var w = CMS_WEB_BY_POS[id];
        var img = (p.image || (w && w.image) || '');
        rows += '<tr data-posid="' + id + '">'
            + '<td><input type="checkbox" class="cms-prod-cb" data-posid="' + id + '"/></td>'
            + '<td>' + (img ? '<img src="' + img + '" style="max-height:36px;max-width:54px;border-radius:4px;"/>' : '—') + '</td>'
            + '<td>' + id + '</td>'
            + '<td><strong>' + esc(p.name || '-') + '</strong><br/><small>' + esc((p.description || '').slice(0,60)) + '</small></td>'
            + '<td>' + WPOS.util.money(p.price || 0) + '</td>'
            + '<td>' + esc(POS_CATS[p.categoryid] ? POS_CATS[p.categoryid].name : 'None') + '</td>'
            + '<td>' + esc(POS_SUPS[p.supplierid] ? POS_SUPS[p.supplierid].name : 'None') + '</td>'
            + '<td>' + esc(taxName(p.taxid)) + '</td>'
            + '<td>' + esc(p.code || '') + '</td>'
            + '<td>' + (w ? ('<span class="label label-success">Web</span> <input type="checkbox" ' + (w.visible !== false ? 'checked' : '') + ' onchange="toggleVisible(' + id + ', this.checked)" title="Visible on website"/> <small>order ' + (w.order || 0) + (w.badge ? ' · ' + esc(w.badge) : '') + (w.category ? ' · ' + esc(w.category) : '') + '</small>')
                     : ('<span class="label label-grey">not on website</span>')) + '</td>'
            + '<td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="openProductDialog(' + id + ')">Edit</button>' + (WPOS.isAdmin() ? ' <button class="btn btn-xs btn-danger" onclick="removeProduct(' + id + ')">Delete</button>' : '') + '</td>'
            + '</tr>';
    });
    // unlinked website items (no POS product)
    (CMS_ITEMS || []).forEach(function(it){
        if (it.linked_itemid && POS_ITEMS[it.linked_itemid]) return;
        rows += '<tr data-webid="' + escId(it.id) + '" class="warning">'
            + '<td></td>'
            + '<td>' + (it.image ? '<img src="' + it.image + '" style="max-height:36px;max-width:54px;border-radius:4px;"/>' : '—') + '</td>'
            + '<td>—</td>'
            + '<td><strong>' + esc(it.name || '-') + '</strong> <span class="label label-warning">no POS product</span><br/><small>' + esc((it.description || '').slice(0,60)) + '</small></td>'
            + '<td>' + WPOS.util.money(it.price || 0) + '</td>'
            + '<td>—</td><td>—</td><td>—</td><td>—</td>'
            + '<td><span class="label label-warning">not on till</span> <small>order ' + (it.order || 0) + (it.badge ? ' · ' + esc(it.badge) : '') + '</small></td>'
            + '<td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="openProductDialog(null, \'' + escId(it.id) + '\')">Edit</button>' + (WPOS.isAdmin() ? ' <button class="btn btn-xs btn-danger" onclick="removeProduct(null, \'' + escId(it.id) + '\')">Delete</button>' : '') + '</td>'
            + '</tr>';
    });
    $('#cms-products-body').html(rows || '<tr><td colspan="11" class="center">No products yet. Click "Add product".</td></tr>');
    // check-all
    $('#cms-prod-checkall').off('change').on('change', function(){
        var on = this.checked;
        $('#cms-products-body .cms-prod-cb').prop('checked', on);
    });
}

function filterProducts(q){
    q = (q || '').toLowerCase();
    $('#cms-products-body tr').each(function(){
        var txt = $(this).text().toLowerCase();
        $(this).toggle(!q || txt.indexOf(q) !== -1);
    });
}

function toggleVisible(posId, checked){
    var w = CMS_WEB_BY_POS[posId];
    if (!w) return;
    w.visible = !!checked;
    CMS.menu = { items: CMS_ITEMS };
    _postCms(function(res){ if (!res || !res.ok) cmsStatus('Website save failed: ' + (res && res.error)); });
}

function openProductDialog(posId, webId){
    var dlg = $('#cmsproductdialog');
    if (!dlg.hasClass('ui-dialog-content')) dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '620px', modal: true, autoOpen: false, title: 'Product' });
    fillPosSelects(); fillCatSelects(); fillLinkedSelect();
    // reset
    $('#cms-item-id').val(posId || '');
    $('#cms-item-webid').val(webId || '');
    ['name','altname','description','image','cost','price','code','badge','tag','order'].forEach(function(k){ cmsSet('cms-item-' + k, ''); });
    $('#cms-item-qty').val('1'); $('#cms-item-tax').val('1');
    $('#cms-item-poscategory').val('0'); $('#cms-item-supplier').val('0');
    $('#cms-item-category').val(''); $('#cms-item-linked').val('');
    $('#cms-item-type').val('general');
    $('#cms-item-visible').prop('checked', true);
    $('#cms-item-preview').attr('src', '');
    // tabs reset
    $('.tabbable a[href="#cmsitem-details"]').tab('show');
    // linked select visibility
    if (posId) {
        $('#cms-item-linked-wrap').hide();
        $('#cms-item-linked-static').show().text('POS product #' + posId);
    } else {
        $('#cms-item-linked-wrap').show();
        $('#cms-item-linked-static').hide().text('');
    }

    var w = null, p = null;
    if (posId && POS_ITEMS[posId]) {
        p = POS_ITEMS[posId];
        w = CMS_WEB_BY_POS[posId];
    } else if (webId) {
        w = (CMS_ITEMS || []).filter(function(x){ return x.id === webId; })[0];
        if (w && w.linked_itemid && POS_ITEMS[w.linked_itemid]) { p = POS_ITEMS[w.linked_itemid]; posId = w.linked_itemid; $('#cms-item-id').val(posId); $('#cms-item-linked-wrap').hide(); $('#cms-item-linked-static').show().text('POS product #' + posId); }
    }

    if (p) {
        cmsSet('cms-item-name', p.name);
        cmsSet('cms-item-altname', p.alt_name);
        cmsSet('cms-item-description', p.description);
        cmsSet('cms-item-image', p.image);
        cmsSet('cms-item-cost', p.cost);
        cmsSet('cms-item-price', p.price);
        cmsSet('cms-item-qty', p.qty);
        ensureUnitOption('cms-item-unit', p.unit);
        $('#cms-item-unit').val(p.unit || 'Pcs');
        cmsSet('cms-item-code', p.code);
        $('#cms-item-tax').val(p.taxid);
        $('#cms-item-poscategory').val(p.categoryid);
        $('#cms-item-supplier').val(p.supplierid);
        $('#cms-item-type').val(p.type || 'general');
        $('#cms-item-preview').attr('src', p.image || '');
        // modifiers
        populateModifiers(p.modifiers || []);
    }
    if (w) {
        cmsSet('cms-item-webid', w.id);
        cmsSet('cms-item-badge', w.badge);
        cmsSet('cms-item-tag', w.tag);
        cmsSet('cms-item-order', w.order);
        $('#cms-item-category').val(w.category || '');
        $('#cms-item-visible').prop('checked', w.visible !== false);
    } else {
        cmsSet('cms-item-webid', webId || '');
        cmsSet('cms-item-order', String(CMS_ITEMS.length + 1));
    }
    dlg.dialog('open');
}

function onLinkedChange(val){
    if (!val) { $('#cms-item-id').val(''); return; }
    $('#cms-item-id').val(val);
    var p = POS_ITEMS[val];
    if (!p) return;
    cmsSet('cms-item-name', p.name);
    cmsSet('cms-item-altname', p.alt_name);
    cmsSet('cms-item-description', p.description);
    cmsSet('cms-item-image', p.image);
    cmsSet('cms-item-cost', p.cost);
    cmsSet('cms-item-price', p.price);
    cmsSet('cms-item-qty', p.qty);
    ensureUnitOption('cms-item-unit', p.unit);
    $('#cms-item-unit').val(p.unit || 'Pcs');
    cmsSet('cms-item-code', p.code);
    $('#cms-item-tax').val(p.taxid);
    $('#cms-item-poscategory').val(p.categoryid);
    $('#cms-item-supplier').val(p.supplierid);
    $('#cms-item-type').val(p.type || 'general');
    $('#cms-item-preview').attr('src', p.image || '');
    populateModifiers(p.modifiers || []);
}

function populateModifiers(mods){
    var modtable = $('#cms-modtable'); var seltable = $('#cms-selmodtable');
    modtable.html(''); seltable.html('');
    if (!mods) return;
    mods.forEach(function(mod){
        if (mod.type === 'select') {
            var modopttable = '';
            (mod.options || []).forEach(function(o){
                modopttable += '<tr><td><input onclick="handleSelectCheckbox(this);" type="checkbox" class="modoptdefault" ' + (o.default === true ? 'checked="checked"' : '') + '/></td><td><input style="width: 130px" type="text" class="modoptname" value="' + escAttr(o.name) + '"/></td><td><input type="text" style="width: 60px" class="modoptprice" value="' + escAttr(o.price) + '"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>';
            });
            seltable.append('<tr class="selmoditem"><td colspan="4" style="padding-right:0; padding-left:0;"><div style="padding-left:8px; padding-right:8px;"><label>Name:</label>&nbsp;<input style="width: 130px" type="text" class="modname" value="' + escAttr(mod.name) + '"/><button class="btn btn-danger btn-xs pull-right" style="margin-left:5px;" onclick="$(this).parents().eq(2).remove();">X</button></div><table class="table" style="margin-top:5px;">' + modtableheader + '<tbody class="modoptions">' + modopttable + '</tbody></table></td></tr>');
        } else {
            modtable.append('<tr><td><input type="text" style="width: 40px" class="modqty" value="' + escAttr(mod.qty) + '"/></td><td><input type="text" style="width: 40px" class="modminqty" value="' + escAttr(mod.minqty) + '"/></td><td><input type="text" style="width: 40px" class="modmaxqty" value="' + escAttr(mod.maxqty) + '"/></td><td><input style="width: 130px" type="text" class="modname" value="' + escAttr(mod.name) + '"/></td><td><input type="text" style="width: 60px" class="modprice" value="' + escAttr(mod.price) + '"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>');
        }
    });
}

function escAttr(s){ return String(s == null ? '' : s).replace(/"/g, '&quot;'); }

var modtableheader = '<thead class="table-header smaller"><tr><th><small>Default</small></th><th><small>Name</small></th><th><small>Price</small></th><th><button class="btn btn-primary btn-xs pull-right" onclick="addSelectModItem($(this).parents().eq(3).find(\'.modoptions\'));">Add Option</button></th></tr></thead>';
var modselectoption = '<tr><td><input onclick="handleSelectCheckbox($(this));" type="checkbox" class="modoptdefault"/></td><td><input style="width: 130px" type="text" class="modoptname" value=""/></td><td><input type="text" style="width: 60px" class="modoptprice" value="0.00"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parents().eq(1).remove();">X</button></td></tr>';

function addItemModifier(){
    $('#cms-modtable').append('<tr><td><input onchange="var row = $(this).parent().parent(); if ($(this).val()>row.find(\'.modminqty\').val()) row.find(\'.modminqty\').val($(this).val())" type="text" style="width: 40px" class="modqty" value="0"/></td><td><input type="text" style="width: 40px" class="modminqty" value="0"/></td><td><input type="text" style="width: 40px" class="modmaxqty" value="0"/></td><td><input style="width: 130px" type="text" class="modname" value=""/></td><td><input type="text" style="width: 60px" class="modprice" value="0.00"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>');
}
function addSelectItemModifier(){
    var seltable = $('#cms-selmodtable');
    var modelem = $('<tr class="selmoditem"><td colspan="4" style="padding-right:0; padding-left:0;"><div style="padding-left:8px; padding-right:8px;"><label>Name:</label>&nbsp;<input style="width: 130px" type="text" class="modname" value=""/><button class="btn btn-danger btn-xs pull-right" style="margin-left:5px;" onclick="$(this).parents().eq(2).remove();">X</button></div><table class="table" style="margin-top:5px;">' + modtableheader + '<tbody class="modoptions">' + modselectoption + '</tbody></table></td></tr>');
    modelem.find('.modoptdefault').prop('checked', true);
    seltable.append(modelem);
}
function addSelectModItem(elem){
    $(elem).append(modselectoption);
    if (elem.find('tr').length == 1) $(elem).find('.modoptdefault').prop('checked', true);
}
function handleSelectCheckbox(elem){
    var table = $(elem).parent().parent().parent();
    table.find('.modoptdefault').prop('checked', false);
    $(elem).prop('checked', true);
}
function collectItemModifiers(){
    var mods = [];
    $('#cms-selmodtable .selmoditem').each(function(){
        var mod = { type: "select", options: [] };
        mod.name = $(this).find(".modname").val();
        $(this).find('.modoptions tr').each(function(){
            mod.options.push({
                default: $(this).find(".modoptdefault").is(':checked'),
                name: $(this).find(".modoptname").val(),
                price: $(this).find(".modoptprice").val()
            });
        });
        mods.push(mod);
    });
    $('#cms-modtable tr').each(function(){
        mods.push({
            type: "simple",
            qty: $(this).find(".modqty").val(),
            minqty: $(this).find(".modminqty").val(),
            maxqty: $(this).find(".modmaxqty").val(),
            name: $(this).find(".modname").val(),
            price: $(this).find(".modprice").val()
        });
    });
    return mods;
}

function saveProductDialog(){
    var name = cmsGet('cms-item-name');
    if (!name) { alert('Name required'); return; }
    var posId = $('#cms-item-id').val();
    var webId = $('#cms-item-webid').val();
    if (!webId) webId = 'item-' + Date.now();
    var price = parseFloat(String(cmsGet('cms-item-price')).replace(/[^0-9.]/g, '')) || 0;
    var cost = parseFloat(String(cmsGet('cms-item-cost')).replace(/[^0-9.]/g, '')) || 0;
    var qty = parseFloat(String(cmsGet('cms-item-qty')).replace(/[^0-9.]/g, '')) || 1;

    var item;
    if (posId && POS_ITEMS[posId]) {
        item = JSON.parse(JSON.stringify(POS_ITEMS[posId]));
    } else {
        item = { code: '', qty: 1, name: '', alt_name: '', description: '', taxid: 1, cost: 0, price: 0, supplierid: 0, categoryid: 0, type: 'general', modifiers: [] };
    }
    item.name = name;
    item.alt_name = cmsGet('cms-item-altname');
    item.description = cmsGet('cms-item-description');
    item.image = cmsGet('cms-item-image');
    item.price = price;
    item.cost = cost;
    item.taxid = parseInt($('#cms-item-tax').val(), 10) || 1;
    item.qty = qty;
    item.unit = $('#cms-item-unit').val() || 'Pcs';
    var code = String(cmsGet('cms-item-code') || '').trim().toUpperCase();
    if (!code) code = 'CB' + Math.random().toString(36).substr(2,8).toUpperCase();
    item.code = code;
    item.supplierid = parseInt($('#cms-item-supplier').val(), 10) || 0;
    item.categoryid = parseInt($('#cms-item-poscategory').val(), 10) || 0;
    item.type = $('#cms-item-type').val() || 'general';
    item.website_id = webId;
    item.modifiers = collectItemModifiers();

    // new product with no POS category chosen: match/create a POS category from the website category label
    if (!posId && item.categoryid === 0) {
        var wc = $('#cms-item-category').val();
        if (wc) {
            var label = categoryLabel(wc);
            if (label && label !== wc) {
                var matchKey = Object.keys(POS_CATS).filter(function(k){ return String(POS_CATS[k].name).toLowerCase() === String(label).toLowerCase(); })[0];
                if (matchKey) item.categoryid = POS_CATS[matchKey].id;
                else {
                    try {
                        var r = WPOS.sendJsonData('categories/add', JSON.stringify({ name: label }));
                        if (r && r.id) { POS_CATS[r.id] = r; item.categoryid = r.id; }
                    } catch(e) {}
                }
            }
        }
    }

    WPOS.util.showLoader();
    var result;
    if (posId) result = WPOS.sendJsonData('items/edit', JSON.stringify(item));
    else result = WPOS.sendJsonData('items/add', JSON.stringify(item));
    if (result === false || result == null) { WPOS.util.hideLoader(); return; }

    var finalId = posId ? parseInt(posId, 10) : parseInt(result.id, 10);
    POS_ITEMS[finalId] = result;

    // website mirror
    var w = CMS_WEB_BY_POS[finalId];
    if (!w) w = (CMS_ITEMS || []).filter(function(x){ return x.id === webId; })[0];
    if (!w) { w = {}; CMS_ITEMS.push(w); }
    w.id = webId;
    w.name = result.name || name;
    w.price = parseFloat(result.price) || price;
    w.description = result.description || item.description || '';
    w.image = item.image || '';
    w.linked_itemid = finalId;
    w.category = $('#cms-item-category').val();
    w.badge = cmsGet('cms-item-badge');
    w.tag = cmsGet('cms-item-tag');
    w.order = parseInt(cmsGet('cms-item-order') || '0', 10) || 0;
    w.visible = $('#cms-item-visible').is(':checked');

    CMS.menu = { items: CMS_ITEMS };
    _postCms(function(res){
        WPOS.util.hideLoader();
        if (!res || !res.ok) { cmsStatus('Website save failed: ' + (res && res.error)); alert('POS product saved, but the website content could not be saved: ' + ((res && res.error) || 'unknown error')); return; }
        cmsStatus('Saved ' + new Date().toLocaleTimeString() + ' — live on till & website');
        $('#cmsproductdialog').dialog('close');
        fillProducts();
    });
}

function removeProduct(posId, webId){
    if (!WPOS.isAdmin()) { alert('Only administrators can delete entries.'); return; }
    var alsoPos = (posId != null && posId !== '');
    if (alsoPos) {
        if (!confirm('Delete POS product #' + posId + ' and its website menu item? This removes it from the till, kitchen and website.')) return;
    } else {
        if (!confirm('Delete this website menu item?')) return;
    }
    if (alsoPos) {
        try { WPOS.sendJsonData('items/delete', JSON.stringify({ id: posId })); } catch(e) { alert('POS product delete failed — remove it from Inventory manually.'); }
        delete POS_ITEMS[posId];
    }
    CMS_ITEMS = CMS_ITEMS.filter(function(x){ return x.id !== webId && x.linked_itemid !== posId; });
    CMS.menu = { items: CMS_ITEMS };
    WPOS.util.showLoader();
    _postCms(function(res){
        WPOS.util.hideLoader();
        if (!res || !res.ok) { cmsStatus('Website save failed: ' + (res && res.error)); alert('Menu item removed; website save failed: ' + ((res && res.error) || 'unknown')); return; }
        cmsStatus('Deleted ' + new Date().toLocaleTimeString());
        fillProducts();
    });
}

function deleteSelectedProducts(){
    if (!WPOS.isAdmin()) { alert('Only administrators can delete entries.'); return; }
    var ids = $('#cms-products-body .cms-prod-cb:checked').map(function(){ return $(this).data('posid'); }).get();
    if (!ids.length) { alert('Select at least one product'); return; }
    if (!confirm('Delete ' + ids.length + ' selected product(s) from the till and website?')) return;
    WPOS.util.showLoader();
    try { WPOS.sendJsonData('items/delete', JSON.stringify({ id: ids.join(',') })); } catch(e) { alert('POS delete failed'); WPOS.util.hideLoader(); return; }
    ids.forEach(function(id){ delete POS_ITEMS[id]; });
    var idset = {}; ids.forEach(function(id){ idset[id] = true; });
    CMS_ITEMS = CMS_ITEMS.filter(function(x){ return !idset[x.linked_itemid]; });
    CMS.menu = { items: CMS_ITEMS };
    _postCms(function(res){ WPOS.util.hideLoader(); if (!res || !res.ok) { alert('Website save failed: ' + (res && res.error)); return; } cmsStatus('Deleted ' + ids.length + ' product(s)'); fillProducts(); });
}

function exportProducts(){
    var data = POS_ITEMS;
    var selected = $('#cms-products-body .cms-prod-cb:checked').map(function(){ return $(this).data('posid'); }).get();
    if (selected.length) {
        var sel = {};
        selected.forEach(function(id){ if (POS_ITEMS[id]) sel[id] = POS_ITEMS[id]; });
        data = sel;
    }
    var categories = POS_CATS, suppliers = POS_SUPS;
    var taxrules = (function(){ try { return WPOS.getTaxTable().rules; } catch(e){ return {}; } })();
    var csv = WPOS.data2CSV(
        ['ID', 'Stock Code', 'Name', 'Description', 'Default Qty', 'Unit Cost', 'Unit Price', 'Tax Rule Name', 'Category Name', 'Supplier Name'],
        ['id', 'code', 'name', 'description', 'qty', 'cost', 'price',
            {key:'taxid', func: function(value){ return taxrules.hasOwnProperty(value) ? taxrules[value].name : 'Unknown'; }},
            {key:'categoryid', func: function(value){ return categories.hasOwnProperty(value) ? categories[value].name : 'Unknown'; }},
            {key:'supplierid', func: function(value){ return suppliers.hasOwnProperty(value) ? suppliers[value].name : 'Unknown'; }}
        ],
        data
    );
    WPOS.initSave('products', csv);
}

// ---------- Categories (website) ----------
function fillCategories(){
    var html = '';
    CMS.categories.forEach(function(c, i){
        html += '<div style="margin:4px 0;"><input type="text" value="' + escAttr(c.label || '') + '" data-ci="' + i + '" style="width:200px;" /> <input type="text" value="' + escAttr(c.id || '') + '" data-ciid="' + i + '" style="width:150px;" placeholder="id" />' + (WPOS.isAdmin() ? ' <button class="btn btn-xs btn-danger" onclick="CMS.categories.splice(' + i + ',1); fillCategories();">Remove</button>' : '') + '</div>';
    });
    $('#cms-cats-list').html(html || '<p class="text-muted">No categories.</p>');
}
function addCategory(){
    CMS.categories.push({ id: 'cat-' + Date.now(), label: 'New category' });
    fillCategories();
}

// ---------- Offers ----------
function fillOffers(){
    var html = '';
    CMS.offers.forEach(function(o, i){
        html += '<div style="margin:4px 0; border:1px solid #eee; padding:8px; border-radius:6px;">'
            + '<input type="text" value="' + escAttr(o.title || '') + '" data-oi="' + i + '" data-of="title" style="width:110px;" placeholder="Title" /> '
            + '<input type="text" value="' + escAttr(o.description || '') + '" data-oi="' + i + '" data-of="description" style="width:300px;" placeholder="Description" /> '
            + '<input type="text" value="' + escAttr(o.price || '') + '" data-oi="' + i + '" data-of="price" style="width:60px;" placeholder="KSh" /> '
            + '<input type="text" value="' + escAttr(o.badge || '') + '" data-oi="' + i + '" data-of="badge" style="width:100px;" placeholder="Badge" /> '
            + (WPOS.isAdmin() ? '<button class="btn btn-xs btn-danger" onclick="CMS.offers.splice(' + i + ',1); fillOffers();">Remove</button>' : '') + '</div>';
    });
    $('#cms-offers-list').html(html || '<p class="text-muted">No offers.</p>');
    $('#cms-offers-list input').on('change', function(){
        var i = $(this).data('oi'), f = $(this).data('of');
        if (i != null && f) CMS.offers[i][f] = f === 'price' ? (parseFloat($(this).val()) || 0) : $(this).val();
    });
}
function addOffer(){
    CMS.offers.push({ title: 'New Offer', description: '', price: 0, badge: '' });
    fillOffers();
}

// ---------- POS categories (till tray) ----------
var POS_CAT_TOP = {};
function fillPosCategories(){
    var rows = '';
    var ids = Object.keys(POS_CATS).sort(function(a,b){ return String(POS_CATS[a].name).localeCompare(String(POS_CATS[b].name)); });
    ids.forEach(function(id){
        var c = POS_CATS[id];
        var img = c.image || '';
        if (!img && POS_CAT_TOP[id]) img = POS_CAT_TOP[id].top_seller_image || '';
        var kind = c.image ? 'set' : (img ? 'top seller' : '');
        rows += '<tr>'
            + '<td>' + id + '</td>'
            + '<td><strong>' + esc(c.name || '-') + '</strong></td>'
            + '<td>' + (c.numitems != null ? c.numitems : '-') + '</td>'
            + '<td>' + (img ? '<img src="' + img + '" style="max-height:36px; max-width:64px; border-radius:4px;"/> <small style="display:block; color:#888;">' + kind + '</small>' : '<span style="color:#999;">—</span>') + '</td>'
            + '<td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="openPosCategoryDialog(' + id + ')">Edit</button>' + (WPOS.isAdmin() ? ' <button class="btn btn-xs btn-danger" onclick="removePosCategory(' + id + ')">Delete</button>' : '') + '</td>'
            + '</tr>';
    });
    $('#cms-poscats-body').html(rows || '<tr><td colspan="5" class="center">No POS categories yet.</td></tr>');
}
function openPosCategoryDialog(id){
    var dlg = $('#cmsposcatdialog');
    if (!dlg.hasClass('ui-dialog-content')) dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '460px', modal: true, autoOpen: false, title: 'POS category' });
    $('#cms-poscat-id').val(id || '');
    if (id && POS_CATS[id]) {
        cmsSet('cms-poscat-name', POS_CATS[id].name);
        cmsSet('cms-poscat-image', POS_CATS[id].image);
        $('#cms-poscat-preview').attr('src', POS_CATS[id].image || '');
    } else {
        cmsSet('cms-poscat-name', '');
        cmsSet('cms-poscat-image', '');
        $('#cms-poscat-preview').attr('src', '');
    }
    dlg.dialog('open');
}
function uploadPosCategoryImage(){
    var fi = document.getElementById('cms-poscat-file');
    if (!fi.files.length) { alert('Choose an image first'); return; }
    var fd = new FormData();
    fd.append('file', fi.files[0]);
    WPOS.util.showLoader();
    $.ajax({ url: '/api/website_upload.php', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
        success: function(res){ WPOS.util.hideLoader(); if (res && res.ok) { $('#cms-poscat-image').val(res.url); $('#cms-poscat-preview').attr('src', res.url); } else alert(res && res.error ? res.error : 'Upload failed'); },
        error: function(){ WPOS.util.hideLoader(); alert('Upload failed'); }
    });
}
function useTopSellerPosImage(){
    var id = $('#cms-poscat-id').val();
    var top = POS_CAT_TOP[id] || {};
    if (!top.top_seller_image) { alert('No top-selling product image for this category yet'); return; }
    $('#cms-poscat-image').val(top.top_seller_image);
    $('#cms-poscat-preview').attr('src', top.top_seller_image);
}
function savePosCategory(){
    var name = cmsGet('cms-poscat-name');
    if (!name) { alert('Name required'); return; }
    var id = $('#cms-poscat-id').val();
    WPOS.util.showLoader();
    var item = { name: name };
    var result;
    if (id) {
        item.id = id;
        result = WPOS.sendJsonData('categories/edit', JSON.stringify(item));
        var img = cmsGet('cms-poscat-image') || '';
        if (result !== false) {
            try { WPOS.sendJsonData('categories/setImage', JSON.stringify({ id: id, image: img })); } catch(e) {}
            if (POS_CATS[id]) { POS_CATS[id].name = name; POS_CATS[id].image = img; }
        }
    } else {
        result = WPOS.sendJsonData('categories/add', JSON.stringify(item));
        if (result !== false && result && result.id) {
            var newId = result.id;
            var img = cmsGet('cms-poscat-image') || '';
            POS_CATS[newId] = result;
            if (img) { try { WPOS.sendJsonData('categories/setImage', JSON.stringify({ id: newId, image: img })); } catch(e) {} POS_CATS[newId].image = img; }
        }
    }
    WPOS.util.hideLoader();
    if (result === false) return;
    fillPosCategories();
    $('#cmsposcatdialog').dialog('close');
}
function removePosCategory(id){
    if (!WPOS.isAdmin()) { alert('Only administrators can delete entries.'); return; }
    if (!confirm('Delete POS category #' + id + '? Items in it will become uncategorised.')) return;
    WPOS.util.showLoader();
    if (WPOS.sendJsonData('categories/delete', JSON.stringify({ id: id }))) {
        delete POS_CATS[id];
        fillPosCategories();
    }
    WPOS.util.hideLoader();
}

function categoryLabel(id){
    var c = (CMS.categories || []).filter(function(x){ return x.id === id; })[0];
    return c ? c.label : id;
}

// ---------- Save ----------
function saveCms(retry){
    CMS.site = collectGeneral();
    CMS.menu = { items: CMS_ITEMS };
    CMS.categories = Array.from(document.querySelectorAll('#cms-cats-list input[data-ci]')).map(function(el){
        var i = parseInt(el.getAttribute('data-ci'), 10);
        var idEl = document.querySelector('input[data-ciid="' + i + '"]');
        return { id: idEl && idEl.value ? idEl.value : ('cat-' + i), label: el.value };
    });
    WPOS.util.showLoader();
    _postCms(function(res){
        WPOS.util.hideLoader();
        if (!res || !res.ok) { cmsStatus('Error: ' + (res && res.error)); return; }
        cmsStatus('Saved ' + new Date().toLocaleTimeString());
        alert('Published. The site is live with your changes.');
    }, retry);
}
function cmsStatus(msg){ $('#cms-status').text(msg); }

// ---------- Upload ----------
function compressImageFile(file, cb){
    var MAX_EDGE = 1600, TARGET_BYTES = 400 * 1024;
    var isRaster = /image\/(jpeg|jpg|png|webp)/i.test(file.type);
    if (!isRaster) { cb(file); return; }
    if (file.size <= TARGET_BYTES && !(file.type === 'image/png' && file.size > 600 * 1024)) { cb(file); return; }
    var img = new Image();
    var url = URL.createObjectURL(file);
    img.onload = function(){
        try {
            var w = img.naturalWidth, h = img.naturalHeight;
            if (w <= 0 || h <= 0) { cb(file); return; }
            var scale = Math.min(1, MAX_EDGE / Math.max(w, h));
            if (scale === 1 && file.size <= TARGET_BYTES) { cb(file); URL.revokeObjectURL(url); return; }
            var nw = Math.max(1, Math.round(w * scale)), nh = Math.max(1, Math.round(h * scale));
            var c = document.createElement('canvas');
            c.width = nw; c.height = nh;
            var ctx = c.getContext('2d');
            ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, nw, nh);
            ctx.drawImage(img, 0, 0, nw, nh);
            var supportsWebP = false;
            try { supportsWebP = (c.toDataURL('image/webp', 0.85).indexOf('data:image/webp') === 0); } catch(e) {}
            var mime = supportsWebP ? 'image/webp' : 'image/jpeg';
            var ext = supportsWebP ? 'webp' : 'jpg';
            c.toBlob(function(blob){
                URL.revokeObjectURL(url);
                if (!blob || blob.size >= file.size) { cb(file); return; }
                var out = new File([blob], file.name.replace(/\.[^.]+$/, '') + '-web.' + ext, { type: mime });
                cb(out);
            }, mime, 0.82);
        } catch(e) { URL.revokeObjectURL(url); cb(file); }
    };
    img.onerror = function(){ URL.revokeObjectURL(url); cb(file); };
    img.src = url;
}
function uploadItemImage(retry){
    var fileInput = document.getElementById('cms-item-file');
    if (!fileInput.files.length) { alert('Choose an image first'); return; }
    var picked = fileInput.files[0];
    var pickedName = (picked.name || '').toLowerCase();
    if (/\.(heic|heif)$/.test(pickedName) || /image\/(heic|heif)/.test(picked.type || '')) {
        alert('That photo is in HEIC format (iPhone "High Efficiency"), which browsers and the server cannot use.\n\nFix (30 seconds):\n• iPhone: Settings > Camera > Formats > "Most Compatible"\n• Or Share > Save as JPEG and upload that copy.\n\nJPG / PNG / WebP photos upload fine.');
        return;
    }
    if (picked.size > 20 * 1024 * 1024) {
        alert('That photo is ' + Math.round(picked.size / 1048576) + 'MB. Please choose one under 20MB.');
        return;
    }
    var wrap = document.getElementById('cms-upload-progress-wrap');
    var bar = document.getElementById('cms-upload-bar');
    var status = document.getElementById('cms-upload-status');
    if (wrap) wrap.style.display = 'block';
    if (bar) bar.style.width = '0%';
    if (status) status.textContent = 'Compressing image...';
    WPOS.util.showLoader();
    compressImageFile(picked, function(file){
        if (status) status.textContent = 'Uploading ' + Math.round(file.size / 1024) + 'KB...';
        var fd = new FormData();
        fd.append('file', file);
        try {
            var saved = JSON.parse(localStorage.getItem('cb_auth') || 'null');
            if (saved && saved.username && saved.auth_hash) {
                fd.append('username', saved.username);
                fd.append('auth_hash', saved.auth_hash);
            }
        } catch(e) {}
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/website_upload.php', true);
        xhr.responseType = 'json';
        xhr.upload.onprogress = function(e){
            if (e.lengthComputable && bar) {
                var pct = Math.round((e.loaded / e.total) * 100);
                bar.style.width = pct + '%';
                if (status) status.textContent = 'Uploading... ' + pct + '%';
            }
        };
        xhr.onload = function(){
            WPOS.util.hideLoader();
            var res = xhr.response;
            if (!res) { try { res = JSON.parse(xhr.responseText || '{}'); } catch(e) { res = {}; } }
            if (xhr.status >= 200 && xhr.status < 300 && res && res.ok) {
                if (bar) bar.style.width = '100%';
                if (status) status.textContent = 'Upload complete ✓  (' + Math.round((res.size || file.size) / 1024) + 'KB)';
                $('#cms-item-image').val(res.url);
                $('#cms-item-preview').attr('src', res.url);
                var linkedId = parseInt($('#cms-item-id').val(), 10);
                if (linkedId > 0) {
                    try { WPOS.sendJsonData('items/setDetails', JSON.stringify({ itemid: linkedId, image: res.url })); } catch(e) {}
                    if (status) status.textContent = 'Upload complete ✓  Synced to POS — website & till tray will refresh shortly.';
                }
                setTimeout(function(){ if (wrap) wrap.style.display = 'none'; }, 3500);
            } else {
                var msg = (res && res.error) ? res.error : ('Upload failed (HTTP ' + xhr.status + ')');
                if (status) status.textContent = 'Failed: ' + msg;
                if (bar) bar.style.background = '#d9534f';
                if (!retry && (xhr.status === 403 || /Unauthorized/i.test(msg))) {
                    try {
                        var saved = JSON.parse(localStorage.getItem('cb_auth') || 'null');
                        if (saved && saved.username && saved.auth_hash) {
                            var r = WPOS.sendJsonData('authrenew', JSON.stringify({ username: saved.username, auth_hash: saved.auth_hash }));
                            if (r && r.id) {
                                $.ajaxSetup({ beforeSend: function(x){ x.setRequestHeader('anti-csrf-token', r.csrf_token); } });
                                uploadItemImage(true);
                                return;
                            }
                        }
                    } catch(e) {}
                }
                alert('Upload failed: ' + msg + (xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
            }
        };
        xhr.onerror = function(){
            WPOS.util.hideLoader();
            if (status) status.textContent = 'Network error — please try again.';
            if (bar) bar.style.background = '#d9534f';
            alert('Upload failed: network error.');
        };
        xhr.send(fd);
    });
}

// ---------- Import (CSV) ----------
var importdialog = null;
function openImportDialog(){
    if (importdialog != null) importdialog.csvImport("destroy");
    importdialog = $("body").csvImport({
        jsonFields: {
            'code': {title:'Stock Code', required: true},
            'name': {title:'Name', required: true},
            'description': {title:'Description', required: false, value: ""},
            'qty': {title:'Default Qty', required: false, value: 1},
            'cost': {title:'Unit Cost', required: false, value: 0.00},
            'price': {title:'Unit Price', required: false, value: ""},
            'tax_name': {title:'Tax Rule Name', required: false, value: ""},
            'supplier_name': {title:'Supplier Name', required: false, value: ""},
            'category_name': {title:'Category Name', required: false, value: ""}
        },
        csvHasHeader: true,
        importOptions: [
            {label: "Set unknown tax names to no tax", id:"skip_tax", checked:false},
            {label: "Create unknown suppliers", id:"add_suppliers", checked:true},
            {label: "Create unknown categories", id:"add_categories", checked:true}
        ],
        onImport: function(jsondata, options){ importItems(jsondata, options); }
    });
}
function importItems(jsondata, options){
    showModalLoader("Importing Items");
    var total = jsondata.length;
    var percent_inc = total / 100;
    setModalLoaderStatus("Uploading data...");
    var data = {"options": options, "import_data": jsondata};
    var result = WPOS.sendJsonDataAsync('items/import/set', JSON.stringify(data), function(data){
        if (data !== false){
            WPOS.startEventSourceProcess(
                '/api/items/import/start',
                function(data){
                    if (data.hasOwnProperty('progress')) {
                        setModalLoaderSubStatus(data.progress + " of " + total);
                        var progress = Math.round(percent_inc * data.progress);
                        setModalLoaderProgress(progress);
                    }
                    if (data.hasOwnProperty('status')) setModalLoaderStatus(data.status);
                    if (data.hasOwnProperty('error')) {
                        if (data.error == "OK") {
                            showModalCloseButton('Item Import Complete!');
                        } else {
                            showModalCloseButton("Error Importing Items", data.error);
                        }
                        if (data.hasOwnProperty('data')){
                            for (var i in data.data) {
                                if (data.data.hasOwnProperty(i)) POS_ITEMS[i] = data.data[i];
                            }
                            fillProducts();
                        }
                    }
                },
                function(e){ showModalCloseButton("Event feed failed " + e.message); }
            );
        } else {
            showModalCloseButton("Item Import Failed!");
        }
    }, function(error){ showModalCloseButton("Item Import Failed!", error); });
    if (!result) showModalCloseButton("Item Import Failed!");
}

// ---------- Modal loader ----------
var eventuiinit = false;
function initModalLoader(title){
    $("#cms-modalloader").removeClass('hide').dialog({
        resizable: true, width: 400, modal: true, autoOpen: false, title: title, title_html: true,
        closeOnEscape: false, open: function(event, ui){ $(".ui-dialog-titlebar-close").hide(); }
    });
}
function showModalLoader(title){
    if (!eventuiinit){ initModalLoader(title); eventuiinit = true; }
    $("#cms-modalloader_status").text('Initializing...');
    $("#cms-modalloader_substatus").text('');
    $("#cms-modalloader_cbtn").hide();
    $("#cms-modalloader_img").show();
    $("#cms-modalloader_prog").show();
    $("#cms-modalloader").dialog('open');
}
function setModalLoaderProgress(progress){ $("#cms-modalloader_progbar").attr('width', progress + "%"); }
function showModalCloseButton(result, substatus){
    $("#cms-modalloader_status").text(result);
    setModalLoaderSubStatus(substatus ? substatus : '');
    $("#cms-modalloader_img").hide();
    $("#cms-modalloader_prog").hide();
    $("#cms-modalloader_cbtn").show();
}
function setModalLoaderStatus(status){ $("#cms-modalloader_status").text(status); }
function setModalLoaderSubStatus(status){ $("#cms-modalloader_substatus").text(status); }

// ---------- POST ----------
function _postCms(cb, retry){
    var _saveUrl = '/api/save_website_content.php';
    try {
        var _sv = JSON.parse(localStorage.getItem('cb_auth') || 'null');
        if (_sv && _sv.username && _sv.auth_hash)
            _saveUrl += '?username=' + encodeURIComponent(_sv.username) + '&auth_hash=' + encodeURIComponent(_sv.auth_hash);
    } catch(e) {}
    $.ajax({ url: _saveUrl, type: 'POST', data: JSON.stringify(CMS), contentType: 'application/json', dataType: 'json',
        success: function(res){ cb(res); },
        error: function(xhr){
            if (!retry && xhr.status === 403) {
                try {
                    var saved = JSON.parse(localStorage.getItem('cb_auth') || 'null');
                    if (saved && saved.username && saved.auth_hash) {
                        var r = WPOS.sendJsonData('authrenew', JSON.stringify({ username: saved.username, auth_hash: saved.auth_hash }));
                        if (r && r.id) {
                            $.ajaxSetup({ beforeSend: function(x){ x.setRequestHeader('anti-csrf-token', r.csrf_token); } });
                            _postCms(cb, true);
                            return;
                        }
                    }
                } catch(e) {}
            }
            cb(null, xhr);
        }
    });
}

function openLiveSite(){
    var url = (CMS.site && CMS.site.website_url) ? CMS.site.website_url.trim() : '';
    if (!url) {
        var host = location.hostname;
        var isLocal = /localhost|127\.0\.0\.1/.test(host);
        url = isLocal ? '/website/' : '/';
    }
    window.open(url, '_blank');
}

// ---------- boot ----------
loadCms();

// deep-link tab from hash (e.g. #!websitecms&tab=menu)
(function(){
    var m = location.hash.match(/tab=([a-zA-Z]+)/);
    var key = m ? m[1].toLowerCase() : 'general';
    var map = { menu: '#cms-tab-menu', cats: '#cms-tab-cats', offers: '#cms-tab-offers', general: '#cms-tab-general' };
    var target = map[key] || '#cms-tab-general';
    setTimeout(function(){ $('.cb-tabs a[href="' + target + '"]').tab('show'); }, 80);
})();
</script>

<style type="text/css">
#cms-products-table_processing { display: none; }
</style>