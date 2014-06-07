<?php

namespace Smartex\Controller;

use Smartex\Model;

class Custom_Post_Type extends \Lic{

    protected $post_arg = array (
        //'register_meta_box_cb' => array($this, "metabox"),
        'labels' => array (
            'name' => 'Produits',
            'singular_name' => 'Produit',
            'add_new' => 'Ajoutez un nouveau produit',
            'add_new_item' => 'Ajoutez un nouveau produit',
            'new_item' => 'Nouveau produit',
            'view_item' => 'Voir produit',
            'all_items' => 'Tous les produits'
        ),
        'public' => true,
        'has_archive' => true,
        'query_var' => 'assurance',
        'capability_type' => 'post',
        'taxonomies' => array (
            "type_produit"
        ),
        'supports' => array (
            'title',
            'editor',
            'thumbnail',
            'comments',
            'post-formats'
        )
    ), $metabox = array(), $label;

    public function __construct($label, $args = array()){

    }

    public function hook_init() {
        register_post_type (
            "assurance",
            array (
                'register_meta_box_cb' => array($this, "metabox"),
                'labels' => array (
                    'name' => 'Produits',
                    'singular_name' => 'Produit',
                    'add_new' => 'Ajoutez un nouveau produit',
                    'add_new_item' => 'Ajoutez un nouveau produit',
                    'new_item' => 'Nouveau produit',
                    'view_item' => 'Voir produit',
                    'all_items' => 'Tous les produits'
                ),
                'public' => true,
                'has_archive' => true,
                'query_var' => 'assurance',
                'capability_type' => 'post',
                'taxonomies' => array (
                    "type_produit"
                ),
                'supports' => array (
                    'title',
                    'editor',
                    'thumbnail',
                    'comments',
                    'post-formats'
                )
            ) );

        register_taxonomy ( "type_produit", "assurance", array (
            'labels' => array (
                'name' => 'Types de produit',
                'singular_name' => 'Type de produit'
            ),
            'description' => 'Type de produit',
            'public' => true,
            'hierarchical' => true
        ) );

        //add_shortcode("imcompare_produit", array($this, "compare"));
    }

    public function hook_save_post() {
        global $post;
        if("assurance" == get_post_type ( $post )) {
            $produit = Custom_Post::factory("assurance", $post);
            $produit->perform_update(array_intersect_key($_POST, $produit->get_fields()), $_FILES);
        }
    }

    public function metabox() {
        global $post;
        //$produit = Lim::factory("assurance", $post);
        //Junten::$map->set("title", "Propriété du produit " . $post->ID);
        //$produit->perform_edit((object)$produit->as_array(), "produit_form_metabox", "metabox");

        //should be called within an internal hook
        $this->after();
    }

    public function compare($shortcode_args) {
        $action = $this->request->action();
        if(!$action)
            $action = "liste";
        return $this->$action();
    }

    public function liste() {
        /*
        $produits = Lim::factory("assurance");
        return Liv::factory("admin/produit/list-table", array(
            "total" => $produits->count_all(),
            "rows" => $produits->find_all()))->render();
        */
    }

    public function filter_compare() {
        /*
		$produits = Lim::factory("assurance");
		$produits->include = $_POST["compare"];
		return Liv::factory("admin/produit/list-table", array(
				"total" => $produits->count_all(),
				"rows" => $produits->find_all()
		))->render();
        */
    }
}