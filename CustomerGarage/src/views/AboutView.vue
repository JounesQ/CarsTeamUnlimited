<template>
  <main class="about">
    <section class="hero">
      <div class="hero-inner">
        <p class="eyebrow">P7 Garahe Gallery</p>
        <h1>More than just a car lot.</h1>
        <p class="lead">
          We are a Butuan-grown team obsessed with matching the right vehicle to the right owner –
          with honesty, transparency, and real after–sales support.
        </p>
      </div>
    </section>

    <section class="grid">
      <div class="card">
        <h2>What we do</h2>
        <p>
          P7 Garahe Gallery is a dedicated buy‑and‑sell hub for carefully inspected vehicles – from
          first‑time buyer cars to business workhorses and family haulers.
        </p>
        <p>
          Every unit goes through visual checks, background verification, and documentation review
          before we put it on display or list it online.
        </p>
      </div>

      <div class="card highlights">
        <h2>Why customers choose us</h2>
        <ul>
          <li><span class="dot"></span> Transparent pricing – no hidden “surprise” fees</li>
          <li><span class="dot"></span> Curated inventory, not random mixed listings</li>
          <li><span class="dot"></span> Friendly, no‑pressure viewing at our showroom</li>
          <li><span class="dot"></span> Assistance with financing options and documents</li>
        </ul>
      </div>
    </section>

    <section class="stats">
      <div class="stat">
        <div class="stat-number">10+</div>
        <div class="stat-label">Trusted Brands</div>
      </div>
      <div class="stat">
        <div class="stat-number">100%</div>
        <div class="stat-label">Transparent Pricing</div>
      </div>
      <div class="stat">
        <div class="stat-number">8AM–7PM</div>
        <div class="stat-label">Showroom hours (Mon–Sun)</div>
      </div>
    </section>

    <section class="journey">
      <h2>Your journey with us</h2>
      <ol class="steps">
        <li>
          <h3>1. Browse</h3>
          <p>Explore available units online or visit the showroom to see them in person.</p>
        </li>
        <li>
          <h3>2. Inspect & test</h3>
          <p>Check the vehicle, ask questions, and (where possible) schedule a test drive.</p>
        </li>
        <li>
          <h3>3. Decide with confidence</h3>
          <p>We walk you through pricing, paperwork, and available payment options.</p>
        </li>
        <li>
          <h3>4. Drive home happy</h3>
          <p>Complete the transaction and bring home a unit that truly fits your needs.</p>
        </li>
      </ol>
    </section>
  </main>
</template>

<style scoped>
.about {
  min-height: 100vh;
  padding: 3rem 0 4rem;
  background: #050505;
  color: #f5f5f5;
}

.hero {
  text-align: center;
  margin-bottom: 3rem;
}

.hero-inner {
  max-width: 720px;
  margin: 0 auto;
  padding: 0 1.5rem;
}
@media (min-width: 769px) {
  .hero-inner { max-width: none; padding: 0 0.5rem; }
}

.eyebrow {
  font-size: 0.85rem;
  letter-spacing: 0.25em;
  text-transform: uppercase;
  color: #d4af37;
  margin-bottom: 0.75rem;
}

.hero h1 {
  font-size: 2.5rem;
  font-weight: 900;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 50%, #ffffff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.lead {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #d0d0d0;
}

.grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
  gap: 2rem;
  max-width: 1100px;
  margin: 0 auto 3rem;
  padding: 0 1.5rem;
}
@media (min-width: 769px) {
  .grid { max-width: none; padding: 0 0.5rem; margin-left: 0; margin-right: 0; }
}

.card {
  padding: 2rem;
  border-radius: 16px;
  background: radial-gradient(circle at top left, rgba(212, 175, 55, 0.12), transparent 55%),
    #0a0a0a;
  border: 1px solid rgba(212, 175, 55, 0.28);
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.7);
}

.card h2 {
  font-size: 1.4rem;
  margin-bottom: 1rem;
  color: #f9f3d5;
}

.card p {
  font-size: 0.98rem;
  line-height: 1.7;
  color: #c0c0c0;
}

.card p + p {
  margin-top: 0.75rem;
}

.highlights ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.highlights li {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.98rem;
  color: #d5d5d5;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #d4af37;
  margin-top: 0.4rem;
  flex-shrink: 0;
}

.stats {
  max-width: 900px;
  margin: 0 auto 3rem;
  padding: 1.5rem 1.5rem 0;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  gap: 1.5rem;
  border-top: 1px solid rgba(212, 175, 55, 0.25);
}
@media (min-width: 769px) {
  .stats { max-width: none; padding: 1.5rem 0.5rem 0; margin-left: 0; margin-right: 0; }
}

.stat {
  text-align: center;
  min-width: 140px;
}

.stat-number {
  font-size: 2.2rem;
  font-weight: 900;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 60%, #ffffff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.9rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #a0a0a0;
}

.journey {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 1.5rem 1rem;
}
@media (min-width: 769px) {
  .journey { max-width: none; padding: 0 0.5rem 1rem; margin-left: 0; margin-right: 0; }
}

.journey h2 {
  font-size: 1.6rem;
  margin-bottom: 1.5rem;
  color: #f9f3d5;
}

.steps {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem 2rem;
}

.steps li {
  padding: 1.25rem 1.5rem;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.18), rgba(0, 0, 0, 0.95));
  border: 1px solid rgba(212, 175, 55, 0.45);
  box-shadow: 0 14px 36px rgba(0, 0, 0, 0.75);
}

.steps h3 {
  font-size: 1.05rem;
  margin-bottom: 0.4rem;
  color: #ffffff;
}

.steps p {
  font-size: 0.95rem;
  color: #e0e0e0;
  line-height: 1.6;
}

@media (max-width: 900px) {
  .grid {
    grid-template-columns: minmax(0, 1fr);
  }

  .steps {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 768px) {
  .about {
    padding: 1.5rem 0.25rem 2rem;
  }

  .hero {
    margin-bottom: 1.5rem;
  }

  .hero-inner {
    padding: 0 0.25rem;
  }

  .hero h1 {
    font-size: 1.75rem;
  }

  .grid {
    padding: 0 0.25rem;
    margin-bottom: 1.5rem;
  }

  .card {
    padding: 1rem 0.75rem;
  }

  .stats {
    padding: 1rem 0.25rem 0;
    margin-bottom: 1.5rem;
  }

  .journey {
    padding: 0 0.25rem 1rem;
  }

  .steps li {
    padding: 0.75rem 0.75rem;
  }
}

@media (max-width: 600px) {
  .about {
    padding-top: 1rem;
  }

  .hero h1 {
    font-size: 1.5rem;
  }

  .lead {
    font-size: 0.95rem;
  }

  .card {
    padding: 0.875rem 0.5rem;
  }

  .steps li {
    padding: 0.6rem 0.5rem;
  }
}
</style>
