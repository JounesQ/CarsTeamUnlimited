<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const pillars = [
  {
    key: 'reliable',
    title: 'Reliable',
    copy: 'Nothing reaches the floor untouched. Each unit is road-tested, mechanically checked, and its paperwork traced back to the registered owner before we price it.',
  },
  {
    key: 'financing',
    title: 'Financing',
    copy: 'We handle the bank legwork in-house. Bring your IDs and proof of income and we can come back with a decision in as fast as one day.',
  },
  {
    key: 'trade-in',
    title: 'Trade-In',
    copy: 'Drive in with your current car and we will appraise it on the spot, then apply that value straight to the unit you actually want.',
  },
]

const branches = [
  { name: 'Showroom 1', area: 'General Trias' },
  { name: 'Showroom 2', area: 'General Trias' },
  { name: 'Showroom 3', area: 'General Trias' },
  { name: 'Showroom 4', area: 'Silang' },
]
</script>

<template>
  <main class="about">
    <section class="hero">
      <div class="hero-inner">
        <span class="flag">About Us</span>
        <h1>We sell cars the way we'd want one sold to us.</h1>
        <p class="lead">
          Cars Team Unlimited is a Cavite-based buy-and-sell team running four showrooms across
          General Trias and Silang. We are not a listing board — every unit we advertise is one we
          physically hold, inspected and priced by the same people who will hand you the keys.
        </p>
      </div>
      <div class="hero-checker"></div>
    </section>

    <section class="pillars">
      <div class="pillars-inner">
        <div v-for="pillar in pillars" :key="pillar.key" class="pillar">
          <span class="pillar-title">{{ pillar.title }}</span>
          <p>{{ pillar.copy }}</p>
        </div>
      </div>
    </section>

    <section class="story">
      <div class="story-inner">
        <div class="story-text">
          <h2>How we got here</h2>
          <p>
            We started with a single lot and a simple frustration: buyers were being shown units
            nobody had properly checked, at prices nobody would explain. So we built the opposite.
          </p>
          <p>
            Today the team runs four branches, but the process has not changed. We buy selectively,
            we document everything, and we quote one price that stays negotiable once you have seen
            the car in person. If a unit has a scratch, a replaced part, or a quirk in its history,
            you will hear it from us before you ask.
          </p>
          <p>
            Most of our customers are first-time buyers and small business owners. That shapes how we
            work — patient walkthroughs, honest answers about running costs, and financing options
            explained in plain terms instead of jargon.
          </p>
        </div>

        <aside class="story-facts">
          <h3>At a glance</h3>
          <dl>
            <div>
              <dt>Based in</dt>
              <dd>Cavite, Philippines</dd>
            </div>
            <div>
              <dt>Showrooms</dt>
              <dd>4 branches</dd>
            </div>
            <div>
              <dt>Open</dt>
              <dd>8:00 AM – 6:00 PM, Mon–Sun</dd>
            </div>
            <div>
              <dt>Financing</dt>
              <dd>Approval in as fast as 1 day</dd>
            </div>
            <div>
              <dt>Trade-in</dt>
              <dd>Accepted at every branch</dd>
            </div>
          </dl>
        </aside>
      </div>
    </section>

    <section class="visit">
      <div class="visit-inner">
        <div class="visit-head">
          <span class="flag">What to expect</span>
          <h2>Walking into any branch</h2>
        </div>
        <div class="visit-grid">
          <div class="visit-card">
            <h3>No appointment needed</h3>
            <p>
              Come by during opening hours and ask for whichever unit caught your eye online. If it
              is parked at another branch, we will tell you straight away rather than sell you
              something else.
            </p>
          </div>
          <div class="visit-card">
            <h3>Test drives are standard</h3>
            <p>
              Bring your licence and you can drive it. We would rather you find a problem during the
              test drive than a week after the sale.
            </p>
          </div>
          <div class="visit-card">
            <h3>Nobody follows you around</h3>
            <p>
              Take your time, open every door, look underneath. Our team stays nearby for questions
              and otherwise leaves you to it.
            </p>
          </div>
          <div class="visit-card">
            <h3>Numbers before commitment</h3>
            <p>
              Ask for a full computation — down payment, monthly amortisation, and what your trade-in
              is worth — before you decide anything.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="network">
      <div class="network-inner">
        <div class="network-head">
          <span class="flag">Our Network</span>
          <h2>Four showrooms, one inventory</h2>
          <p>
            Stock moves between branches, so what you see online is available to view at whichever
            location is closest to you — just tell us where you are heading.
          </p>
        </div>
        <ul class="branch-list">
          <li v-for="branch in branches" :key="branch.name">
            <span class="branch-name">{{ branch.name }}</span>
            <span class="branch-area">{{ branch.area }}</span>
          </li>
        </ul>
      </div>
    </section>

    <section class="cta-band">
      <div class="cta-inner">
        <h2>Ready to see one in person?</h2>
        <p>Browse what is on the floor right now, or head to the map and pick your nearest branch.</p>
        <div class="cta-actions">
          <button type="button" class="cta-primary" @click="router.push('/vehicles')">
            Browse Vehicles
          </button>
          <RouterLink to="/" class="cta-secondary">See showroom locations</RouterLink>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.about {
  min-height: 100vh;
  background: var(--color-background);
  color: var(--color-text);
  padding-bottom: 0;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.flag {
  display: inline-block;
  padding: 0.35rem 1.25rem;
  background: var(--red-primary);
  color: var(--white-pure);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 3px;
  text-transform: uppercase;
  border-radius: 4px;
}

/* Hero */
.hero {
  position: relative;
  padding: 5rem 1.5rem 4.5rem;
  background: var(--gradient-hero-alt);
  overflow: hidden;
}

.hero-inner {
  max-width: 900px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.hero h1 {
  font-size: 2.75rem;
  font-weight: 900;
  line-height: 1.15;
  color: var(--color-heading);
  margin: 1.25rem 0 1.25rem;
  max-width: 18ch;
}

.lead {
  font-size: 1.1rem;
  line-height: 1.75;
  color: var(--color-text-muted);
  max-width: 62ch;
}

.hero-checker {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 12px;
  background-image:
    linear-gradient(45deg, var(--checker-square) 25%, transparent 25%, transparent 75%, var(--checker-square) 75%),
    linear-gradient(45deg, var(--checker-square) 25%, transparent 25%, transparent 75%, var(--checker-square) 75%);
  background-size: 12px 12px;
  background-position: 0 0, 6px 6px;
  background-color: var(--checker-base);
  opacity: var(--checker-opacity);
}

/* Pillars */
.pillars {
  padding: 4rem 1.5rem;
  background: var(--color-background-soft);
  transition: background-color 0.3s ease;
}

.pillars-inner {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.pillar {
  padding: 2rem 1.75rem;
  border-radius: 14px;
  background: var(--gradient-card);
  border: 1px solid var(--color-card-border);
  border-top: 3px solid var(--red-primary);
  transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.pillar:hover {
  transform: translateY(-5px);
  border-color: rgba(216, 31, 38, 0.5);
  border-top-color: var(--red-light);
  box-shadow: 0 14px 34px rgba(216, 31, 38, 0.18);
}

.pillar-title {
  display: block;
  font-size: 1.2rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--color-heading);
  margin-bottom: 0.75rem;
}

.pillar p {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--color-text-muted);
}

/* Story */
.story {
  padding: 5rem 1.5rem;
  background: var(--color-background);
  transition: background-color 0.3s ease;
}

.story-inner {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
  gap: 3rem;
  align-items: start;
}

.story-text h2 {
  font-size: 2rem;
  font-weight: 900;
  color: var(--color-heading);
  margin-bottom: 1.25rem;
}

.story-text p {
  font-size: 1rem;
  line-height: 1.8;
  color: var(--color-text-muted);
}

.story-text p + p {
  margin-top: 1rem;
}

.story-facts {
  padding: 1.75rem;
  border-radius: 14px;
  background: var(--gradient-card-accent);
  border: 1px solid rgba(216, 31, 38, 0.35);
  position: sticky;
  top: 100px;
}

.story-facts h3 {
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--color-accent-text);
  margin-bottom: 1.25rem;
}

.story-facts dl {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.story-facts dl > div {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid var(--color-card-border);
}

.story-facts dl > div:last-child {
  padding-bottom: 0;
  border-bottom: 0;
}

.story-facts dt {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--color-text-muted);
}

.story-facts dd {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-heading);
}

/* Visit */
.visit {
  padding: 5rem 1.5rem;
  background: var(--color-background-soft);
  border-top: 1px solid rgba(216, 31, 38, 0.15);
  transition: background-color 0.3s ease;
}

.visit-inner {
  max-width: 1100px;
  margin: 0 auto;
}

.visit-head {
  margin-bottom: 2.5rem;
}

.visit-head h2 {
  font-size: 2rem;
  font-weight: 900;
  color: var(--color-heading);
  margin-top: 1rem;
}

.visit-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
}

.visit-card {
  padding: 1.75rem;
  border-radius: 14px;
  background: var(--gradient-card);
  border: 1px solid var(--color-card-border);
  border-left: 3px solid rgba(216, 31, 38, 0.6);
  transition: border-left-color 0.3s ease, transform 0.3s ease;
}

.visit-card:hover {
  border-left-color: var(--red-primary);
  transform: translateX(5px);
}

.visit-card h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-heading);
  margin-bottom: 0.6rem;
}

.visit-card p {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--color-text-muted);
}

/* Network */
.network {
  padding: 5rem 1.5rem;
  background: var(--color-background);
  transition: background-color 0.3s ease;
}

.network-inner {
  max-width: 1100px;
  margin: 0 auto;
}

.network-head {
  margin-bottom: 2.5rem;
  max-width: 62ch;
}

.network-head h2 {
  font-size: 2rem;
  font-weight: 900;
  color: var(--color-heading);
  margin: 1rem 0 0.85rem;
}

.network-head p {
  font-size: 1rem;
  line-height: 1.75;
  color: var(--color-text-muted);
}

.branch-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.branch-list li {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  padding: 1.25rem;
  border-radius: 12px;
  background: var(--color-background-mute);
  border: 1px solid var(--color-card-border);
  transition: border-color 0.3s ease, background 0.3s ease;
}

.branch-list li:hover {
  border-color: rgba(216, 31, 38, 0.5);
  background: rgba(216, 31, 38, 0.08);
}

.branch-name {
  font-size: 1rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--color-heading);
}

.branch-area {
  font-size: 0.85rem;
  color: var(--color-accent-text);
}

/* CTA band */
.cta-band {
  padding: 4rem 1.5rem;
  background: linear-gradient(135deg, #a3141a 0%, #d81f26 60%, #f0353d 100%);
}

.cta-inner {
  max-width: 1100px;
  margin: 0 auto;
  text-align: center;
}

.cta-band h2 {
  font-size: 2rem;
  font-weight: 900;
  color: var(--white-pure);
  margin-bottom: 0.65rem;
}

.cta-band p {
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 2rem;
}

.cta-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.cta-primary {
  padding: 0.9rem 2.25rem;
  border-radius: 50px;
  border: 2px solid var(--white-pure);
  background: var(--white-pure);
  color: var(--red-dark);
  font-size: 1.02rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.cta-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.cta-secondary {
  display: inline-flex;
  align-items: center;
  padding: 0.9rem 2.25rem;
  border-radius: 50px;
  border: 2px solid rgba(255, 255, 255, 0.7);
  color: var(--white-pure);
  font-size: 1.02rem;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.25s ease, border-color 0.25s ease;
}

.cta-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--white-pure);
}

/* Responsive */
@media (max-width: 900px) {
  .pillars-inner,
  .visit-grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .story-inner {
    grid-template-columns: minmax(0, 1fr);
    gap: 2rem;
  }

  .story-facts {
    position: static;
  }

  .branch-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .hero {
    padding: 3rem 1rem 3rem;
  }

  .hero h1 {
    font-size: 1.85rem;
    max-width: none;
  }

  .lead {
    font-size: 1rem;
  }

  .pillars,
  .story,
  .visit,
  .network {
    padding: 3rem 1rem;
  }

  .story-text h2,
  .visit-head h2,
  .network-head h2,
  .cta-band h2 {
    font-size: 1.6rem;
  }

  .cta-band {
    padding: 3rem 1rem;
  }
}

@media (max-width: 480px) {
  .hero h1 {
    font-size: 1.55rem;
  }

  .pillar,
  .visit-card,
  .story-facts {
    padding: 1.25rem;
  }

  .branch-list {
    grid-template-columns: minmax(0, 1fr);
  }

  .cta-actions {
    flex-direction: column;
  }
}
</style>
