<?php

function statCard($title,$number,$icon,$color){

?>

<div class="card">

    <div class="card-icon <?php echo $color; ?>">

        <i class="fas <?php echo $icon; ?>"></i>

    </div>

    <div class="card-info">

        <h2><?php echo $number; ?></h2>

        <p><?php echo $title; ?></p>

    </div>

</div>

<?php

}

?>