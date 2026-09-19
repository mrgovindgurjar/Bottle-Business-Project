@php($issue = $issue ?? null)
<div class="issue-grid">
    <div class="issue-card">
        <h3>Issue Details</h3>
        <div class="issue-fields">
            <label>Title *<input name="title" value="{{ old('title',$issue?->title) }}" required></label>
            <label>Category<select name="category_id"><option value="">Select category</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$issue?->category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></label>
            <label>Priority<select name="priority" required>@foreach(['low','normal','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority',$issue?->priority ?? 'normal')===$p)>{{ ucfirst($p) }}</option>@endforeach</select></label>
            <label>Status<select name="status">@foreach(['open','in_progress','pending_customer','resolved','closed','cancelled'] as $s)<option value="{{ $s }}" @selected(old('status',$issue?->status ?? 'open')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select></label>
            <label>Source<input name="source" value="{{ old('source',$issue?->source) }}" placeholder="Phone / WhatsApp / Delivery / Internal"></label>
            <label>Due Date<input type="date" name="due_date" value="{{ old('due_date',$issue?->due_date?->format('Y-m-d')) }}"></label>
            <label class="span-2">Description *<textarea name="description" rows="6" required>{{ old('description',$issue?->description) }}</textarea></label>
            <label class="span-2">Resolution<textarea name="resolution" rows="4">{{ old('resolution',$issue?->resolution) }}</textarea></label>
            <label class="span-2">Attachments<input type="file" name="attachment_files[]" multiple></label>
            <label class="check"><input type="checkbox" name="customer_visible" value="1" @checked(old('customer_visible',$issue?->customer_visible ?? true))> Customer visible</label>
        </div>
    </div>

    <div class="issue-card">
        <h3>Links & Assignment</h3>
        <div class="issue-fields">
            <label>Customer<select name="customer_id"><option value="">None</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id',$issue?->customer_id)==$c->id)>{{ $c->business_name }}</option>@endforeach</select></label>
            <label>Assigned Staff<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $s)<option value="{{ $s->id }}" @selected(old('assigned_to',$issue?->assigned_to)==$s->id)>{{ $s->full_name }}{{ $s->designation ? ' — '.$s->designation : '' }}</option>@endforeach</select></label>
            <label>Order<select name="order_id"><option value="">None</option>@foreach($orders as $o)<option value="{{ $o->id }}" @selected(old('order_id',$issue?->order_id)==$o->id)>{{ $o->order_number ?? ('Order #'.$o->id) }}</option>@endforeach</select></label>
            <label>Delivery<select name="delivery_id"><option value="">None</option>@foreach($deliveries as $d)<option value="{{ $d->id }}" @selected(old('delivery_id',$issue?->delivery_id)==$d->id)>{{ $d->delivery_number ?? ('Delivery #'.$d->id) }}</option>@endforeach</select></label>
            <label>Batch<select name="batch_id"><option value="">None</option>@foreach($batches as $b)<option value="{{ $b->id }}" @selected(old('batch_id',$issue?->batch_id)==$b->id)>{{ $b->batch_number ?? ('Batch #'.$b->id) }}</option>@endforeach</select></label>
            <label>Reported At<input type="datetime-local" name="reported_at" value="{{ old('reported_at',$issue?->reported_at?->format('Y-m-d\TH:i')) }}"></label>
        </div>
    </div>
</div>
