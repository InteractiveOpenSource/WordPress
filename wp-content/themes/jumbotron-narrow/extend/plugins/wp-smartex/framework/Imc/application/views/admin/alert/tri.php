<?php  if(count($messages)>0) { ?>
    <?php
    foreach($messages as $type => $armessage) {
        foreach($armessage as $message) {
            ?>
            <div class="alert">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <?php echo $message; ?>
            </div>
        <?php } } ?>
<?php } ?>