<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php wp_title('::') ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo jumbotron_template_uri() ?>/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo jumbotron_template_uri() ?>/css/jumbotron-narrow.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo jumbotron_template_uri() ?>/js/html5shiv.js"></script>
    <script src="<?php echo jumbotron_template_uri() ?>/js/respond.min.js"></script>
    <![endif]-->
    <?php wp_head(); ?>
</head>

<body>

<div class="container">
    <div class="header">
        <ul class="nav nav-pills pull-right">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <?php do_action('jmb_nav_menu'); ?>
        <h3 class="text-muted"><?php echo bloginfo('blogname'); ?></h3>
    </div>