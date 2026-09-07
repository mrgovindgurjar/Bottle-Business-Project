@include('website.header')



<main id="home">
  <!-- HERO -->
  <section class="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow hero-glow-a"></div><div class="hero-glow hero-glow-b"></div>
    <div class="container hero-container">
      <div class="row align-items-center">
        <div class="col-xl-6 hero-copy">
          <div class="eyebrow reveal"><span></span> CUSTOM BRANDED WATER BOTTLES</div>
          <h1 class="hero-reveal">Your brand.<br><em>Your bottle.</em><br>Your experience.</h1>
          <p class="lead hero-reveal">We turn a plain water bottle into a branded customer touchpoint — purified water, custom label, smart QR and a premium finish designed around your business.</p>
          <div class="hero-actions hero-reveal">
            <a class="btn btn-dark btn-lg rounded-pill px-4" href="#customize">Create a bottle <span>↗</span></a>
            <a class="btn btn-white btn-lg rounded-pill px-4" href="#showcase">See real examples <span>↓</span></a>
          </div>
          <div class="hero-proof hero-reveal">
            <div><b>Custom</b><span>Branding</span></div>
            <i></i>
            <div><b>Smart</b><span>QR labels</span></div>
            <i></i>
            <div><b>Bulk</b><span>Batch orders</span></div>
          </div>
        </div>

        <div class="col-xl-6 hero-visual">
          <div class="visual-label label-left"><span>01</span> YOUR BOTTLE</div>
          <div class="visual-label label-right">CUSTOM WATER BOTTLES <span>●</span></div>
          <div class="hero-stage" id="heroStage">
            <div class="hero-ring ring-one"></div><div class="hero-ring ring-two"></div>
            <div class="floating-sample sample-one">
              <img src="{{  asset('website/img/taste-of-india.png') }}" alt="Restaurant branded bottle example">
              <div><b>Restaurant</b><span>custom label</span></div>
            </div>
            <div class="floating-sample sample-two">
              <img src="{{ asset('website/img/public-house.png') }}" alt="Premium restaurant branded bottle example">
              <div><b>Hospitality</b><span>front + back label</span></div>
            </div>

            <div class="hero-bottle-wrap" id="heroBottle">
              <div class="hero-ad-status">
                <span class="status-dot"></span>
                <b id="heroAdStatus">PLAIN BOTTLE</b>
                <small>YOUR BRAND IN MOTION</small>
              </div>
              <div class="hero-shadow"></div>
              <div class="hero-bottle">
                <div class="hb-cap"></div>
                <div class="hb-neck"></div>
                <div class="hb-body">
                  <div class="hb-shine"></div>
                  <div class="hb-water"></div>
                  <div class="hb-label">
                    <small>SMART WATER · YOUR BRAND</small>
                    <strong>MAKE<br>IT YOURS.</strong>
                    <div class="hb-label-bottom"><span>PURE WATER</span><i class="mini-qr"></i></div>
                  </div>
                  <div class="hb-label-sweep"></div>
                  <div class="hb-bubbles"><i></i><i></i><i></i><i></i><i></i></div>
                </div>
                <div class="hb-base"></div>
              </div>
              <div class="hero-ad-steps">
                <span class="active"><i>01</i> Bottle</span>
                <span><i>02</i> Water</span>
                <span><i>03</i> Brand</span>
                <span><i>04</i> QR</span>
              </div>
            </div>
            <div class="bottle-callout callout-brand"><span>✦</span> Custom artwork</div>
            <div class="bottle-callout callout-qr"><span>⌗</span> QR ready</div>
            <div class="bottle-callout callout-process"><span>✦</span> Built around your brand</div>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-bottom"><span>SCROLL TO EXPLORE</span><b>↓</b></div>
  </section>

  <!-- VALUE -->
  <section class="value-strip">
    <div class="container">
      <div class="value-grid">
        <div><span>01</span><b>Premium bottles</b><small>Multiple sizes & formats</small></div>
        <div><span>02</span><b>Custom branding</b><small>Labels made around you</small></div>
        <div><span>03</span><b>Smart QR</b><small>Menu, website, offer or link</small></div>
        <div><span>04</span><b>Bulk fulfillment</b><small>Built for business orders</small></div>
      </div>
    </div>
  </section>

  <section class="brand-statement">
    <div class="container">
      <div class="statement-line reveal"><span>NOT A GENERIC WATER BOTTLE</span><b>→</b><strong>WE BUILD A BOTTLE AROUND YOUR BRAND.</strong></div>
    </div>
  </section>

  <!-- SOLUTIONS -->
  <section id="solutions" class="section solutions">
    <div class="container">
      <div class="section-head reveal">
        <div><span class="eyebrow">01 / MADE FOR BUSINESS</span><h2>One bottle.<br>Many possibilities.</h2></div>
        <p>Whether it sits on a restaurant table, in a hotel room, at a wedding or in a corporate event kit—the bottle becomes part of the brand experience.</p>
      </div>
      <div class="solution-grid">
        <article class="solution-card solution-feature reveal">
          <div class="solution-number">01</div><div class="solution-icon">◒</div>
          <h3>Restaurants & cafés</h3><p>Put your logo, menu, offers, contact details and QR experience directly in the customer's hand.</p>
          <div class="solution-art restaurant-art"><div class="art-bottle"></div><div class="art-card">YOUR<br>MENU<br><small>SCAN QR</small></div></div>
          <a href="#customize">Build a restaurant bottle →</a>
        </article>
        <article class="solution-card reveal">
          <div class="solution-number">02</div><div class="solution-icon">◇</div>
          <h3>Hotels & hospitality</h3><p>Premium-looking bottles designed to match the property, room and guest experience.</p>
          <div class="solution-art hotel-art"><div class="art-bottle slim"></div></div>
          <a href="#customize">Explore hospitality →</a>
        </article>
        <article class="solution-card reveal">
          <div class="solution-number">03</div><div class="solution-icon">✦</div>
          <h3>Events & weddings</h3><p>Create memorable bottles for celebrations, launches, conferences and special occasions.</p>
          <div class="solution-art event-art"><div class="event-stack"><i></i><i></i><i></i></div></div>
          <a href="#customize">Create an event bottle →</a>
        </article>
      </div>
    </div>
  </section>

  <!-- SHOWCASE -->
  <section id="showcase" class="section showcase">
    <div class="container">
      <div class="section-head reveal">
        <div><span class="eyebrow">02 / REAL BRANDING</span><h2>Imagine your brand<br>looking like this.</h2></div>
        <p>These examples show the direction of the finished product. Your actual artwork is discussed, refined and approved with our team before production.</p>
      </div>
      <div class="showcase-main reveal tilt-card">
        <div class="showcase-photo"><img src="{{  asset('website/img/taste-of-india.png') }}" alt="Taste of India branded water bottle example"><span class="showcase-tag">FOOD BRANDING</span></div>
        <div class="showcase-detail">
          <span class="eyebrow">CUSTOM FRONT + BACK LABEL</span>
          <h3>Your bottle can carry the brand story, not just the logo.</h3>
          <p>Restaurant identity, menu highlights, QR code, contact information and messaging can all be designed into the label.</p>
          <div class="detail-pills"><span>Logo</span><span>QR</span><span>Menu</span><span>Contact</span><span>Brand colors</span></div>
        </div>
      </div>
      <div class="showcase-secondary">
        <div class="secondary-copy reveal"><span class="eyebrow">HOSPITALITY EXAMPLE</span><h3>Front label for impact.<br>Back label for information.</h3><p>A premium black-and-gold treatment can make the same bottle feel completely different.</p></div>
        <div class="secondary-photo reveal tilt-card"><img src="{{  asset('website/img/public-house.png') }}" alt="The Public House branded water bottle example"></div>
      </div>
    </div>
  </section>

  <!-- PROCESS -->
  <section id="process" class="section process">
    <div class="container">
      <div class="section-head reveal">
        <div><span class="eyebrow">03 / OUR PROCESS</span><h2>From empty bottle<br>to your brand.</h2></div>
        <p>We handle the production journey. You bring the brand idea. Together we finalize the design before anything goes into production.</p>
      </div>
      <div class="process-track">
        <div class="process-line"><span></span></div>
        <div class="process-step reveal"><div class="step-node">01</div><div class="step-icon">◇</div><h3>Choose</h3><p>Pick the bottle size, format and approximate batch quantity.</p></div>
        <div class="process-step reveal"><div class="step-node">02</div><div class="step-icon">◌</div><h3>Design</h3><p>Create a preview or share your artwork and branding requirements.</p></div>
        <div class="process-step reveal"><div class="step-node">03</div><div class="step-icon">⌗</div><h3>Discuss</h3><p>Our team talks with you, refines the artwork and confirms the QR destination.</p></div>
        <div class="process-step reveal"><div class="step-node">04</div><div class="step-icon">✓</div><h3>Approve</h3><p>The final artwork is confirmed by you before production starts.</p></div>
        <div class="process-step reveal"><div class="step-node">05</div><div class="step-icon">↗</div><h3>Deliver</h3><p>Water filled, labels applied, quality checked and packed for delivery.</p></div>
      </div>
    </div>
  </section>

  <!-- CUSTOMIZER -->
  <section id="customize" class="section customizer">
  <div class="container">
    <div class="studio-head reveal">
      <div>
        <span class="eyebrow">04 / BRAND STUDIO</span>
        <h2>Build the idea.<br><em>We make it production-ready.</em></h2>
      </div>
      <p>Play with the look, upload a logo, add your QR destination and create a visual concept. Your final artwork is always discussed and approved with our team before production.</p>
    </div>

    <div class="design-studio reveal">
      <div class="studio-stage" id="studioStage">
        <div class="stage-topline"><span>LIVE CONCEPT</span><b>DRAG / MOVE TO ROTATE</b></div>
        <div class="studio-glow"></div>
        <div class="studio-grid"></div>
        <div class="studio-bottle" id="studioBottle">
          <div class="s-cap"></div><div class="s-neck"></div>
          <div class="s-body">
            <div class="s-highlight"></div>
            <div class="s-label front-label" id="frontLabel">
              <span class="label-brand-mini">YOUR BRAND</span>
              <strong id="frontBrand">YOUR<br>BRAND</strong>
              <div class="uploaded-logo-wrap"><img id="logoPreview" alt="Uploaded logo preview"></div>
              <div class="studio-qr" id="frontQr"></div>
              <small>PURE WATER · SMART QR</small>
            </div>
            <div class="s-label back-label" id="backLabel">
              <span>ABOUT YOUR BRAND</span>
              <strong id="backBrand">YOUR BRAND</strong>
              <p>Scan for menu, offers, WhatsApp or any link you choose.</p>
              <div class="studio-qr small" id="backQr"></div>
            </div>
          </div>
        </div>
        <div class="studio-floor"></div>
        <div class="rotation-pill"><span>↻</span> 360° CONCEPT VIEW</div>
      </div>

      <aside class="studio-panel">
        <div class="panel-top">
          <span class="eyebrow">DESIGN REQUEST</span>
          <span class="concept-badge">CONCEPT ONLY</span>
        </div>

        <div class="studio-tabs">
          <button class="studio-tab active" data-face="front">Front</button>
          <button class="studio-tab" data-face="back">Back</button>
        </div>

        <div class="control-block">
          <label>Brand / restaurant name</label>
          <input id="studioBrand" class="form-control form-control-lg rounded-4" value="YOUR BRAND" maxlength="28">
        </div>

        <div class="control-block">
          <label>Upload logo</label>
          <label class="upload-box" for="logoInput">
            <input type="file" id="logoInput" accept="image/png,image/jpeg,image/webp">
            <span class="upload-icon">↑</span>
            <b>Upload your logo</b>
            <small>PNG / JPG / WEBP</small>
          </label>
        </div>

        <div class="control-block">
          <label>Label style</label>
          <div class="style-pills">
            <button class="label-style active" data-style="#101828">Midnight</button>
            <button class="label-style" data-style="#b5121b">Restaurant Red</button>
            <button class="label-style" data-style="#b38a32">Premium Gold</button>
            <button class="label-style" data-style="#0f6d67">Fresh Green</button>
          </div>
        </div>

        <div class="control-block">
          <label>Smart QR destination</label>
          <div class="input-with-icon"><span>⌗</span><input id="qrUrl" value="https://yourbrand.com/menu" placeholder="https://..."></div>
          <small class="helper">Example: digital menu, website, WhatsApp, offer or feedback page.</small>
        </div>

        <div class="control-block">
          <label>Approx. batch quantity</label>
          <div class="quantity-control"><button id="studioMinus">−</button><strong id="studioQty">500</strong><span>bottles</span><button id="studioPlus">+</button></div>
        </div>

        <div class="studio-estimate">
          <div><span>Design request</span><b>FREE</b></div>
          <div><span>Approx. batch</span><strong id="studioEstimate">₹13,000</strong></div>
        </div>

        <button class="btn btn-dark btn-lg rounded-pill w-100 py-3" id="submitDesign">Send design idea & request quote ↗</button>
        <p class="approval-note"><span>✓</span> Final label artwork, print details and QR destination are confirmed with you before production.</p>
      </aside>
    </div>
  </div>
</section>


<!-- DESIGN REQUEST FLOW -->
<div class="request-modal" id="requestModal" aria-hidden="true">
  <div class="request-backdrop" data-close-request></div>
  <div class="request-dialog">
    <button class="request-close" data-close-request aria-label="Close">×</button>
    <div class="request-progress"><span id="requestProgress"></span></div>
    <div class="request-head"><span class="eyebrow">AQUAFORM / DESIGN REQUEST</span><span class="request-step-label" id="requestStepLabel">01 / 04</span></div>
    <div class="request-body">
      <div class="request-step active" data-step="1">
        <h3>Tell us about<br><em>your business.</em></h3>
        <p>We'll use this to understand your requirement and contact you about the bottle design.</p>
        <div class="request-grid">
          <div class="request-field full"><label>Business name *</label><input id="rqBusiness" placeholder="e.g. Taste of India"></div>
          <div class="request-field"><label>Your name *</label><input id="rqName" placeholder="Your name"></div>
          <div class="request-field"><label>Mobile / WhatsApp *</label><input id="rqPhone" placeholder="+91"></div>
          <div class="request-field"><label>Email</label><input id="rqEmail" type="email" placeholder="you@company.com"></div>
          <div class="request-field"><label>Business type</label><select id="rqType"><option>Restaurant / Café</option><option>Hotel / Hospitality</option><option>Corporate</option><option>Wedding / Event</option><option>Gym / Fitness</option><option>Other</option></select></div>
        </div>
      </div>
      <div class="request-step" data-step="2">
        <h3>What bottle are<br>you <em>imagining?</em></h3>
        <p>Don't worry if you're unsure. Your team can suggest the best option.</p>
        <div class="choice-group"><label>Preferred bottle size</label><div class="choice-grid">
          <button class="choice active" data-group="size" data-value="500ml"><b>500ml</b><small>Most popular</small></button>
          <button class="choice" data-group="size" data-value="750ml"><b>750ml</b><small>Premium table</small></button>
          <button class="choice" data-group="size" data-value="1 Litre"><b>1 L</b><small>Large format</small></button>
          <button class="choice" data-group="size" data-value="Not sure"><b>Not sure</b><small>We'll suggest</small></button>
        </div></div>
        <div class="choice-group"><label>Approximate quantity</label><div class="choice-grid">
          <button class="choice active" data-group="batch" data-value="500–999"><b>500–999</b><small>bottles</small></button>
          <button class="choice" data-group="batch" data-value="1,000–4,999"><b>1K–4.9K</b><small>bottles</small></button>
          <button class="choice" data-group="batch" data-value="5,000+"><b>5K+</b><small>bottles</small></button>
          <button class="choice" data-group="batch" data-value="Need advice"><b>Need advice</b><small>Help me choose</small></button>
        </div></div>
      </div>
      <div class="request-step" data-step="3">
        <h3>Tell us about<br>the <em>branding.</em></h3>
        <p>Send an existing design or simply explain what you want. Our team will refine it with you.</p>
        <div class="request-grid">
          <div class="request-field full"><label>What would you like on the bottle?</label><textarea id="rqBranding" rows="4" placeholder="Logo, restaurant name, tagline, menu QR, contact details, offers..."></textarea></div>
          <div class="request-field full"><label>QR destination</label><input id="rqQr" placeholder="https://yourwebsite.com/menu"></div>
          <div class="request-field full"><label>Existing artwork / logo</label><label class="request-upload" for="rqFile"><input type="file" id="rqFile" accept=".png,.jpg,.jpeg,.webp,.pdf"><span>↑</span><b id="rqFileName">Upload logo or artwork</b><small>PNG, JPG, WEBP or PDF</small></label></div>
        </div>
      </div>
      <div class="request-step" data-step="4">
        <h3>Anything else<br>we should <em>know?</em></h3>
        <p>More context helps us prepare for the first conversation.</p>
        <div class="request-field"><label>Additional requirement</label><textarea id="rqNotes" rows="7" placeholder="Delivery city, event date, preferred look, timeline, special requirements..."></textarea></div>
        <div class="request-summary"><div><span>Business</span><b id="sumBusiness">—</b></div><div><span>Bottle</span><b id="sumBottle">500ml · 500–999</b></div><div><span>Contact</span><b id="sumContact">—</b></div></div>
      </div>
      <div class="request-success" id="requestSuccess">
        <div class="success-orbit"><span>✓</span></div><span class="eyebrow">REQUEST RECEIVED</span>
        <h3>Your bottle idea<br>is <em>with our team.</em></h3>
        <p>We'll contact you to discuss the design, quote and production details. Final artwork is approved with you before production.</p>
        <button class="btn btn-dark rounded-pill px-4" data-close-request>Back to website ↗</button>
      </div>
    </div>
    <div class="request-footer" id="requestFooter"><button class="request-back" id="requestBack">← Back</button><div><span id="requestHint">Step 1 of 4</span><button class="btn btn-dark rounded-pill px-4" id="requestNext">Continue →</button></div></div>
  </div>
</div>
  <!-- QR -->
  <section class="section qr-section">
    <div class="container">
      <div class="qr-shell reveal">
        <div class="qr-visual">
          <div class="qr-orbit q1"></div><div class="qr-orbit q2"></div>
          <div class="smart-bottle">
            <div class="sb-cap"></div><div class="sb-neck"></div><div class="sb-body"><div class="sb-label"><span>SMART LABEL</span><strong>SCAN.<br>CONNECT.</strong><div class="qr-pattern"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div><small>YOUR DIGITAL EXPERIENCE</small></div></div>
          </div>
          <div class="qr-float">QR READY <b>●</b></div>
        </div>
        <div class="qr-content">
          <span class="eyebrow">05 / SMART QR</span>
          <h2>The bottle becomes your digital doorway.</h2>
          <p>Your QR can lead customers to a digital menu, website, WhatsApp, offer, booking page, feedback form, Instagram or any URL you choose.</p>
          <div class="qr-links"><div><b>⌗ Digital menu</b><span>Perfect for restaurants</span></div><div><b>↗ WhatsApp</b><span>Start a conversation</span></div><div><b>✦ Offers</b><span>Run campaigns</span></div><div><b>✓ Feedback</b><span>Collect reviews</span></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- CUSTOMER -->
  <section id="account" class="section account">
    <div class="container">
      <div class="section-head reveal"><div><span class="eyebrow">06 / CUSTOMER PANEL</span><h2>Order once.<br>Reorder easily.</h2></div><p>Once the Laravel customer panel is connected, customers will have their complete design and order history in one place.</p></div>
      <div class="dashboard-card reveal">
        <div class="dash-side"><div class="brand mini-brand"><span class="brand-icon">A</span>qua<span>Form</span></div><span class="dash-active">Overview</span><span>Design requests</span><span>Orders</span><span>Invoices</span><span>Profile</span></div>
        <div class="dash-main"><div class="dash-top"><div><small>GOOD MORNING</small><h3>Your bottle workspace</h3></div><span class="dash-user">MG</span></div><div class="dash-metrics"><div><span>Active orders</span><b>02</b></div><div><span>Designs approved</span><b>08</b></div><div><span>Delivered batches</span><b>17</b></div></div><div class="dash-order"><div class="dash-bottle"></div><div><b>Restaurant Summer Batch</b><small>2,000 × 750ml • Custom QR label</small></div><span class="dash-status">In production</span><button>View →</button></div><div class="dash-order"><div class="dash-bottle gold"></div><div><b>Launch Event Bottles</b><small>500 × 500ml • Final design approved</small></div><span class="dash-status approved">Approved</span><button>Reorder →</button></div></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="final-cta">
    <div class="cta-noise"></div><div class="cta-orb"></div>
    <div class="container text-center position-relative">
      <span class="eyebrow light">READY WHEN YOU ARE</span>
      <h2>Let's put your<br><em>brand on the bottle.</em></h2>
      <p>Share your idea. We'll help turn it into a finished branded water bottle.</p>
      <a href="#customize" class="btn btn-light btn-lg rounded-pill px-5 py-3">Start a design request ↗</a>
    </div>
  </section>
</main>


@include('website.footer')