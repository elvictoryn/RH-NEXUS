<?php // ======= Footer compartido ======= ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    window.toast = (msg, icon='success') => {
      Swal.fire({toast:true, position:'top-end', icon, title:msg, showConfirmButton:false, timer:2200, timerProgressBar:true});
    };
  </script>
</body>
</html>
