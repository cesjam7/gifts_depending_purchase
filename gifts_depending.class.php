<?php
class GiftsDependingPurchase {

    function __construct() {
        add_action( 'init', array($this,'gifts_depending_init') );
        add_action( 'add_meta_boxes', array($this, 'gifts_depending_meta_boxes') );
        add_action( 'save_post', array($this, 'gifts_depending_meta_save') );
        add_filter('manage_gift_posts_columns', array($this, 'gifts_depending_columns_head') );
        add_action('manage_gift_posts_custom_column', array($this, 'gifts_depending_columns_content'), 10, 2);
        add_filter( 'manage_edit-gift_sortable_columns', array($this, 'my_sortable_gift_column') );
    }

    function gifts_depending_init() {
        register_post_type(
            'gift', array(
                'labels' => array('name' => 'Gifts', 'singular_name' => 'Gift'),
                'public' => TRUE,
                'rewrite' => array( 'slug' => 'gift' ),
                'has_archive' => TRUE,
                'menu_icon' =>  'dashicons-tickets-alt',
                'supports' =>  array( 'title', 'editor', 'author', 'revisions', 'thumbnail')
            )
        );
    }

    // Registrar Meta box en los formularios
    function gifts_depending_meta_boxes() {

        add_meta_box( 'gift-info', 'Información del Cupón', array($this, 'gifts_depending_info_content'), 'gift', 'normal', 'high' );

    }

    // Agregar custom fields al meta box
    function gifts_depending_info_content( $post ) {

        $values = get_post_custom( $post->ID );

        wp_nonce_field( 'gifts_depending_meta_box_nonce', 'meta_box_nonce' );

        // Cuadro desplegable para elegir la región
        $value = isset( $values['gifts_depending_precio_minimo'] ) ? esc_attr( $values['gifts_depending_precio_minimo'][0] ) : '';
        $output = '<p class="form-field _regular_price_field ">';
        $output .= '<label for="gifts_depending_precio_minimo" class="gift-label">Precio Mínimo (S/.)</label>';
        $output .= '<input type="number" name="gifts_depending_precio_minimo" id="gifts_depending_precio_minimo" class="short wc_input_price" value="'.$value.'" />';
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
        if( isset( $_POST['gifts_depending_precio_minimo'] ) ) {
            update_post_meta( $post_id, 'gifts_depending_precio_minimo', esc_attr( $_POST['gifts_depending_precio_minimo'] ) );
        }

    }

    // ADD NEW COLUMN
    function gifts_depending_columns_head($columns) {
        $new = array();
        foreach($columns as $key => $title) {
            if ($key=='title') $new['foto'] = '<span class="dashicons dashicons-format-image"></span>';
            if ($key=='author') $new['precio_minimo'] = 'Precio Mínimo';
            $new[$key] = $title;
        }
        return $new;
    }

    // SHOW THE FEATURED IMAGE
    function gifts_depending_columns_content($column_name, $post_ID) {
        if ($column_name == 'foto') {
            echo "<img src='".get_the_post_thumbnail_url($post_ID, 'thumbnail')."' style='width:100%; height:auto;' />";
        }
        if ($column_name == 'precio_minimo') {
            $precio = get_post_meta($post_ID, 'gifts_depending_precio_minimo', true);
            if ($precio > 0) {
                echo intval($precio);
            } else {
                echo 0;
            }
        }
    }

    function my_sortable_gift_column( $columns ) {
        $columns['precio_minimo'] = 'precio_minimo';

        //To make a column 'un-sortable' remove it from the array
        //unset($columns['date']);

        return $columns;
    }

}
?>
