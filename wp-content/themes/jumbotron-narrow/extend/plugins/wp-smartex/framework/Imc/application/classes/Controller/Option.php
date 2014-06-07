<?php
class Controller_Option extends Lic {
	public function hook_admin_menu() {
		add_options_page ( "smartex-sample", "Smartex Options", "manage_options", "smartex", array (
				$this,
				"page" 
		) );
	}
	public function page() {
		$action = $this->request->action();
		if(!$action)
			$action = "liste";
		$this->$action();
	}
	public function edition() {
        $opts = array();
        for($i = 0; $i < count($_POST["title"]); $i++) {
            if($_POST["title"][$i] != "") {
                $opts[str_replace("-", "_", sanitize_title($_POST["title"][$i]))] = array(
                    "title" => $_POST["title"][$i],
                    "type" => isset($_POST["type"][$i]) ? $_POST["type"][$i] : 'text'
                );
            }
        }

        array_walk($opts, function(&$item, $key){
            $item['slug'] = $key;
            $item = Model_Field::factory(ucfirst($item['title']), $item['type'], $item);
        });
		update_option("smartex_produit", $opts);

		$this->liste();
	}
	public function liste() {
		$opts = get_option ( "smartex_produit", Model_Field::factory('Prix', 'numeric') );

        array_walk($opts, function(&$item, $key){
            $item = Model_Field::option_adapter($item, $key);
        });

		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
            var render_message = function(message, context){
                if(typeof context == 'undefined') context = $('#message-container');
                var htm = '';
                htm += '<div id="msgbox" class="update-nag">';
                htm += message;
                htm += '</div>';
                var msg = $(htm).appendTo(context);
                setTimeout(function(){
                    msg.fadeOut(1000, function(){
                        $(this).remove();
                    });
                }, 5000)
            };

			$(".rem").on("click", function(){
                var title = $(this).data('title');
				$(this).parent().parent().remove();
                render_message('Le champ <b>' + title + '</b> est en cours de suppression. Cliquer Enregistrer pour valider la suppression');
			});
		});
		</script>
        <div id="message-container" style="padding: 20px 0"></div>
		<form name="frm_smartex_produit" method="post" action="">
			<table width="100%" class="wp-list-table widefat fixed posts">
				<tr>
					<th>Champs</th>
					<th>Type</th>
					<th></th>
				</tr>
			<?php foreach ($opts as $k => $opt) {
					if($k == "prix")
						continue;
				?>
		  		<tr>
		  			<td><?php echo $opt->title; ?></td>
		  			<td><?php echo Helper_Form::select('type[]', Model_Field::$map_type, $opt->type) ?></td>
		  			<td>
                        <input type="hidden" name="title[]" value="<?php echo $opt->title; ?>"/>
                        <input type="button" name="rem" class="rem" data-title="<?php echo $opt->title; ?>" value="supprimer"/>
                    </td>
				</tr>
			<?php } ?>
				<tr>
					<td><input type="text" name="title[]"/></td>
                    <td><?php echo Helper_Form::select('type[]', Model_Field::$map_type, null) ?></td>
					<td>
						<input type="hidden" name="action" value="edition"/>
						<input type="submit" name="submit" value="Enregistrer"/>
					</td>
				</tr>
			</table>
		</form>
		<?php
	}
	
	public function migrate() {
		$olds = get_option("smartex_produit");
		$opts = array();
		foreach($olds as $kold => $vold) {
			$opts[str_replace("-", "_", $kold)] = $vold;
		}
		update_option("smartex_produit", $opts);
		$this->liste();
	}
}