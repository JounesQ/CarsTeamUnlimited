<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/api/client'

const router = useRouter()
const availableCount = ref<number | null>(null)

// Address strings are the single source of truth: both the embedded map and the
// Waze deep link are derived from them, so editing an address updates both.
const showrooms = [
  {
    id: 1,
    name: 'Showroom 1',
    area: 'General Trias',
    address: 'Phase 2 Blk 3 Lot 10, Meridian Place, Pasong Kawayan 2, General Trias, Cavite',
  },
  {
    id: 2,
    name: 'Showroom 2',
    area: 'General Trias',
    address: 'Lot 233 Along Governor Ferrer Drive, Pasong Kawayan 2, General Trias, 4107 Cavite',
  },
  {
    id: 3,
    name: 'Showroom 3',
    area: 'General Trias',
    address: 'Tirona Santiago Arnaldo Highway, General Trias, Cavite',
  },
  {
    id: 4,
    name: 'Showroom 4',
    area: 'Silang',
    address: 'Tubuan 2 Bypass, Silang, Cavite',
  },
] as const

const contact = {
  phones: [
    { number: '0917 307 5796', network: 'Globe' },
    { number: '0961 741 8001', network: 'Smart' },
    { number: '0917 142 4777', network: 'Globe' },
  ],
  facebook: 'https://www.facebook.com/profile.php?id=61565270293988',
  messenger: 'https://m.me/CarsTeamUnlimited',
}

const activeShowroomId = ref<number>(showrooms[0].id)
const activeShowroom = computed(
  () => showrooms.find((s) => s.id === activeShowroomId.value) ?? showrooms[0],
)

const mapSrc = computed(
  () =>
    `https://maps.google.com/maps?q=${encodeURIComponent(activeShowroom.value.address)}&t=&z=15&ie=UTF8&iwloc=&output=embed`,
)

const wazeUrl = (address: string) =>
  `https://waze.com/ul?q=${encodeURIComponent(address)}&navigate=yes`

const showroomsSection = ref<HTMLElement | null>(null)

const scrollToShowrooms = () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  showroomsSection.value?.scrollIntoView({
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
    block: 'start',
  })
}

onMounted(async () => {
  try {
    const res = await api.getVehicles({ per_page: 1, status: 'available' })
    availableCount.value = res.total
  } catch {
    availableCount.value = null
  }
})
</script>

<template>
  <main class="home">
    <div class="hero-section">
      <div class="hero-content">
        <div class="hero-badge">
          <img
            src="/CTUF%20logo.jpg"
            alt="Cars Team Unlimited — Ride Your Dreams. Buy, Sell, Financing, Trade-In."
            class="hero-badge-img"
            width="1024"
            height="1024"
          />
        </div>
        <p class="hero-eyebrow">Reliable &middot; Financing &middot; Trade-In</p>
        <h1 class="brand-name red-outline-lg">CARS TEAM UNLIMITED FINANCING</h1>
        <p class="tagline">Your next dream car is here</p>
        <ul class="hero-badges">
          <li><strong>Negotiable</strong><span>Upon viewing</span></li>
          <li><strong>Test Drive</strong><span>Available now</span></li>
          <li><strong>Easy Approval</strong><span>As fast as 1 day</span></li>
        </ul>
        <div class="cta-buttons">
          <button type="button" class="cta-primary" @click="router.push('/vehicles')">
            <span>Browse Vehicles</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
          <a class="cta-secondary" href="#showrooms" @click.prevent="scrollToShowrooms">
            Find a showroom
          </a>
        </div>
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-checker"></div>
    </div>

    <div class="features-section">
      <div class="features-container">
        <div class="section-flag-wrap">
          <span class="section-flag">We Offer</span>
        </div>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
              </svg>
            </div>
            <h3>Wide Selection</h3>
            <p>A deep inventory of quality, road-ready units across every body type.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="9" y1="15" x2="15" y2="15"></line>
              </svg>
            </div>
            <h3>Financing</h3>
            <p>In-house and bank financing options with approval in as fast as one day.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <polyline points="9 12 11 14 15 10"></polyline>
              </svg>
            </div>
            <h3>Trusted Dealer</h3>
            <p>Every unit is inspected and document-verified before it hits the floor.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
              </svg>
            </div>
            <h3>Competitive Prices</h3>
            <p>Transparent pricing that stays negotiable once you see the unit in person.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="17 1 21 5 17 9"></polyline>
                <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                <polyline points="7 23 3 19 7 15"></polyline>
                <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
              </svg>
            </div>
            <h3>Trade-In Accepted</h3>
            <p>Bring your current vehicle and put its value straight into your next one.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="stats-section">
      <div class="stats-container">
        <div class="stat-item">
          <div class="stat-number">4</div>
          <div class="stat-label">Showrooms in Cavite</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <div class="stat-number">100%</div>
          <div class="stat-label">Transparent Pricing</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <div class="stat-number">{{ availableCount != null ? availableCount : '—' }}</div>
          <div class="stat-label">Available Now</div>
        </div>
      </div>
    </div>

    <div id="showrooms" ref="showroomsSection" class="location-section">
      <div class="location-container">
        <div class="location-header">
          <span class="section-flag">Visit Us</span>
          <h2 class="section-title">Four Showrooms, One Team</h2>
          <p class="section-subtitle">
            Drop by any branch for a walkthrough and a test drive — pick a location to see it on the map.
          </p>
        </div>

        <div class="showroom-layout">
          <div class="showroom-list">
            <div
              v-for="showroom in showrooms"
              :key="showroom.id"
              class="showroom-card"
              :class="{ active: showroom.id === activeShowroomId }"
            >
              <button
                type="button"
                class="showroom-select"
                :aria-pressed="showroom.id === activeShowroomId"
                @click="activeShowroomId = showroom.id"
              >
                <span class="showroom-index">{{ String(showroom.id).padStart(2, '0') }}</span>
                <span class="showroom-body">
                  <span class="showroom-name">
                    {{ showroom.name }}
                    <span class="showroom-area">{{ showroom.area }}</span>
                  </span>
                  <span class="showroom-address">{{ showroom.address }}</span>
                </span>
              </button>
              <a
                class="showroom-waze"
                :href="wazeUrl(showroom.address)"
                target="_blank"
                rel="noopener noreferrer"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                </svg>
                <span>Waze</span>
              </a>
            </div>
          </div>

          <div class="showroom-map">
            <Transition name="map-fade">
              <iframe
                :key="activeShowroom.id"
                :src="mapSrc"
                :title="`Map of ${activeShowroom.name}`"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            </Transition>
          </div>
        </div>

        <div class="contact-grid">
          <div class="info-item">
            <div class="info-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
            </div>
            <div class="info-content">
              <h3>Contact Us</h3>
              <p v-for="phone in contact.phones" :key="phone.number" class="phone-line">
                <a class="phone-number" :href="`tel:${phone.number.replace(/\s/g, '')}`">{{ phone.number }}</a>
                <span class="phone-network">{{ phone.network }}</span>
              </p>
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
            <div class="info-content">
              <h3>Follow Our Page</h3>
              <p>Message us for unit availability and financing computations.</p>
              <div class="social-buttons">
                <a :href="contact.facebook" target="_blank" rel="noopener noreferrer" class="social-link">
                  <span>Facebook</span>
                </a>
                <a :href="contact.messenger" target="_blank" rel="noopener noreferrer" class="social-link">
                  <span>Messenger</span>
                </a>
              </div>
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <div class="info-content">
              <h3>Business Hours</h3>
              <p>8:00 AM – 6:00 PM<br />Monday to Sunday</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.home {
  min-height: 100vh;
  background: var(--color-background);
  transition: background-color 0.3s ease;
}

/* Hero Section */
.hero-section {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gradient-hero);
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 50% 50%, rgba(216, 31, 38, 0.1) 0%, transparent 70%);
  pointer-events: none;
}

/* Checkered flag strip, echoing the CTU badge */
.hero-checker {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 14px;
  background-image:
    linear-gradient(45deg, var(--checker-square) 25%, transparent 25%, transparent 75%, var(--checker-square) 75%),
    linear-gradient(45deg, var(--checker-square) 25%, transparent 25%, transparent 75%, var(--checker-square) 75%);
  background-size: 14px 14px;
  background-position: 0 0, 7px 7px;
  background-color: var(--checker-base);
  opacity: var(--checker-opacity);
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}
@media (min-width: 769px) {
  .hero-content { max-width: none; padding: 1rem 0.5rem; }
}

/* Brand badge */
.hero-badge {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
  animation: fadeInUp 1s ease-out both;
}

.hero-badge-img {
  width: 100%;
  max-width: 340px;
  height: auto;
  object-fit: contain;
  background: transparent;
  box-shadow: 0 0 60px rgba(216, 31, 38, 0.35);
  transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.4s ease;
  animation: badgeFloat 6s ease-in-out 1s infinite;
}

.hero-badge-img:hover {
  transform: scale(1.04);
  box-shadow: 0 0 80px rgba(216, 31, 38, 0.55);
}

@keyframes badgeFloat {
  0%, 100% {
    translate: 0 0;
  }
  50% {
    translate: 0 -10px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-badge,
  .hero-badge-img {
    animation: none;
  }
}

.hero-eyebrow {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--red-primary);
  -webkit-text-fill-color: var(--red-primary);
  -webkit-text-stroke: 0.04em var(--white-pure);
  paint-order: stroke fill;
  margin-bottom: 0.75rem;
  animation: fadeInUp 1s ease-out 0.1s both;
}

.brand-name {
  font-size: clamp(1.05rem, 4.6vw, 3.5rem);
  font-weight: 900;
  margin: 0 auto 0.75rem;
  line-height: 1;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  text-shadow: none;
  white-space: nowrap;
  animation: fadeInUp 1s ease-out 0.2s both;
}

.tagline {
  font-size: 1.35rem;
  color: var(--color-text);
  margin-bottom: 2rem;
  font-weight: 300;
  letter-spacing: 1px;
  text-transform: uppercase;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.hero-badges {
  list-style: none;
  padding: 0;
  margin: 0 0 2.5rem;
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1rem 2.5rem;
  animation: fadeInUp 1s ease-out 0.5s both;
}

.hero-badges li {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  padding: 0 1.25rem;
}

.hero-badges li + li::before {
  content: '';
  position: absolute;
  left: -1.25rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1px;
  height: 28px;
  background: rgba(216, 31, 38, 0.45);
}

.hero-badges strong {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-heading);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.hero-badges span {
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  animation: fadeInUp 1s ease-out 0.6s both;
}

.cta-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2.5rem;
  font-size: 1.1rem;
  font-weight: 700;
  border: 2px solid var(--red-primary);
  border-radius: 50px;
  background: linear-gradient(135deg, #d81f26 0%, #f0353d 100%);
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(216, 31, 38, 0.45);
  letter-spacing: 0.5px;
}

.cta-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(216, 31, 38, 0.65);
}

.cta-primary svg {
  transition: transform 0.3s ease;
}

.cta-primary:hover svg {
  transform: translateX(4px);
}

.cta-secondary {
  display: inline-flex;
  align-items: center;
  padding: 1rem 2.25rem;
  font-size: 1.05rem;
  font-weight: 600;
  border: 2px solid var(--color-border);
  border-radius: 50px;
  background: transparent;
  color: var(--color-heading);
  text-decoration: none;
  transition: all 0.3s ease;
  letter-spacing: 0.5px;
}

.cta-secondary:hover {
  border-color: var(--red-primary);
  background: rgba(216, 31, 38, 0.12);
}

/* Section chrome */
.section-flag {
  display: inline-block;
  padding: 0.35rem 1.25rem;
  background: var(--red-primary);
  color: var(--white-pure);
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 3px;
  text-transform: uppercase;
  border-radius: 4px;
}

.section-flag-wrap {
  text-align: center;
  margin-bottom: 2.5rem;
}

/* Features Section */
.features-section {
  padding: 5rem 2rem;
  background: var(--color-background-soft);
  transition: background-color 0.3s ease;
}
@media (min-width: 769px) {
  .features-section { padding: 5rem 0.5rem; }
}

.features-container {
  max-width: 1200px;
  margin: 0 auto;
}
@media (min-width: 769px) {
  .features-container { max-width: none; }
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1.25rem;
}

.feature-card {
  padding: 2rem 1.5rem;
  background: var(--gradient-card);
  border: 1px solid rgba(216, 31, 38, 0.22);
  border-radius: 16px;
  text-align: center;
  transition: all 0.3s ease;
}

.feature-card:hover {
  border-color: rgba(216, 31, 38, 0.55);
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(216, 31, 38, 0.22);
}

.feature-icon {
  width: 58px;
  height: 58px;
  margin: 0 auto 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(216, 31, 38, 0.22) 0%, rgba(240, 53, 61, 0.1) 100%);
  border-radius: 50%;
  color: var(--color-accent-text);
}

.feature-card h3 {
  font-size: 1.15rem;
  margin-bottom: 0.65rem;
  color: var(--color-heading);
  font-weight: 700;
}

.feature-card p {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  line-height: 1.6;
}

/* Stats Section */
.stats-section {
  padding: 4rem 2rem;
  background: var(--color-background);
  border-top: 1px solid rgba(216, 31, 38, 0.15);
  transition: background-color 0.3s ease;
}
@media (min-width: 769px) {
  .stats-section { padding: 4rem 0.5rem; }
}

.stats-container {
  max-width: 1000px;
  margin: 0 auto;
  display: flex;
  justify-content: space-around;
  align-items: center;
  flex-wrap: wrap;
  gap: 2rem;
}
@media (min-width: 769px) {
  .stats-container { max-width: none; }
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 3rem;
  font-weight: 900;
  color: var(--red-primary);
  -webkit-text-fill-color: var(--red-primary);
  -webkit-text-stroke: 0.04em var(--white-pure);
  paint-order: stroke fill;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 500;
}

.stat-divider {
  width: 1px;
  height: 60px;
  background: linear-gradient(to bottom, transparent, rgba(216, 31, 38, 0.45), transparent);
}

/* Location Section */
.location-section {
  padding: 5rem 2rem;
  background: var(--color-background-soft);
  border-top: 1px solid rgba(216, 31, 38, 0.15);
  scroll-margin-top: 80px;
  transition: background-color 0.3s ease;
}
@media (min-width: 769px) {
  .location-section { padding: 5rem 0.5rem; }
}

.location-container {
  max-width: 1200px;
  margin: 0 auto;
}
@media (min-width: 769px) {
  .location-container { max-width: none; }
}

.location-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 900;
  color: var(--color-heading);
  margin: 1rem 0 0.75rem;
}

.section-subtitle {
  font-size: 1.05rem;
  color: var(--color-text-muted);
  font-weight: 300;
  max-width: 620px;
  margin: 0 auto;
}

/* Showrooms */
.showroom-layout {
  display: grid;
  grid-template-columns: 1fr 1.15fr;
  gap: 2rem;
  align-items: start;
}

.showroom-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.showroom-card {
  position: relative;
  display: flex;
  align-items: stretch;
  gap: 0.75rem;
  background: var(--gradient-card);
  border: 1px solid var(--color-card-border);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.showroom-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: transparent;
  transition: background 0.3s ease;
}

.showroom-card:hover {
  border-color: rgba(216, 31, 38, 0.45);
  transform: translateX(4px);
}

.showroom-card.active {
  border-color: var(--red-primary);
  box-shadow: 0 6px 24px rgba(216, 31, 38, 0.25);
}

.showroom-card.active::before {
  background: var(--red-primary);
}

.showroom-select {
  flex: 1;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1rem 1.25rem 1.5rem;
  background: transparent;
  border: 0;
  text-align: left;
  cursor: pointer;
  font: inherit;
  color: inherit;
}

.showroom-index {
  font-size: 1.5rem;
  font-weight: 900;
  color: rgba(216, 31, 38, 0.55);
  line-height: 1;
  flex-shrink: 0;
}

.showroom-card.active .showroom-index {
  color: var(--red-primary);
}

.showroom-body {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  min-width: 0;
}

.showroom-name {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-heading);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.showroom-area {
  padding: 0.15rem 0.55rem;
  border-radius: 999px;
  background: rgba(216, 31, 38, 0.15);
  border: 1px solid rgba(216, 31, 38, 0.35);
  color: var(--color-accent-text);
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: none;
}

.showroom-address {
  font-size: 0.88rem;
  color: var(--color-text-muted);
  line-height: 1.5;
}

.showroom-waze {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  flex-shrink: 0;
  width: 76px;
  padding: 0 0.5rem;
  border-left: 1px solid var(--color-card-border);
  background: var(--color-accent-soft);
  color: var(--color-text-muted);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.25s ease;
}

.showroom-waze:hover {
  background: blue;
  color: var(--white-pure);
}

.showroom-map {
  width: 100%;
  height: 430px;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid rgba(216, 31, 38, 0.25);
  box-shadow: var(--shadow-lg);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  position: sticky;
  top: 100px;
  background: var(--color-background-mute);
}

.showroom-map:hover {
  border-color: rgba(216, 31, 38, 0.5);
  box-shadow: 0 15px 50px rgba(216, 31, 38, 0.22);
}

/* Both frames are stacked so the outgoing map can cross-fade into the new one */
.showroom-map iframe {
  display: block;
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
}

.map-fade-enter-active,
.map-fade-leave-active {
  transition: opacity 0.45s ease;
}

.map-fade-enter-from,
.map-fade-leave-to {
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .map-fade-enter-active,
  .map-fade-leave-active {
    transition: none;
  }
}

/* Contact cards */
.contact-grid {
  margin-top: 3rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.info-item {
  display: flex;
  gap: 1.25rem;
  padding: 1.5rem;
  background: var(--gradient-card);
  border: 1px solid rgba(216, 31, 38, 0.22);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.info-item:hover {
  border-color: rgba(216, 31, 38, 0.55);
  transform: translateY(-4px);
  box-shadow: 0 5px 20px rgba(216, 31, 38, 0.18);
}

.info-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(216, 31, 38, 0.22) 0%, rgba(240, 53, 61, 0.1) 100%);
  border-radius: 50%;
  color: var(--color-accent-text);
}

.info-content h3 {
  font-size: 1.1rem;
  color: var(--color-heading);
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.info-content p {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  line-height: 1.6;
  margin: 0;
}

.phone-line {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.phone-number {
  color: var(--yellow-accent);
  font-weight: 800;
  letter-spacing: 1px;
  text-decoration: none;
}

.phone-number:hover {
  color: #ffe566;
}

.phone-network {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.social-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 0.85rem;
}

.social-link {
  padding: 0.35rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(216, 31, 38, 0.5);
  color: var(--color-accent-text);
  font-size: 0.85rem;
  text-decoration: none;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  background: rgba(216, 31, 38, 0.08);
  transition: all 0.3s ease;
}

.social-link:hover {
  border-color: var(--red-primary);
  background: var(--red-primary);
  color: var(--white-pure);
  box-shadow: 0 4px 16px rgba(216, 31, 38, 0.45);
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 900px) {
  .showroom-layout {
    grid-template-columns: 1fr;
  }

  .showroom-map {
    position: relative;
    top: auto;
    height: 400px;
  }
}

@media (max-width: 768px) {
  .hero-section {
    min-height: auto;
    align-items: flex-start;
    padding-top: 0.5rem;
    padding-bottom: 1rem;
  }

  .hero-content {
    padding: 2rem 0.25rem 8rem;
  }

  .hero-badge {
    margin-bottom: 1.25rem;
  }

  .hero-badge-img {
    max-width: 200px;
  }

  .hero-eyebrow {
    font-size: 0.55rem;
  }

  .brand-name {
    letter-spacing: 0.03em;
  }

  .tagline {
    font-size: 0.65rem;
  }

  .hero-badges {
    gap: 0.75rem 1.25rem;
  }

  .hero-badges li + li::before {
    display: none;
  }

  .cta-primary,
  .cta-secondary {
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
  }

  .features-section {
    padding: 3rem 0.25rem;
  }

  .features-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .feature-card {
    padding: 1.5rem 0.75rem;
  }

  .stats-section {
    padding: 2.5rem 0.25rem;
  }

  .stat-number {
    font-size: 2.25rem;
  }

  .stat-divider {
    display: none;
  }

  .location-section {
    padding: 3rem 0.25rem;
  }

  .showroom-map {
    height: 350px;
  }

  .section-title {
    font-size: 1.75rem;
  }

  .section-subtitle {
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) {
  .hero-badge-img {
    max-width: 160px;
  }

  .brand-name {
    letter-spacing: 0.02em;
  }

  .features-section {
    padding: 2rem 0.25rem;
  }

  .stats-section {
    padding: 2rem 0.25rem;
  }

  .location-section {
    padding: 2rem 0.25rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .showroom-map {
    height: 300px;
  }

  .showroom-select {
    padding: 1rem 0.75rem 1rem 1.25rem;
    gap: 0.75rem;
  }

  .showroom-waze {
    width: 62px;
  }

  .info-item {
    padding: 1.25rem;
    gap: 1rem;
  }

  .info-icon {
    width: 40px;
    height: 40px;
  }

  .info-icon svg {
    width: 20px;
    height: 20px;
  }
}
</style>
