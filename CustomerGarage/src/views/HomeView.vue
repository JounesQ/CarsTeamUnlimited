<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <main class="home">
    <div class="hero-section">
      <div class="hero-content">
        <div class="logo-container">
          <img src="/p7-logo.png" alt="P7 Garahe Gallery" class="logo" />
        </div>
        <h1 class="brand-name">P7 GARAHE GALLERY</h1>
        <p class="tagline">WE KNOW YOUR NEEDS</p>
        <div class="cta-buttons">
          <button type="button" class="cta-primary" @click="router.push('/vehicles')">
            <span>Browse Vehicles</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
        </div>
      </div>
      <div class="hero-overlay"></div>
    </div>

    <div class="features-section">
      <div class="features-container">
        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
              <path d="M2 17l10 5 10-5"></path>
              <path d="M2 12l10 5 10-5"></path>
            </svg>
          </div>
          <h3>Premium Selection</h3>
          <p>Curated collection of high-quality vehicles for every need</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <h3>Quality Assured</h3>
          <p>Every vehicle inspected and verified for your peace of mind</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
          </div>
          <h3>Expert Support</h3>
          <p>Professional guidance throughout your buying journey</p>
        </div>
      </div>
    </div>

    <div class="stats-section">
      <div class="stats-container">
        <div class="stat-item">
          <div class="stat-number">500+</div>
          <div class="stat-label">Vehicles Sold</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <div class="stat-number">1000+</div>
          <div class="stat-label">Happy Customers</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
          <div class="stat-number">50+</div>
          <div class="stat-label">Available Now</div>
        </div>
      </div>
    </div>

    <div class="location-section">
      <div class="location-container">
        <div class="location-header">
          <h2 class="section-title">Visit Our Showroom</h2>
          <p class="section-subtitle">Find us at our premium location</p>
        </div>
        <div class="location-content">
          <div class="location-map">
            <!-- Google Maps embed for P7 Garahe Butuan City - WGPJ+VX4 -->
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.379354582659!2d125.52989257375167!3d8.937146290422609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3301c10012edffb1%3A0x48649660d489f595!2sP7%20Garahe%20butuan%20city!5e0!3m2!1sen!2sph!4v1770786166071!5m2!1sen!2sph"
              width="100%"
              height="100%"
              style="border:0;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
          <div class="location-info">
            <div class="info-item">
              <div class="info-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
              <div class="info-content">
                <h3>Address</h3>
                <p>WGPJ+VX4, C.T. Montalban Street<br>Butuan City, Agusan del Norte</p>
              </div>
            </div>
            <div class="info-item">
              <div class="info-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
              </div>
              <div class="info-content">
                <h3>Phone</h3>
                <p>+63 XXX XXX XXXX</p>
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
                <p>Open 24 Hours<br>7 Days a Week</p>
              </div>
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
  background: #000;
}

/* Hero Section */
.hero-section {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
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

.logo-container {
  margin-bottom: 2rem;
  animation: fadeInDown 1s ease-out;
}

.logo {
  width: 180px;
  height: 180px;
  object-fit: contain;
  filter: drop-shadow(0 0 30px rgba(212, 175, 55, 0.5));
}

.brand-name {
  font-size: 3.5rem;
  font-weight: 900;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 50%, #d4af37 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 2px;
  text-shadow: 0 0 40px rgba(212, 175, 55, 0.3);
  animation: fadeInUp 1s ease-out 0.2s both;
}

.tagline {
  font-size: 1.25rem;
  color: #e0e0e0;
  margin-bottom: 3rem;
  font-weight: 300;
  letter-spacing: 1px;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  animation: fadeInUp 1s ease-out 0.6s both;
}

.cta-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2.5rem;
  font-size: 1.1rem;
  font-weight: 600;
  border: 2px solid #d4af37;
  border-radius: 50px;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
  color: #000;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
  letter-spacing: 0.5px;
}

.cta-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(212, 175, 55, 0.6);
}

.cta-primary svg {
  transition: transform 0.3s ease;
}

.cta-primary:hover svg {
  transform: translateX(4px);
}

/* Features Section */
.features-section {
  padding: 5rem 2rem;
  background: #0a0a0a;
}

.features-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  padding: 2.5rem 2rem;
  background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
  border: 1px solid rgba(212, 175, 55, 0.2);
  border-radius: 16px;
  text-align: center;
  transition: all 0.3s ease;
}

.feature-card:hover {
  border-color: rgba(212, 175, 55, 0.5);
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
}

.feature-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.2) 0%, rgba(244, 208, 63, 0.1) 100%);
  border-radius: 50%;
  color: #d4af37;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #d4af37;
  font-weight: 700;
}

.feature-card p {
  font-size: 1rem;
  color: #b0b0b0;
  line-height: 1.6;
}

/* Stats Section */
.stats-section {
  padding: 4rem 2rem;
  background: #000;
  border-top: 1px solid rgba(212, 175, 55, 0.1);
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

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 3rem;
  font-weight: 900;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1rem;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 500;
}

.stat-divider {
  width: 1px;
  height: 60px;
  background: linear-gradient(to bottom, transparent, rgba(212, 175, 55, 0.3), transparent);
}

/* Location Section */
.location-section {
  padding: 5rem 2rem;
  background: #0a0a0a;
  border-top: 1px solid rgba(212, 175, 55, 0.1);
}

.location-container {
  max-width: 1200px;
  margin: 0 auto;
}

.location-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 900;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.75rem;
}

.section-subtitle {
  font-size: 1.1rem;
  color: #888;
  font-weight: 300;
}

.location-content {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 2rem;
  align-items: start;
}

.location-map {
  width: 100%;
  height: 500px;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid rgba(212, 175, 55, 0.2);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
}

.location-map:hover {
  border-color: rgba(212, 175, 55, 0.4);
  box-shadow: 0 15px 50px rgba(212, 175, 55, 0.2);
}

.location-map iframe {
  display: block;
}

.location-info {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-item {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
  border: 1px solid rgba(212, 175, 55, 0.2);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.info-item:hover {
  border-color: rgba(212, 175, 55, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(212, 175, 55, 0.15);
}

.info-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.2) 0%, rgba(244, 208, 63, 0.1) 100%);
  border-radius: 50%;
  color: #d4af37;
}

.info-content h3 {
  font-size: 1.1rem;
  color: #d4af37;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.info-content p {
  font-size: 0.95rem;
  color: #b0b0b0;
  line-height: 1.6;
  margin: 0;
}

/* Animations */
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

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
@media (max-width: 768px) {
  .logo {
    width: 120px;
    height: 120px;
  }

  .brand-name {
    font-size: 2rem;
  }

  .tagline {
    font-size: 1rem;
  }

  .cta-primary {
    padding: 0.875rem 2rem;
    font-size: 1rem;
  }

  .features-container {
    grid-template-columns: 1fr;
  }

  .stat-divider {
    display: none;
  }

  .stat-number {
    font-size: 2.5rem;
  }

  .location-content {
    grid-template-columns: 1fr;
  }

  .location-map {
    height: 350px;
  }

  .section-title {
    font-size: 2rem;
  }

  .section-subtitle {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .brand-name {
    font-size: 1.75rem;
    letter-spacing: 1px;
  }

  .features-section {
    padding: 3rem 1rem;
  }

  .stats-section {
    padding: 3rem 1rem;
  }

  .location-section {
    padding: 3rem 1rem;
  }

  .section-title {
    font-size: 1.75rem;
  }

  .location-map {
    height: 300px;
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
