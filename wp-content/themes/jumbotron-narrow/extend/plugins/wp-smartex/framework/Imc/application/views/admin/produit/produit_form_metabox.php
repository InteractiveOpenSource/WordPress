<div>
<table>
     <?php
	foreach($fields as $k => $v) {
     ?>   
    <tr>	
	<th><?php echo Junten::label($k, $v["title"]); ?></th>
	<td><?php echo Junten::input($k, $prefills->$k, $fields[$k]["html"]); ?></td>
    </tr>
    <?php } ?>    
        <tr>
	<th colspan="2"><?php echo $hiddens; ?></th>
    </tr>
</table>
</div>