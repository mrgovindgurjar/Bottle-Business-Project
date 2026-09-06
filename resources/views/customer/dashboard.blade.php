@extends('customer.layout.main')
    
    @section('title', 'Customer panel')
    
    
    
     @section('content')
     <main class="portal-main">
      <!-- OVERVIEW -->
      <section class="portal-view active" id="view-overview">
        <div class="portal-welcome">
          <div>
            <span class="portal-eyebrow">CUSTOMER DASHBOARD / 25 AUG 2026</span>
            <h1>Good evening,<br><em>Taste of India.</em></h1>
            <p>Here’s what’s happening with your branded bottle orders.</p>
          </div>
          <button class="portal-primary" onclick="showPortalView('designs')">+ Start a new design</button>
        </div>

        <div class="portal-hero-order">
          <div class="order-hero-copy">
            <div class="order-id-row"><span class="live-dot"></span> ACTIVE PROJECT <b>AQ-1024</b></div>
            <h2>Your bottles are<br><em>in production.</em></h2>
            <p>2,000 × 750ml custom branded bottles</p>
            <div class="production-progress">
              <div class="production-track"><span style="width:68%"></span></div>
              <div><strong>68%</strong><small>Production progress</small></div>
            </div>
            <div class="order-meta">
              <div><span>Expected dispatch</span><b>12 Sep 2026</b></div>
              <div><span>Order value</span><b>₹52,000</b></div>
              <div><span>Design</span><b class="approved">Approved ✓</b></div>
            </div>
            <button class="text-action" onclick="showPortalView('orders')">View production journey →</button>
          </div>
          <div class="order-visual">
            <div class="portal-orbit orbit-a"></div><div class="portal-orbit orbit-b"></div>
            <div class="portal-bottle">
              <div class="pb-cap"></div><div class="pb-neck"></div>
              <div class="pb-body"><div class="pb-glass"></div><div class="pb-label"><small>TASTE OF INDIA</small><strong>PURE<br>TASTE.</strong><i></i></div></div>
            </div>
            <div class="visual-chip chip-top">● LIVE PRODUCTION</div>
            <div class="visual-chip chip-bottom">750ml · 2,000 units</div>
          </div>
        </div>

        <div class="portal-section-title"><div><span>YOUR ACTIVITY</span><h3>Recent orders</h3></div><button onclick="showPortalView('orders')">View all →</button></div>
        <div class="recent-orders">
          <article class="recent-card"><div class="mini-order-bottle red"></div><div><span>AQ-1024 · 24 AUG 2026</span><strong>Taste of India — 750ml</strong><small>2,000 bottles · Production</small></div><b class="status production">Production</b></article>
          <article class="recent-card"><div class="mini-order-bottle gold"></div><div><span>AQ-0981 · 05 AUG 2026</span><strong>Taste of India — 500ml</strong><small>1,000 bottles · Delivered</small></div><b class="status delivered">Delivered</b></article>
        </div>
      </section>

      <!-- ORDERS -->
      <section class="portal-view" id="view-orders">
        <div class="portal-page-heading"><span class="portal-eyebrow">ORDER MANAGEMENT</span><h1>My <em>orders.</em></h1><p>Track every bottle from approved artwork to your doorstep.</p></div>
        <div class="order-list-card">
          <div class="order-list-head"><div><span>AQ-1024</span><strong>2,000 × 750ml</strong></div><b class="status production">Production · 68%</b></div>
          <div class="journey">
            <div class="journey-line"><span style="width:68%"></span></div>
            <div class="journey-step done"><i>✓</i><b>Request</b><small>24 Aug</small></div>
            <div class="journey-step done"><i>✓</i><b>Design approved</b><small>26 Aug</small></div>
            <div class="journey-step done"><i>✓</i><b>Bottles ready</b><small>29 Aug</small></div>
            <div class="journey-step current"><i>●</i><b>Water filling</b><small>In progress</small></div>
            <div class="journey-step"><i>05</i><b>Labeling</b><small>Next</small></div>
            <div class="journey-step"><i>06</i><b>Quality check</b><small>Pending</small></div>
            <div class="journey-step"><i>07</i><b>Dispatch</b><small>12 Sep</small></div>
          </div>
          <div class="order-list-footer"><span>Last updated 25 Aug 2026 · 4:40 PM</span><button class="portal-outline">View details</button><button class="portal-primary small">Reorder later ↗</button></div>
        </div>
        <div class="order-list-card past"><div class="order-list-head"><div><span>AQ-0981</span><strong>1,000 × 500ml</strong></div><b class="status delivered">Delivered</b></div><div class="past-order-info"><div class="past-bottle"></div><div><strong>Delivered on 18 Aug 2026</strong><p>Same approved artwork can be reused for your next batch.</p><button class="portal-primary small">Reorder same design ↗</button></div></div></div>
      </section>

      <!-- DESIGNS -->
      <section class="portal-view" id="view-designs">
        <div class="portal-page-heading"><span class="portal-eyebrow">DESIGN STUDIO</span><h1>Your <em>designs.</em></h1><p>Review concepts, request revisions and approve final artwork before production.</p></div>
        <div class="design-approval-card">
          <div class="design-preview">
            <div class="design-preview-bottle"><div class="dp-cap"></div><div class="dp-body"><div class="dp-label"><small>TASTE OF INDIA</small><strong>PURE<br>TASTE.</strong><div class="dp-qr"></div></div></div></div>
            <span class="preview-badge">VERSION 03</span>
            <span class="preview-note">Final artwork preview</span>
          </div>
          <div class="design-details">
            <div class="design-status"><span>●</span> Awaiting your approval</div>
            <h2>Taste of India<br><em>Premium Label</em></h2>
            <p>Front + back label concept with your restaurant branding, menu QR and contact details.</p>
            <div class="design-checks"><span>✓ Logo placement</span><span>✓ QR destination</span><span>✓ Contact details</span><span>✓ Print-safe artwork</span></div>
            <div class="design-actions"><button class="portal-primary" onclick="approveDesign()">Approve design ✓</button><button class="portal-outline" onclick="requestRevision()">Request changes</button></div>
            <small class="design-safe">Final production starts only after your approval.</small>
          </div>
        </div>
        <div class="revision-history"><div><span>VERSION HISTORY</span><h3>Previous revisions</h3></div><div class="revision-row"><b>V03</b><strong>Updated QR + back label</strong><small>25 Aug · Current</small><span class="approved">Current</span></div><div class="revision-row"><b>V02</b><strong>Adjusted logo size</strong><small>24 Aug</small><span>Superseded</span></div><div class="revision-row"><b>V01</b><strong>Initial concept</strong><small>23 Aug</small><span>Superseded</span></div></div>
      </section>

      <!-- DOCUMENTS -->
      <section class="portal-view" id="view-documents">
        <div class="portal-page-heading"><span class="portal-eyebrow">DOCUMENT CENTER</span><h1>Your <em>documents.</em></h1><p>Quotes, invoices, artwork and order summaries in one place.</p></div>
        <div class="document-grid">
          <article class="document-card"><span>PDF</span><div><strong>Quotation · AQ-1024</strong><small>Generated 24 Aug 2026 · ₹52,000</small></div><button>↓</button></article>
          <article class="document-card"><span>ART</span><div><strong>Final artwork · V03</strong><small>Approved production artwork</small></div><button>↓</button></article>
          <article class="document-card"><span>PDF</span><div><strong>Order summary · AQ-1024</strong><small>2,000 × 750ml · Production</small></div><button>↓</button></article>
          <article class="document-card"><span>PDF</span><div><strong>Invoice · AQ-0981</strong><small>Paid · 18 Aug 2026</small></div><button>↓</button></article>
        </div>
      </section>

      <!-- PROFILE -->
      <section class="portal-view" id="view-profile">
        <div class="portal-page-heading"><span class="portal-eyebrow">BUSINESS PROFILE</span><h1>Your <em>business.</em></h1><p>Keep your contact and delivery details up to date.</p></div>
        <div class="profile-card">
          <div class="profile-avatar-large">TI</div>
          <div class="profile-fields"><label>Business name<input value="Taste of India"></label><label>Contact person<input value="Rahul Sharma"></label><label>Mobile / WhatsApp<input value="+91 9XXXXXXXXX"></label><label>Email<input value="orders@tasteofindia.example"></label><label>Delivery city<input value="Bhopal, Madhya Pradesh"></label><label>Business type<select><option>Restaurant / Café</option><option>Hotel / Hospitality</option><option>Corporate</option></select></label></div>
          <button class="portal-primary">Save changes</button>
        </div>
      </section>
    </main>
    @endsection