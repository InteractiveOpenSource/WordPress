<div class="">
    <div class="box box-color box-bordered">
	<!--
    <div class="box-title">
	    <h3>
		<i class="icon-table"></i>
		Produit <?php echo Junten::plural("%s record", $total); ?>
	    </h3>
	</div>
    -->
	<div class="box-content nopadding">
		<form name="frm_imcompare_produit" action="" method="post">
            <div class="row">
                <?php 	foreach($rows as $row) { 
							$p = Lim::factory("assurance", $row->post); ?>
							<div class="span3 ">
								<?php if ($row->post->post_title=="monabanq"){ ?>
								<div class="assurance-liste2">
								<?php }else{ ?>
								<div class="assurance-liste">
								<?php } ?>
			
									<div class="titre">
										<h5><?php echo $row->post->post_title; ?>
                                        	
                                        </h5>
									</div>
								   
									<?php if(has_post_thumbnail($row->post->ID)) { ?>
										<?php
										$thumb = get_post_thumbnail_id($row->post->ID);
										$img_url = wp_get_attachment_url( $thumb,'full'); //get img URL
										$image = aq_resize( $img_url, 780, 377, true ); //resize & crop img
										?>
										<figure class="featured-thumbnail large">
											<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><img src="<?php echo $image ?>" alt="<?php the_title(); ?>" /></a>
										</figure>
										<div class="clear"></div>
									<?php } ?>
										
                                   <!-- LOGO ANNONCEUR -->     
                                    <?php   
										 if(@$p->get_related('annonceur', 'post/ID')!=NULL){	
											$tamp_id =  $p->get_related('annonceur', 'post/ID'); 
											$tamp_annonceur =  $p->get_related('annonceur', 'post/post_title'); 
											$tamp_thumbnail =  Helper_Front::get_post_thumb($p->get_related('annonceur', 'post/ID')); 
											if($tamp_id!=""){ 
									?>
                                                <a href="<?php echo  get_post_permalink(  $tamp_id  ); ?> " style="margin: 0 auto; display: block; width: auto; height: 50px; text-align: center;"><img style=" height:50px;" src="<?php echo $tamp_thumbnail; ?>" alt="<?php echo $tamp_annonceur; ?>" title="<?php echo $tamp_annonceur; ?>"></a>
									<?php 
											} 
										 }
									?>    
                                    <!-- FIN LOGO ANNONCEUR --> 
                                    
                                         				  
									<?php if(function_exists('the_ratings')) { echo expand_ratings_template('<span class="rating">%RATINGS_IMAGES%</span>', $row->post->ID); } ?>
									<div class="desc-assurance2">
										<ul>
                                            <?php
                                            $liste =  $row->get_fields();
                                            foreach($liste as $key => $row2){
                                                if(($row2['title']!="Contrat")&&($row2['title']!="Annonceur")&&($row2['title']!="Prix")){
                                                    if($row2['title']!=""){
                                                        echo "<li>".$row2['title'].": ".$p->getValue($key)."</li>";
                                                    }
                                                }
                                            }
                                            ?>
										</ul>
										<div class="prime-ch">
											<?php if($row->getValue("prix")!=''): ?><?php echo $row->getValue("prix"); ?><sup>&euro;</sup><?php endif; ?>
										</div>
										<div class="clr"></div>
			
									</div>
								</div>
								<a href="<?php echo get_permalink($row->post->ID) ?>" class="btn btn-primary marg-auto"><?php echo theme_locals("read_more"); ?></a>

                                    <?php if(@$p->get_related('annonceur', 'post/ID')!=NULL){ ?>
                                        <?php $tamp_id =  $p->get_related('contrat', 'post/ID'); ?>
                                        <div class="contrat-link">
                                            <a href="<?php echo  get_post_permalink(  $tamp_id  ); ?> ">Contrat</a>
                                        </div>

                                    <?php } ?>

                                    <!-- BOUTON CONTRAT -->

                                    <!-- FIN BOUTON CONTRAT  -->
                            </div>
                <?php } ?>
                
                
               				 
            </div>

		</form>	    
	</div>
    </div>
</div>