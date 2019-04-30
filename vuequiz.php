<?php

/**
 * @link              dusin.pl
 * @since             1.0.0
 * @package           Vuequiz
 *
 * @wordpress-plugin
 * Plugin Name:       vuequiz
 * Plugin URI:        dusin.pl
 * Description:       Plugin for creating worpdress quiz.
 * Version:           1.0.0
 * Author:            Adrian Dusinkiewicz
 * Author URI:        dusin.pl
 * Text Domain:       vuequiz
 */

//KOD PO STRONIE PANELU ADMINISTRACYJNEGO

//Dodanie nowego typu postów
add_action( 'init', 'create_post_type' );
function create_post_type() {
	register_post_type( 'vue_quiz',
		array(
		'labels' => array(
			'name' => __( 'VueQuiz' ),
			'singular_name' => __( 'VueQuiz' )
		),
		'public' => true,
		'has_archive' => true,
		'menu_position' => 99,
		'menu_icon' => 'dashicons-grid-view',
		'supports' => array('title', 'custom-fields')
		)
	);
}

//Dodanie metaboxu do edycji postu
add_action( 'add_meta_boxes', 'add_your_fields_meta_box' );
function add_your_fields_meta_box() {
	add_meta_box(
		'vue_quiz_meta_box', // $id
		'Questions', // $title
		'show_your_fields_meta_box', // $callback
		'vue_quiz', // $screen
		'normal', // $context
		'high' // $priority
	);
	remove_meta_box( 'submitdiv', 'vue_quiz', 'normal' );
}
function show_your_fields_meta_box() {
	global $post;
	$id = $post->ID;
	wp_enqueue_media();	
	wp_enqueue_style( 'vuequizapp', plugin_dir_url( __FILE__ ) . 'app/admin/css/app.css', false, 1.0, '');
	wp_enqueue_script( 'vuequizapp', plugin_dir_url( __FILE__ ) . 'app/admin/js/app.js', false, 1.0, true);
	print_r("<div id='vue-quiz' data-id='" . $id . "'><div id='app'></div></div>");
}

//Dodanie elemntu wyświetlającego informację o shortcode
add_action( 'edit_form_after_title', 'add_content_before_editor' );
function add_content_before_editor() {
	global $post;
	if($post->post_status != 'auto-draft' && $post->post_type =='vue_quiz') {
		echo '<div style="margin-top: 10px; background: white; padding: 20px;">Kod do umieszczenie quizu na stronie: <strong>[vuequiz id="' . $post->ID . '"]</strong></div>';
	}
    
}

//aktualizacja pól
add_action('wp_ajax_update_fields', 'update_fields');
function update_fields() {

	//pobranie wartości
	$post_id = $_POST['post_id'];
	$post_title = $_POST['post_title'];
	$questions = $_POST['questions'];
	$answers = $_POST['answers'];

	//ustawienie pola odpowiedzi
	$response = '';

	//utworzenie/aktualizacja postu
	$post = get_post($post_id);
	if($post->post_status == 'auto-draft') {
		$response = json_encode(get_edit_post_link($post_id, ''));
		wp_publish_post( $post_id );
		wp_update_post( array(
			'ID'           => $post_id,
			'post_title'   => $post_title,
			'post_name'    => $post_title
		));
	}else {
		$response = json_encode(null);
		wp_update_post( array(
			'ID'           => $post_id,
			'post_title'   => $post_title,
			'post_name'    => $post_title
		));
	}

	//aktualizacja meta pól
	if($questions) {
		update_post_meta($post_id, "questions", serialize($questions));
		update_post_meta($post_id, "answers", serialize($answers));
	} else {
		update_post_meta($post_id, "questions", "");
		update_post_meta($post_id, "answers", "");
	}

	//zwrócenie linku ze stroną edycji postu
	echo $response;
	die();
}

//pobieranie pól
add_action('wp_ajax_get_fields', 'get_fields');
function get_fields() {
	$post_id = $_POST['post_id'];
	if( $post_id) {
		echo wp_json_encode(array(
			'questions' => wp_json_encode(unserialize(get_post_meta($post_id, "questions", true))),
			'answers' => wp_json_encode(unserialize(get_post_meta($post_id, "answers", true)))
		));
	}
	die();
}

//wyłączenie opcji autosave
add_action( 'admin_enqueue_scripts', 'my_admin_enqueue_scripts' );
function my_admin_enqueue_scripts() {
    if ( 'vue_quiz' == get_post_type() ) {
        wp_dequeue_script( 'autosave' );
	}
}

// ukrycie pola z adresem wpisu
add_action( 'admin_head', 'hide_slug_options'  );
function hide_slug_options() {
	global $post;
	global $pagenow;
	if ( $post->post_type =='vue_quiz' ) {
		$hide_slugs = "<style type=\"text/css\">#slugdiv, #edit-slug-box, [for=\"slugdiv-hide\"] { display: none; }</style>\n";
		if (is_admin() && $pagenow=='post-new.php' OR $pagenow=='post.php') print($hide_slugs);
	}
}

//pobieranie obrazka
add_action('wp_ajax_get_image_url', 'get_image_url');
add_action('wp_ajax_nopriv_get_image_url', 'get_image_url');
function get_image_url() {
	$media_id = $_POST['media_id'];
	if( $media_id ) {
		// echo wp_get_attachment_url( $media_id );
		echo wp_get_attachment_image_src( $media_id, 'large' )[0];
	}
	die();
}

//KOD W CZĘŚCI UŻYTKOWNIKA STRONY

//Dodanie shortcode wyświetlającego quiz
function vue_quiz( $atts ) {
	wp_enqueue_media();	
	wp_enqueue_style( 'vuequizapp', plugin_dir_url( __FILE__ ) . 'app/public/css/app.css', false, 1.0, '');
	wp_enqueue_script( 'vuequizapp', plugin_dir_url( __FILE__ ) . 'app/public/js/app.js', false, 1.0, true);
	$atts = shortcode_atts( array( 'id' => 'null' ), $atts, 'vuequiz' );
	return "<div class='vue-quiz' id='vue-quiz_" . $atts['id'] . "' data-id='" . $atts['id'] . "'></div>";
}
add_shortcode('vuequiz', 'vue_quiz');

//pobranie pytań
add_action('wp_ajax_get_questions', 'get_questions');
add_action('wp_ajax_nopriv_get_questions', 'get_questions');
function get_questions() {
	$post_id = $_POST['post_id'];
	if($post_id) {
		echo wp_json_encode(unserialize(get_post_meta($post_id, "questions", true)));
	}
	die();
}

//pobranie odpowiedzi
add_action('wp_ajax_get_answers', 'get_answers');
add_action('wp_ajax_nopriv_get_answers', 'get_answers');
function get_answers() {
	$post_id = $_POST['post_id'];
	if($post_id) {
		echo wp_json_encode(unserialize(get_post_meta($post_id, "answers", true)));
	}
	die();
}