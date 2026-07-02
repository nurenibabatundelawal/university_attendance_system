</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="../admin/assets/js/script.js"></script>

<?php if (isset($_SESSION['login_success'])) { ?>
<script>
Swal.fire({
  icon:'success', title:'Login Successful',
  text:'Welcome back, <?php echo htmlspecialchars($_SESSION["fullname"]); ?>',
  timer:2000, showConfirmButton:false
});
</script>
<?php unset($_SESSION['login_success']); } ?>

</body>
</html>
