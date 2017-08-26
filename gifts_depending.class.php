<?php
class GiftsDependingPurchase {

    function __construct() {
        add_action( 'init', array($this,'gifts_depending_init') );
        add_action( 'add_meta_boxes', array($this, 'gifts_depending_meta_boxes') );
        add_action( 'save_post', array($this, 'gifts_depending_meta_save') );
        add_action( 'admin_enqueue_scripts', array($this, 'gifts_depending_admin_assets') );
        add_filter( 'manage_gift_posts_columns', array($this, 'gifts_depending_columns_head') );
        add_action( 'manage_gift_posts_custom_column', array($this, 'gifts_depending_columns_content'), 10, 2);
        add_filter( 'manage_edit-gift_sortable_columns', array($this, 'gifts_depending_sortable_column') );
        add_action( 'woocommerce_before_add_to_cart_button', array($this, 'gifts_depending_select_button') );
        add_shortcode( 'gifts_depending_purchase', array($this, 'gifts_depending_shortcode') );

    }

    function gifts_depending_init() {
        register_post_type(
            'gift', array(
                'labels' => array('name' => 'Gifts', 'singular_name' => 'Gift'),
                'public' => TRUE,
                'rewrite' => array( 'slug' => 'gift' ),
                'has_archive' => FALSE,
                'menu_icon' =>  'dashicons-tickets-alt',
                'supports' =>  array( 'title', 'editor', 'author', 'revisions', 'thumbnail')
            )
        );

        wp_enqueue_style( 'gifts_depending_style', plugin_dir_url( __FILE__ ) . 'assets/css/gifts_depending.css', false );
    }

    // Registrar Meta box en los formularios
    function gifts_depending_meta_boxes() {

        add_meta_box( 'gift-info', 'Information', array($this, 'gifts_depending_info_content'), 'gift', 'normal', 'high' );

    }

    // Agregar custom fields al meta box
    function gifts_depending_info_content( $post ) {

        $values = get_post_custom( $post->ID );

        wp_nonce_field( 'gifts_depending_meta_box_nonce', 'meta_box_nonce' );

        // Cuadro desplegable para elegir la región
        $value = isset( $values['gifts_depending_minimum_price'] ) ? esc_attr( $values['gifts_depending_minimum_price'][0] ) : '';
        $output = '<p class="form-field _regular_price_field ">';
        $output .= '<label for="gifts_depending_minimum_price" class="gift-label">Minimum Price</label>';
        $output .= '<input type="number" name="gifts_depending_minimum_price" id="gifts_depending_minimum_price" class="short wc_input_price" value="'.$value.'" />';
        $output .= '</p>';

        echo $output;

    }

    // Guardar datos de los campos personalizados
    function gifts_depending_meta_save( $post_id )
    {
        // Evitar que haga algo cuando esté en guardado automático
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // Verificamos si se envío el nonce
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'gifts_depending_meta_box_nonce' ) ) return;

        // Evitar guardar si no eres el usuario que puede editar
        if( !current_user_can( 'edit_post' ) ) return;

        // Guardamos los datos de región
        if( isset( $_POST['gifts_depending_minimum_price'] ) ) {
            update_post_meta( $post_id, 'gifts_depending_minimum_price', esc_attr( $_POST['gifts_depending_minimum_price'] ) );
        }

    }

    // ADD NEW COLUMN
    function gifts_depending_columns_head($columns) {
        $new = array();
        foreach($columns as $key => $title) {
            if ($key=='title') $new['photo'] = '<span class="dashicons dashicons-format-image"></span>';
            if ($key=='author') $new['minimum_price'] = 'Minimum Price';
            $new[$key] = $title;
        }
        return $new;
    }

    // SHOW THE FEATURED IMAGE
    function gifts_depending_columns_content($column_name, $post_ID) {
        if ($column_name == 'photo') {
            echo "<img src='".get_the_post_thumbnail_url($post_ID, 'thumbnail')."' style='width:100%; height:auto;' />";
        }
        if ($column_name == 'minimum_price') {
            $precio = get_post_meta($post_ID, 'gifts_depending_minimum_price', true);
            if ($precio > 0) {
                echo intval($precio);
            } else {
                echo 0;
            }
        }
    }

    function gifts_depending_sortable_column( $columns ) {
        $columns['minimum_price'] = 'minimum_price';
        return $columns;
    }

    function gifts_depending_select_button() {

        $output = '<div class="gifts_dependin_mb20">';

        global $wpdb;
        $page = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts.' WHERE post_content LIKE "%[gifts_depending_purchase]%" AND post_parent = 0 AND post_type = "page" AND post_status = "publish"');
        if (!empty($page)) {
            $output .= '<a href="'.get_permalink($page[0]).'?prod='.get_the_id().'" class="single_add_to_cart_button button alt">Select gift</a>';
        } else {
            $output .= 'You must publish a page and add this shortcode <strong>[gifts_depending_purchase]</strong>';
        }

        $output .= '<div class="clear"></div>';
        $output .= '</div>';
        echo $output;

    }

    function gifts_depending_shortcode( $atts ){

        $output = '<p>Selecciona el cupón que deseas recibir con tu donación. Recuerda que el monto de tu donación debe ser superior al monto indicado en el cupón.</p>';

        $gifts = new WP_Query(array(
            'post_type'         =>  'gift',
            'posts_per_page'    =>  -1
        ));
        if ($gifts->have_posts()) {
            while ($gifts->have_posts()) { $gifts->the_post();
                $output .= get_the_title();
                $output .= "<hr>";
            }
        } else {
            $output .= "<p>No gifts found</p>";
        }

    	return $output;
    }

}
?>
