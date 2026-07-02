<nav class="top-navbar">
<div class="left">
<button id="menu-btn"><i class="fas fa-bars"></i></button>
<h2>Department Dashboard</h2>
</div>
<div class="right">
<div class="notification" onclick="location.href='notifications.php'" style="cursor:pointer;">
<i class="fas fa-bell"></i>
<span><?php echo $unreadNotif; ?></span>
</div>
<div class="profile">
<i class="fas fa-user-circle"></i>
<div>
<strong><?php echo htmlspecialchars($deptAdmin['fullname'] ?? 'Dept Admin'); ?></strong>
<p><?php echo htmlspecialchars($deptAdmin['department_name'] ?? ''); ?></p>
</div>
</div>
</div>
</nav>
