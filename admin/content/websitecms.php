<!-- Website CMS — mature content manager for the public website -->
<div class="page-header">
    <h1 style="margin-right:20px; display:inline-block;"><i class="icon-globe"></i> Website CMS</h1>
    <span class="btn btn-default btn-sm pull-right" onclick="window.open('/website/', '_blank')" style="margin-right:6px;"><i class="icon-eye-open"></i> View Site</span>
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
                <label>Launch date <input type="text" id="cms-site-launch-date" class="form-control" style="width:100%; display:block;" /></label>
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
            <span style="font-size:12px; color:#666;">Price auto-syncs from POS when linked to an inventory product.</span>
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
        cmsSet('cms-site-payment-note', s.payment_note); cmsSet('cms-site-launch-date', s.launch_date);
        $('#cms-site-announcement-enabled').prop('checked', !!s.announcement_enabled);
        cmsSet('cms-site-announcement', s.announcement);
        cmsSet('cms-site-seo-title', s.seo_title); cmsSet('cms-site-seo-desc', s.seo_desc);
        cmsSet('cms-site-logo', s.logo); $('#cms-logo-preview').attr('src', s.logo || '');
        cmsSet('cms-site-gallery', (s.gallery || []).join('\n'));
    }
    function collectGeneral(){
        var s = {};
        s.name = cmsGet('cms-site-name'); s.hero_title = cmsGet('cms-site-hero-title'); s.hero_subtitle = cmsGet('cms-site-hero-subtitle');
        s.tagline = cmsGet('cms-site-tagline'); s.phone = cmsGet('cms-site-phone'); s.whatsapp = cmsGet('cms-site-whatsapp');
        s.address = cmsGet('cms-site-address'); s.email = cmsGet('cms-site-email'); s.hours = cmsGet('cms-site-hours');
        s.delivery_area = cmsGet('cms-site-delivery-area'); s.delivery_time = cmsGet('cms-site-delivery-time');
        s.payment_note = cmsGet('cms-site-payment-note'); s.launch_date = cmsGet('cms-site-launch-date');
        s.announcement_enabled = $('#cms-site-announcement-enabled').is(':checked');
        s.announcement = cmsGet('cms-site-announcement');
        s.seo_title = cmsGet('cms-site-seo-title'); s.seo_desc = cmsGet('cms-site-seo-desc');
        s.logo = cmsGet('cms-site-logo');
        s.gallery = cmsGet('cms-site-gallery').split('\n').map(function(x){ return x.trim(); }).filter(Boolean);
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
    function uploadItemImage(){
        var fileInput = document.getElementById('cms-item-file');
        if (!fileInput.files.length) { alert('Choose an image first'); return; }
        var fd = new FormData();
        fd.append('file', fileInput.files[0]);
        WPOS.util.showLoader();
        $.ajax({ url: '/api/website_upload.php', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function(res){
                WPOS.util.hideLoader();
                if (res && res.ok) { $('#cms-item-image').val(res.url); $('#cms-item-preview').attr('src', res.url); }
                else alert(res && res.error ? res.error : 'Upload failed');
            },
            error: function(){ WPOS.util.hideLoader(); alert('Upload failed'); }
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
        // 1) push thumbnails into POS for linked items
        CMS_ITEMS.forEach(function(it){
            if (it.linked_itemid && it.image) {
                try { WPOS.sendJsonData('items/setDetails', JSON.stringify({ itemid: it.linked_itemid, image: it.image })); } catch(e) {}
            }
        });
        // 2) unlinked items: offer to create POS products (so they can be sold on the till)
        if (!unlinked.length) { done(''); return; }
        if (!confirm(unlinked.length + ' menu item(s) have no POS product:\n' + unlinked.map(function(u){ return '· ' + u.name; }).join('\n') + '\n\nCreate POS products so they can be sold on the till?')) { done(''); return; }
        var created = 0;
        unlinked.forEach(function(it){
            try {
                var r = WPOS.sendJsonData('items/createFromMenu', JSON.stringify({ name: it.name, price: it.price, category_label: (categoryLabel(it.category) || it.category), image: it.image }));
                if (r !== false && r !== null) { it.linked_itemid = parseInt(r, 10) || parseInt(r.id, 10) || 0; created++; }
            } catch(e) {}
        });
        if (created > 0) {
            CMS.menu = { items: CMS_ITEMS };
            $.ajax({ url: '/api/save_website_content.php', type: 'POST', data: JSON.stringify(CMS), contentType: 'application/json', dataType: 'json', success: function(){}, error: function(){} });
            info = ' · + ' + created + ' POS product(s) created.';
        }
        done(info);
    }
    function categoryLabel(id){
        var c = (CMS.categories || []).filter(function(x){ return x.id === id; })[0];
        return c ? c.label : id;
    }
    function saveCms(){
        CMS.site = collectGeneral();
        CMS.menu = { items: CMS_ITEMS };
        CMS.categories = Array.from(document.querySelectorAll('#cms-cats-list input[data-ci]')).map(function(el){
            var i = parseInt(el.getAttribute('data-ci'), 10);
            var idEl = document.querySelector('input[data-ciid="' + i + '"]');
            return { id: idEl && idEl.value ? idEl.value : ('cat-' + i), label: el.value };
        });
        WPOS.util.showLoader();
        $.ajax({ url: '/api/save_website_content.php', type: 'POST', data: JSON.stringify(CMS), contentType: 'application/json', dataType: 'json',
            success: function(res){
                if (!res || !res.ok) { WPOS.util.hideLoader(); $('#cms-status').text('Error: ' + (res && res.error)); return; }
                syncPosProducts(function(syncInfo){
                    WPOS.util.hideLoader();
                    $('#cms-status').text('Saved ' + new Date().toLocaleTimeString() + (syncInfo || ''));
                    alert('Published. The site is live with your changes.' + (syncInfo || ''));
                });
            },
            error: function(){ WPOS.util.hideLoader(); $('#cms-status').text('Save failed'); }
        });
    }
    $(function(){ loadCms(); });
</script>
