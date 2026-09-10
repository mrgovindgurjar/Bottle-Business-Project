@csrf
<div class="product-form-sections">
    <section class="product-form-section">
        <div class="product-section-intro"><span class="product-step">01</span><div><h3>Product Identity</h3><p>Define the catalog identity customers and staff will see.</p></div></div>
        <div class="product-form-grid product-grid-2">
            <div class="form-group"><label class="form-label">Product Name <b>*</b></label><input class="form-control" name="name" value="{{ old('name',$product->name ?? '') }}" placeholder="e.g. Premium 750ml Custom Bottle" required>@error('name')<div class="field-error">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label class="form-label">SKU <b>*</b></label><input class="form-control" name="sku" value="{{ old('sku',$product->sku ?? '') }}" placeholder="e.g. JAL-750-PET" required><small class="form-hint">Letters, numbers, dots, hyphens and underscores only.</small>@error('sku')<div class="field-error">{{ $message }}</div>@enderror</div>
        </div>
        <div class="product-form-grid product-grid-3">
            <div class="form-group"><label class="form-label">Bottle Size (ml) <b>*</b></label><input type="number" min="1" class="form-control" name="bottle_size_ml" value="{{ old('bottle_size_ml',$product->bottle_size_ml ?? '') }}" placeholder="750" required></div>
            <div class="form-group"><label class="form-label">Bottle Type</label><select class="form-control" name="bottle_type"><option value="">Select type</option>@foreach(['PET','Glass','HDPE','Aluminium','Other'] as $v)<option value="{{ $v }}" @selected(old('bottle_type',$product->bottle_type ?? '')===$v)>{{ $v }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Material</label><select class="form-control" name="material"><option value="">Select material</option>@foreach(['PET','rPET','Glass','HDPE','Aluminium','Other'] as $v)<option value="{{ $v }}" @selected(old('material',$product->material ?? '')===$v)>{{ $v }}</option>@endforeach</select></div>
        </div>
    </section>

    <section class="product-form-section">
        <div class="product-section-intro"><span class="product-step">02</span><div><h3>Packaging & Branding</h3><p>Keep production-ready bottle specifications in one place.</p></div></div>
        <div class="product-form-grid product-grid-3">
            <div class="form-group"><label class="form-label">Cap Type</label><select class="form-control" name="cap_type"><option value="">Select cap</option>@foreach(['Screw Cap','Flip Cap','Sports Cap','Other'] as $v)<option value="{{ $v }}" @selected(old('cap_type',$product->cap_type ?? '')===$v)>{{ $v }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Label Type</label><select class="form-control" name="label_type"><option value="">Select label</option>@foreach(['Sticker Label','Shrink Sleeve','Direct Print','Paper Label','No Label','Other'] as $v)<option value="{{ $v }}" @selected(old('label_type',$product->label_type ?? '')===$v)>{{ $v }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Units per Box <b>*</b></label><input type="number" min="1" class="form-control" name="units_per_box" value="{{ old('units_per_box',$product->units_per_box ?? 1) }}" required></div>
        </div>
        <div class="product-form-grid product-grid-2">
            <div class="form-group"><label class="form-label">Selling Unit <b>*</b></label><select class="form-control" name="unit"><option value="bottle" @selected(old('unit',$product->unit ?? 'bottle')==='bottle')>Bottle</option><option value="box" @selected(old('unit',$product->unit ?? '')==='box')>Box</option><option value="piece" @selected(old('unit',$product->unit ?? '')==='piece')>Piece</option></select></div>
            <div class="form-group product-switch-group"><label class="form-label">Custom Branding</label><label class="product-switch"><input type="checkbox" name="is_custom_branding" value="1" @checked(old('is_custom_branding',$product->is_custom_branding ?? true))><span></span><strong>Brand-ready product</strong></label><small class="form-hint">Enable when logo/label/QR customization is part of the product.</small></div>
        </div>
    </section>

    <section class="product-form-section">
        <div class="product-section-intro"><span class="product-step">03</span><div><h3>Presentation</h3><p>Add the catalog image and useful internal description.</p></div></div>
        <div class="product-form-grid product-grid-2">
            <div>
                <div class="form-group"><label class="form-label">Product Image</label><div class="product-upload" data-product-upload><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-product-image-input><div class="product-upload-preview" data-product-preview>@if(!empty($product?->image_url))<img src="{{ $product->image_url }}" alt="Product">@else<span>＋</span>@endif</div><div><strong>Upload product photo</strong><small>JPG, PNG or WebP · Max 2MB</small></div></div></div>
            </div>
            <div><div class="form-group"><label class="form-label">Short Description</label><input class="form-control" name="short_description" maxlength="300" value="{{ old('short_description',$product->short_description ?? '') }}" placeholder="Premium 750ml PET bottle for restaurant branding."></div><div class="form-group"><label class="form-label">Internal Description</label><textarea class="form-control" name="description" rows="5" placeholder="Production notes, handling details, etc.">{{ old('description',$product->description ?? '') }}</textarea></div></div>
        </div>
    </section>

    <section class="product-form-section">
        <div class="product-section-intro"><span class="product-step">04</span><div><h3>Availability</h3><p>Control whether this product can be selected in future commercial workflows.</p></div></div>
        <div class="product-form-grid product-grid-3">
            <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><option value="active" @selected(old('status',$product->status ?? 'active')==='active')>Active</option><option value="inactive" @selected(old('status',$product->status ?? '')==='inactive')>Inactive</option></select></div>
            <div class="form-group"><label class="form-label">Display Order</label><input type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order',$product->sort_order ?? 0) }}"></div>
        </div>
    </section>
</div>
<div class="product-form-actions"><a href="{{ isset($product) ? route('admin.products.show',$product) : route('admin.products.index') }}" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary"><span>{{ isset($product) ? 'Save Changes' : 'Create Product' }}</span> →</button></div>
