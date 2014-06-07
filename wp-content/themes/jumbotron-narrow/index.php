<?php get_header(); ?>

<?php
    if(is_home()){

    }elseif(is_404()){
        get_template_part('bloc/jumbo-404');
    }
?>

<?php get_footer(); ?>