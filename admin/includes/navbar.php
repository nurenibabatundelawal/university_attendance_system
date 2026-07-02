<nav class="top-navbar">
<div class="left">
<button id="menu-btn"><i class="fas fa-bars"></i></button>
<h2>University Attendance System</h2>
</div>
<div class="right">
<div class="search-box">
<input type="text" placeholder="Search..." id="globalSearch">
<i class="fas fa-search"></i>
</div>
<div class="notification">
<i class="fas fa-bell"></i>
<span>0</span>
</div>
<div class="profile">
<i class="fas fa-user-circle"></i>
<div>
<strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></strong>
<p>Administrator</p>
</div>
</div>
</div>
</nav>
