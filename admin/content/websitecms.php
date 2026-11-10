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
    <li><a href="#cms-tab-menu" data-toggle="tab">Menu Items</a></li>
    <li><a href="#cms-tab-cats" data-toggle="tab">Categories</a></li>
    <li><a href="#cms-tab-offers" data-toggle="tab">Offers</a></li>
</ul>
<style>#cms-tab-general label,#cms-itemdialog label{display:block; font-size:12px;} #cms-tab-general input.form-control,#cms-itemdialog input.form-control{margin-bottom:8px;}</style>

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
            <button class="btn btn-primary btn-sm" onclick="openItemDialog(null);"><i class="icon-plus"></i> Add menu item</button>
            <button class="btn btn-default btn-sm" onclick="syncFromPos();" style="margin-left:4px;"><i class="icon-refresh"></i> Sync from POS</button>
            <button class="btn btn-default btn-sm" onclick="syncToPosNow();" style="margin-left:4px;"><i class="icon-upload-alt"></i> Sync to POS</button>
            <span style="font-size:12px; color:#666;">One source of truth: POS holds name/price/image/description; this CMS holds display extras (badge, tag, order, visible).</span>
        </div>
        <div class="cb-scroll" style="max-height:420px;"><table id="cms-items-table" class="table table-striped table-bordered" style="font-size:12px; min-width:900px;">
            <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Category</th><th>Badge</th><th>Order</th><th>Visible</th><th>Actions</th></tr></thead>
            <tbody id="cms-items-body"></tbody>
        </table></div>
    </div>

    <div class="cb-pane" id="cms-tab-cats">
        <p class="text-muted">These drive the menu category pills on the website.</p>
        <div id="cms-cats-list"></div>
        <button class="btn btn-primary btn-sm" onclick="addCategory();"><i class="icon-plus"></i> Add category</button>
    </div>

    <div class="cb-pane" id="cms-tab-offers">
        <div id="cms-offers-list"></div>
        <button class="btn btn-primary btn-sm" onclick="addOffer();"><i class="icon-plus"></i> Add offer</button>
    </div>


<div id="cmsitemdialog" class="hide" style="padding:14px; min-width:420px;">
    <input type="hidden" id="cms-item-id" />
    <label>Name <input type="text" id="cms-item-name" class="form-control" style="width:100%; display:block;" /></label>
    <label>Price (KSh) <input type="text" id="cms-item-price" class="form-control" style="width:100%; display:block;" /> Category <select id="cms-item-category" class="form-control" style="width:100%; display:block;"></select></label>
    <label>Badge <input type="text" id="cms-item-badge" class="form-control" style="width:100%; display:block;" /> Tag <input type="text" id="cms-item-tag" class="form-control" style="width:100%; display:block;" /></label>
    <label>Description <textarea id="cms-item-description" class="form-control" style="width:100%; height:55px;"></textarea></label>
    <label>Image URL <input type="text" id="cms-item-image" class="form-control" style="width:100%; display:block;" onchange="$('#cms-item-preview').attr('src', this.value);"/></label>
    <div style="margin:4px 0;">
        <img id="cms-item-preview" style="max-height:80px; max-width:120px; border:1px solid #ddd; border-radius:6px;" alt=""/>
        <input type="file" id="cms-item-file" accept="image/*" style="display:inline-block;" />
        <button class="btn btn-xs btn-info" onclick="uploadItemImage();"><i class="icon-cloud-upload"></i> Upload</button>
    </div>
    <label>Linked POS product (price auto-sync) <select id="cms-item-linked" class="form-control" style="width:100%; display:block;"></select></label>
    <label>Sort order <input type="text" id="cms-item-order" class="form-control" style="width:30%; display:inline-block;" /> <label style="display:inline-block;"><input type="checkbox" id="cms-item-visible" /> Visible on site</label></label>
    <div style="margin-top:10px; text-align:center;">
        <button class="btn btn-success btn-sm" onclick="saveItemDialog();">Save item</button>
        <button class="btn btn-default btn-sm" onclick="$('#cmsitemdialog').dialog('close')">Cancel</button>
    </div>
</div>

<script type="text/javascript">
    var CMS = null, CMS_ITEMS = [];
    function escId(s){ return String(s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'"); }
    function cmsGet(id){ var el = document.getElementById(id); return el ? el.value : ''; }
    function cmsSet(id, v){ var el = document.getElementById(id); if (el) el.value = v || ''; }
    function loadCms(){
        WPOS.util.showLoader();
        $.get('/api/website_content.php', function(data){
            CMS = data || {};
            if (!CMS.site) CMS.site = {};
            if (!CMS.menu) CMS.menu = { items: [] };
            if (!CMS.categories) CMS.categories = [];
            if (!CMS.offers) CMS.offers = [];
            CMS_ITEMS = CMS.menu.items.map(function(it){ return Object.assign({}, it); });
            fillGeneral(); fillItems(); fillCategories(); fillOffers(); fillItemDialog(); fillCatSelects();
            WPOS.util.hideLoader();
        });
    }
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
        // Preserve every non-general field (logo, gallery, carousels, mpesa_till,
        // delivery_fee, announcement, seo, ...) so saving never wipes them.
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
        // Preserve photo carousels (hero/kitchen/venue) — not edited in this simple form,
        // but must never be dropped on save.
        if (CMS.site && CMS.site.carousels) s.carousels = CMS.site.carousels;
        return s;
    }
    function fillCatSelects(){
        var c = $('#cms-item-category');
        c.html('<option value="">— none —</option>');
        (CMS.categories || []).forEach(function(cat){ c.append('<option value="' + cat.id + '">' + cat.label + '</option>'); });
    }
    function fillItemDialog(){
        var sel = $('#cms-item-linked');
        sel.html('<option value="">— none —</option>');
        try {
            var items = WPOS.getJsonData('items/get') || {};
            for (var k in items) sel.append('<option value="' + items[k].id + '">' + (items[k].name || items[k].id) + '</option>');
        } catch(e) {}
    }
    function fillItems(){
        CMS_ITEMS.sort(function(a, b){ return (a.order || 0) - (b.order || 0); });
        var html = '';
        CMS_ITEMS.forEach(function(it){
            html += '<tr><td><img src="' + (it.image || '') + '" style="max-height:40px; max-width:60px; border-radius:4px;"/></td>'
                + '<td><strong>' + (it.name || '-') + '</strong><br/><small>' + (it.description || '').slice(0,60) + '</small></td>'
                + '<td>' + WPOS.util.money(it.price || 0) + '</td><td>' + (it.category || '-') + '</td><td>' + (it.badge || '—') + '</td><td>' + (it.order || 0) + '</td>'
                + '<td><input type="checkbox" ' + (it.visible ? 'checked' : '') + ' onchange="toggleItemVisible(\'' + escId(it.id) + '\', this.checked);"/></td>'
                + '<td style="white-space:nowrap;"><button class="btn btn-xs btn-primary" onclick="openItemDialog(\'' + escId(it.id) + '\')">Edit</button> <button class="btn btn-xs btn-danger" onclick="removeItem(\'' + escId(it.id) + '\')">Delete</button></td></tr>';
        });
        $('#cms-items-body').html(html || '<tr><td colspan="8" class="center">No items yet.</td></tr>');
    }
    function openItemDialog(id){
        var dlg = $('#cmsitemdialog');
        if (!dlg.hasClass('ui-dialog-content')) dlg.removeClass('hide').dialog({ width: 'auto', maxWidth: '520px', modal: true, autoOpen: false, title: 'Menu item' });
        fillItemDialog(); fillCatSelects();
        if (!id) {
            $('#cms-item-id').val('');
            ['name','price','badge','tag','description','image'].forEach(function(k){ cmsSet('cms-item-' + k, ''); });
            $('#cms-item-category').val(''); $('#cms-item-linked').val('');
            cmsSet('cms-item-order', String(CMS_ITEMS.length + 1));
            $('#cms-item-visible').prop('checked', true);
            $('#cms-item-preview').attr('src', '');
        } else {
            var it = CMS_ITEMS.filter(function(x){ return x.id === id; })[0];
            if (!it) return;
            $('#cms-item-id').val(it.id);
            cmsSet('cms-item-name', it.name); cmsSet('cms-item-price', it.price); cmsSet('cms-item-badge', it.badge); cmsSet('cms-item-tag', it.tag);
            cmsSet('cms-item-description', it.description); cmsSet('cms-item-image', it.image);
            $('#cms-item-category').val(it.category || ''); $('#cms-item-linked').val(it.linked_itemid || '');
            cmsSet('cms-item-order', it.order);
            $('#cms-item-visible').prop('checked', !!it.visible);
            $('#cms-item-preview').attr('src', it.image || '');
        }
        dlg.dialog('open');
    }
    function saveItemDialog(){
        var id = $('#cms-item-id').val() || ('item-' + Date.now());
        var it = {
            id: id,
            name: cmsGet('cms-item-name'), price: parseFloat(cmsGet('cms-item-price')) || 0,
            category: $('#cms-item-category').val(), badge: cmsGet('cms-item-badge'), tag: cmsGet('cms-item-tag'),
            description: cmsGet('cms-item-description'), image: cmsGet('cms-item-image'),
            linked_itemid: parseInt($('#cms-item-linked').val() || '0', 10) || 0,
            order: parseInt(cmsGet('cms-item-order') || '0', 10) || 0,
            visible: $('#cms-item-visible').is(':checked')
        };
        if (!it.name) { alert('Name required'); return; }
        var i = CMS_ITEMS.findIndex(function(x){ return x.id === id; });
        if (i >= 0) CMS_ITEMS[i] = it; else CMS_ITEMS.push(it);
        $('#cmsitemdialog').dialog('close');
        fillItems();
    }
    function toggleItemVisible(id, vis){
        var it = CMS_ITEMS.filter(function(x){ return x.id === id; })[0];
        if (it) it.visible = !!vis;
    }
    function removeItem(id){
        var it = CMS_ITEMS.filter(function(x){ return x.id === id; })[0];
        if (!it) return;
        var alsoPos = false;
        if (it.linked_itemid) {
            if (!confirm('Delete menu item "' + it.name + '" AND its POS product (ID ' + it.linked_itemid + ')? This removes it from the website and the till.')) return;
            alsoPos = true;
        } else if (!confirm('Delete this menu item?')) return;
        CMS_ITEMS = CMS_ITEMS.filter(function(x){ return x.id !== id; });
        fillItems();
        if (alsoPos) {
            try { WPOS.sendJsonData('items/delete', JSON.stringify({ id: it.linked_itemid })); alert('POS product deleted.'); } catch(e) { alert('Menu item removed; POS product delete failed — remove it from Inventory manually.'); }
        }
    }
    // Downscale big photos in the browser before upload: keeps pages fast on mobile
    // data and makes even huge phone photos sail through server size limits.
    function compressImageFile(file, cb){
        var BIG_BYTES = 400 * 1024, BIG_EDGE = 2000;
        var isRaster = /image\/(jpeg|jpg|png|webp)/.test(file.type);
        if (!isRaster || (file.size <= BIG_BYTES)) { cb(file); return; }
        var img = new Image();
        var url = URL.createObjectURL(file);
        img.onload = function(){
            try {
                var w = img.naturalWidth, h = img.naturalHeight;
                if (Math.max(w, h) <= BIG_EDGE && file.size <= 1.5 * 1024 * 1024) { cb(file); return; }
                var scale = Math.min(1, 1600 / Math.max(w, h));
                var nw = Math.max(1, Math.round(w * scale)), nh = Math.max(1, Math.round(h * scale));
                var c = document.createElement('canvas');
                c.width = nw; c.height = nh;
                var ctx = c.getContext('2d');
                ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, nw, nh); // flatten PNG alpha onto white for JPEG
                ctx.drawImage(img, 0, 0, nw, nh);
                var keepPng = /image\/png/.test(file.type) && file.size <= 3 * 1024 * 1024;
                c.toBlob(function(blob){
                    if (!blob || blob.size >= file.size) { cb(file); return; }
                    var ext = keepPng ? 'png' : 'jpg';
                    var out = new File([blob], file.name.replace(/\.[^.]+$/, '') + '-web.' + ext, { type: keepPng ? 'image/png' : 'image/jpeg' });
                    cb(out);
                }, keepPng ? 'image/png' : 'image/jpeg', 0.85);
            } catch(e) { cb(file); }
        };
        img.onerror = function(){ cb(file); };
        img.src = url;
    }
    function uploadItemImage(retry){
        var fileInput = document.getElementById('cms-item-file');
        if (!fileInput.files.length) { alert('Choose an image first'); return; }
        var picked = fileInput.files[0];
        // Clear, actionable checks BEFORE hitting the server (avoids opaque 400s).
        var pickedName = (picked.name || '').toLowerCase();
        if (/\.(heic|heif)$/.test(pickedName) || /image\/(heic|heif)/.test(picked.type || '')) {
            alert('That photo is in HEIC format (iPhone "High Efficiency"), which browsers and the server cannot use.\n\nFix (30 seconds):\n• iPhone: Settings > Camera > Formats > "Most Compatible" (new photos become JPG)\n• Or open the photo, tap Share > Save as JPEG, and upload that copy.\n\nJPG / PNG / WebP photos upload fine.');
            return;
        }
        if (picked.size > 20 * 1024 * 1024) {
            alert('That photo is ' + Math.round(picked.size / 1048576) + 'MB. Please choose one under 20MB (most phone photos are 2-5MB).');
            return;
        }
        WPOS.util.showLoader();
        compressImageFile(picked, function(file){
            var fd = new FormData();
            fd.append('file', file);
            // Persisted credentials (cb_auth) so uploads survive a stale PHP session.
            try {
                var saved = JSON.parse(localStorage.getItem('cb_auth') || 'null');
                if (saved && saved.username && saved.auth_hash) {
                    fd.append('username', saved.username);
                    fd.append('auth_hash', saved.auth_hash);
                }
            } catch(e) {}
            $.ajax({ url: '/api/website_upload.php', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
                success: function(res){
                    WPOS.util.hideLoader();
                    if (res && res.ok) {
                        $('#cms-item-image').val(res.url); $('#cms-item-preview').attr('src', res.url);
                        // Instant sync: push the new image to the linked POS product immediately
                        // (one source of truth = POS product image), so the till tray + admin reflect it.
                        var linkedId = parseInt($('#cms-item-linked').val(), 10);
                        if (linkedId > 0) {
                            try { WPOS.sendJsonData('items/setDetails', JSON.stringify({ itemid: linkedId, image: res.url })); } catch(e) {}
                        }
                    }
                    else alert('Upload failed: ' + (res && res.error ? res.error : 'unknown error'));
                },
                error: function(xhr){
                    WPOS.util.hideLoader();
                    var msg = 'Upload failed';
                    try { var j = JSON.parse(xhr.responseText || '{}'); if (j.error) msg = 'Upload failed: ' + j.error; } catch(e){}
                    // Session expired mid-upload? Try silent re-auth from persisted credentials, then retry once.
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
                    alert(msg + (xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
                }
            });
        });
    }
    function fillCategories(){
        var html = '';
        CMS.categories.forEach(function(c, i){
            html += '<div style="margin:4px 0;"><input type="text" value="' + (c.label || '') + '" data-ci="' + i + '" style="width:200px;" /> <input type="text" value="' + (c.id || '') + '" data-ciid="' + i + '" style="width:150px;" placeholder="id" /> <button class="btn btn-xs btn-danger" onclick="CMS.categories.splice(' + i + ',1); fillCategories();">Remove</button></div>';
        });
        $('#cms-cats-list').html(html || '<p class="text-muted">No categories.</p>');
    }
    function addCategory(){
        CMS.categories.push({ id: 'cat-' + Date.now(), label: 'New category' });
        fillCategories();
    }
    function fillOffers(){
        var html = '';
        CMS.offers.forEach(function(o, i){
            html += '<div style="margin:4px 0; border:1px solid #eee; padding:8px; border-radius:6px;">'
                + '<input type="text" value="' + (o.title || '') + '" data-oi="' + i + '" data-of="title" style="width:110px;" placeholder="Title" /> '
                + '<input type="text" value="' + (o.description || '') + '" data-oi="' + i + '" data-of="description" style="width:300px;" placeholder="Description" /> '
                + '<input type="text" value="' + (o.price || '') + '" data-oi="' + i + '" data-of="price" style="width:60px;" placeholder="KSh" /> '
                + '<input type="text" value="' + (o.badge || '') + '" data-oi="' + i + '" data-of="badge" style="width:100px;" placeholder="Badge" /> '
                + '<button class="btn btn-xs btn-danger" onclick="CMS.offers.splice(' + i + ',1); fillOffers();">Remove</button></div>';
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
    function syncPosProducts(done){
        var info = '';
        var unlinked = CMS_ITEMS.filter(function(it){ return !it.linked_itemid; });
        // 1) sync full details into POS for linked items (name, price, image, description, website link)
        CMS_ITEMS.forEach(function(it){
            if (it.linked_itemid) {
                try {
                    WPOS.sendJsonData('items/setDetails', JSON.stringify({
                        itemid: it.linked_itemid,
                        name: it.name,
                        price: it.price,
                        image: it.image || '',
                        description: it.description || '',
                        website_id: it.id || ''
                    }));
                } catch(e) {}
            }
        });
        // 2) unlinked items: offer to create POS products (so they can be sold on the till)
        if (!unlinked.length) { done(''); return; }
        if (!confirm(unlinked.length + ' menu item(s) have no POS product:\n' + unlinked.map(function(u){ return '· ' + u.name; }).join('\n') + '\n\nCreate POS products so they can be sold on the till?')) { done(''); return; }
        var created = 0;
        unlinked.forEach(function(it){
            try {
                var r = WPOS.sendJsonData('items/createFromMenu', JSON.stringify({ name: it.name, price: it.price, category_label: (categoryLabel(it.category) || it.category), image: it.image || '', description: it.description || '', website_id: it.id || '' }));
                if (r !== false && r !== null) { it.linked_itemid = parseInt(r, 10) || parseInt(r.id, 10) || 0; created++; }
            } catch(e) {}
        });
        if (created > 0) {
            CMS.menu = { items: CMS_ITEMS };
            _postCms(function(){}); // persist the new links silently
            info = ' · + ' + created + ' POS product(s) created.';
        }
        done(info);
    }
    function categoryLabel(id){
        var c = (CMS.categories || []).filter(function(x){ return x.id === id; })[0];
        return c ? c.label : id;
    }
    function syncFromPos(){
        WPOS.util.showLoader();
        try {
            var items = WPOS.getJsonData('items/get') || {};
            var byId = {};
            for (var k in items) byId[items[k].id] = items[k];
            var changed = 0;
            CMS_ITEMS.forEach(function(it){
                if (!it.linked_itemid || !byId[it.linked_itemid]) return;
                var p = byId[it.linked_itemid];
                var dd = (typeof p.data === 'string') ? (function(){ try { return JSON.parse(p.data); } catch(e){ return {}; } })() : (p.data || {});
                var before = JSON.stringify({n:it.name,pr:it.price,im:it.image,ds:it.description});
                if (p.name) it.name = p.name;
                if (p.price != null && !isNaN(parseFloat(p.price))) it.price = parseFloat(p.price);
                if (p.image || dd.image) it.image = p.image || dd.image;
                if (p.description || dd.description) it.description = p.description || dd.description;
                if (JSON.stringify({n:it.name,pr:it.price,im:it.image,ds:it.description}) !== before) changed++;
            });
            fillItems();
            // push CMS display extras back to POS is handled on Save & Publish
            WPOS.util.hideLoader();
            $('#cms-status').text('Synced ' + changed + ' item(s) from POS. Click Save & Publish to push display extras to POS.');
        } catch(e) { WPOS.util.hideLoader(); $('#cms-status').text('Sync failed: ' + e.message); }
    }
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
            if (!res || !res.ok) { WPOS.util.hideLoader(); $('#cms-status').text('Error: ' + (res && res.error)); return; }
            syncPosProducts(function(syncInfo){
                WPOS.util.hideLoader();
                $('#cms-status').text('Saved ' + new Date().toLocaleTimeString() + (syncInfo || ''));
                alert('Published. The site is live with your changes.' + (syncInfo || ''));
            });
        }, retry);
    }
    // POST the CMS JSON to the server. cb(ok|fail result). Sends persisted creds
    // so saves survive a stale PHP session; silently re-auths once on 403.
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
    // Explicit "Sync to POS" button: push CMS item details (name/price/image/description)
    // into the linked POS products right now — same routine Save & Publish uses.
    function syncToPosNow(){
        WPOS.util.showLoader();
        syncPosProducts(function(info){
            WPOS.util.hideLoader();
            $('#cms-status').text('Pushed to POS ' + new Date().toLocaleTimeString() + (info || ''));
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
loadCms();
</script>
