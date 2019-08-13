<?php
      include_once "pagemenuitem.php"
?>

<div class="box">

    <div id="dialog-about" title="About">
        {tr:about_text}
    </div>

    <?php 
    if (AggregateStatistic::enabled()) {
        pagelink('aggregate_statistics');
    }
    ?>
</div>
