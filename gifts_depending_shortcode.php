<?php
function gifts_depending_shortcode( $atts ){

    $permalink = get_permalink();
    $output = '';

    if (!empty($_GET['idgift']) && $_GET['idgift'] > 0) {
        $output .= 'Single del producto: '.get_the_title($_GET['idgift']);
    } else {

        $gifts = new WP_Query(array(
            'post_type'         =>  'gift',
            'posts_per_page'    =>  -1
        ));
        if ($gifts->have_posts()) {
            $output .= '<p>Selecciona el cupón que deseas recibir con tu donación. Recuerda que el monto de tu donación debe ser superior al monto indicado en el cupón.</p>';
            $output .= '<div class="gdp7_row">';
            while ($gifts->have_posts()) { $gifts->the_post();
                $idgift = get_the_id();
                $parameters = array('idgift' => $idgift);
                if (!empty($_GET['prod']) && $_GET['prod'] > 0) {
                    $parameters['prod'] = $_GET['prod'];
                }
                $output .= '<div class="gdp7_1_3">';
                $output .= '<a href="'.$permalink.'?'.http_build_query($parameters).'">'.get_the_post_thumbnail(get_the_id(), 'shop_catalog').'</a>';
                $output .= '<h3><a href="'.$permalink.'?'.http_build_query($parameters).'">'.get_the_title().'</a></h3>';
                $output .= "</div>";
            }
            $output .= '<div class="clear"></div>';
            $output .= '</div>';
        } else {
            $output .= "<p>No se encontraron cupones</p>";
        }

    }

    return $output;
}
add_shortcode( 'gifts_depending_purchase', 'gifts_depending_shortcode' );

?>
