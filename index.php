<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UniAttend — University Attendance System</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,800;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <div class="logo-mark">
        <svg fill="none" viewBox="0 0 18 18" stroke="#0B1E3D" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 2L2 6v6c0 3.31 3.13 6 7 6s7-2.69 7-6V6L9 2z"/>
          <path d="M6 9l2 2 4-4"/>
        </svg>
      </div>
      <span class="logo-text">Uni<span>Attend</span></span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
    <div class="nav-links" id="navLinks">
      <a href="index.php" class="active">Home</a>
      <a href="#features">Features</a>
      <a href="#portals">Portals</a>
      <a href="#">Support</a>
    </div>
    <a href="login.php" class="nav-cta">Sign in →</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg-grid"></div>
  <div class="hero-bg-glow1"></div>
  <div class="hero-bg-glow2"></div>
  <div class="hero-inner">

    <!-- Left copy -->
    <div class="hero-copy">
      <div class="hero-pill"><span class="hero-pill-dot"></span>Live attendance monitoring</div>
      <h1>Attendance that<br>runs <em>itself</em></h1>
      <p class="hero-desc">
        Fingerprint and RFID-powered check-ins, real-time dashboards,
        and instant exports — so lecturers never touch a register again.
      </p>
      <div class="hero-btns">
        <a href="login.php?role=lecturer" class="btn-primary">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Lecturer login
        </a>
        <a href="student_login.php" class="btn-secondary">Student login</a>
        <a href="login.php?role=department_admin" class="btn-secondary">Department portal</a>
      </div>
      <div class="hero-trust">
        <div class="hero-trust-avatars">
          <div class="trust-avatar" style="background:#3B6FEF">AO</div>
          <div class="trust-avatar" style="background:#059669">KE</div>
          <div class="trust-avatar" style="background:#D97706">FN</div>
          <div class="trust-avatar" style="background:#7C3AED">JB</div>
        </div>
        <div class="hero-trust-text">Trusted by 5,000+ students across campus</div>
      </div>
    </div>

    <!-- Live dashboard card -->
    <div class="hero-card">
      <div class="hc-header">
        <span class="hc-title">Today's overview</span>
        <div class="hc-live"><span class="hc-live-dot"></span>Live</div>
      </div>
      <div class="hc-stats">
        <div class="hc-stat">
          <div class="hc-stat-n">1,103</div>
          <div class="hc-stat-l">Present</div>
        </div>
        <div class="hc-stat">
          <div class="hc-stat-n">88<span>%</span></div>
          <div class="hc-stat-l">Rate</div>
        </div>
        <div class="hc-stat">
          <div class="hc-stat-n">145</div>
          <div class="hc-stat-l">Absent</div>
        </div>
      </div>
      <div class="hc-bar-label">Weekly attendance trend</div>
      <div class="hc-bars">
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:72%;background:rgba(255,255,255,0.15)"></div><span class="hc-bar-day">M</span></div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:85%;background:rgba(255,255,255,0.2)"></div><span class="hc-bar-day">T</span></div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:91%;background:#3B6FEF"></div><span class="hc-bar-day">W</span></div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:78%;background:rgba(255,255,255,0.15)"></div><span class="hc-bar-day">T</span></div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:88%;background:#F59E0B"></div><span class="hc-bar-day">F</span></div>
        <div class="hc-bar-wrap"><div class="hc-bar" style="height:45%;background:rgba(255,255,255,0.1)"></div><span class="hc-bar-day">S</span></div>
      </div>
      <div class="hc-checkins">
        <div class="hc-checkins-label">Recent check-ins</div>
        <div class="hc-row">
          <div class="hc-avatar" style="background:#3B6FEF">AO</div>
          <div class="hc-row-info">
            <div class="hc-name">Amara Okafor</div>
            <div class="hc-method">Fingerprint · CS301</div>
          </div>
          <div class="hc-time">08:02</div>
        </div>
        <div class="hc-row">
          <div class="hc-avatar" style="background:#059669">KE</div>
          <div class="hc-row-info">
            <div class="hc-name">Kelvin Eze</div>
            <div class="hc-method">RFID · ENG201</div>
          </div>
          <div class="hc-time">08:07</div>
        </div>
        <div class="hc-row">
          <div class="hc-avatar" style="background:#D97706">FN</div>
          <div class="hc-row-info">
            <div class="hc-name">Fatima Nwosu</div>
            <div class="hc-method">Fingerprint · MTH401</div>
          </div>
          <div class="hc-time">08:11</div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- STATS BAND -->
<section class="stats-band">
  <div class="stats-band-inner">
    <div class="stat-cell"><div class="stat-n">5<span class="accent">K+</span></div><div class="stat-l">Students enrolled</div></div>
    <div class="stat-cell"><div class="stat-n">98<span class="accent">%</span></div><div class="stat-l">Scan accuracy</div></div>
    <div class="stat-cell"><div class="stat-n">&lt;3<span class="accent">s</span></div><div class="stat-l">Avg check-in time</div></div>
    <div class="stat-cell"><div class="stat-n">24<span class="accent">/7</span></div><div class="stat-l">Live monitoring</div></div>
  </div>
</section>

<!-- FEATURES -->
<section class="section-features" id="features">
  <div class="section-inner">
    <div class="section-eyebrow">Core capabilities</div>
    <h2 class="section-h">Built for serious<br>academic institutions</h2>
    <p class="section-sub">Three verification methods, one unified platform — purpose-built to eliminate proxy attendance and manual roll calls forever.</p>
    <div class="features-grid">

      <div class="feat-card">
        <div class="feat-icon fi-blue">
          <img src="assets/images/icon-fingerprint.svg" alt="Fingerprint">
        </div>
        <h4>Fingerprint verification</h4>
        <p>Biometric authentication that cannot be spoofed. Every scan is logged with a course tag, timestamp, and lecturer session ID.</p>
        <span class="feat-tag feat-tag-blue">Zero proxy attendance</span>
      </div>

      <div class="feat-card">
        <div class="feat-icon fi-emerald">
          <img src="assets/images/icon-rfid.svg" alt="RFID">
        </div>
        <h4>RFID card scanning</h4>
        <p>Tap-and-go check-in using university ID cards. Works across large lecture halls — contactless, fast, and always reliable.</p>
        <span class="feat-tag feat-tag-emerald">Sub-second response</span>
      </div>

      <div class="feat-card">
        <div class="feat-icon fi-gold">
          <img src="assets/images/icon-reports.svg" alt="Reports">
        </div>
        <h4>Real-time reports</h4>
        <p>Live dashboards for every stakeholder. Export detailed PDF or Excel reports by course, student, date range, or department.</p>
        <span class="feat-tag feat-tag-gold">Instant PDF / Excel export</span>
      </div>

    </div>
  </div>
</section>

<!-- PORTALS -->
<section class="section-portals" id="portals">
  <div class="section-inner">
    <div class="section-eyebrow">Access portals</div>
    <h2 class="section-h">One system,<br>three dedicated portals</h2>
    <p class="section-sub">Every role gets exactly the tools they need — nothing more, nothing less.</p>
    <div class="portals-grid">

      <a href="login.php?role=lecturer" class="portal-card pc-lecturer">
        <div class="portal-badge pb-lecturer">Lecturer</div>
        <div class="portal-icon pi-lecturer">
          <img src="assets/images/icon-lecturer.svg" alt="Lecturer">
        </div>
        <h4>Lecturer</h4>
        <p>Start class sessions, monitor check-ins live, flag absentees, and download per-course attendance sheets instantly.</p>
        <div class="portal-btn pb-btn-lecturer">Lecturer login →</div>
        <div class="portal-perms pc-light-perms">
          <div class="perm-item">
            <div class="perm-check check-blue">✓</div>
            Start &amp; end class sessions
          </div>
          <div class="perm-item">
            <div class="perm-check check-blue">✓</div>
            View real-time class attendance
          </div>
          <div class="perm-item">
            <div class="perm-check check-blue">✓</div>
            Export per-course reports
          </div>
        </div>
      </a>

      <a href="student_login.php" class="portal-card pc-student">
        <div class="portal-badge pb-student">Student</div>
        <div class="portal-icon pi-student">
          <img src="assets/images/icon-student.svg" alt="Student">
        </div>
        <h4>Student</h4>
        <p>Check your personal attendance record, view per-course percentages, and download your full attendance history.</p>
        <div class="portal-btn pb-btn-student">Student login →</div>
        <div class="portal-perms pc-light-perms">
          <div class="perm-item">
            <div class="perm-check check-gold">✓</div>
            View personal attendance record
          </div>
          <div class="perm-item">
            <div class="perm-check check-gold">✓</div>
            Track per-course percentages
          </div>
          <div class="perm-item">
            <div class="perm-check check-gold">✓</div>
            Download attendance history
          </div>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-cta">
  <div class="cta-glow"></div>
  <div class="cta-inner">
    <h2>Ready to modernise your campus?</h2>
    <p>Join thousands of students and staff already running smarter, faster, paper-free attendance.</p>
    <a href="login.php" class="btn-primary">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <path d="M9 12l2 2 4-4"/>
      </svg>
      Get started today
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-logo">Uni<span>Attend</span></div>
    <div class="footer-copy">© <?php echo date('Y'); ?> University Attendance System. All rights reserved.</div>
    <div class="footer-links">
      <a href="#">Privacy</a>
      <a href="#">Terms</a>
      <a href="#">Support</a>
    </div>
  </div>
</footer>

<script>
const toggle = document.getElementById('navToggle');
const links = document.getElementById('navLinks');
if(toggle) toggle.addEventListener('click', () => links.classList.toggle('open'));
</script>
</body>
</html>
