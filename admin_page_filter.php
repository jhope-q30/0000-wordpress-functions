<?php

/*  Filter page(s) by parent or "sections"
    Only displays top level items with all children
*/

function _load_edit_functions(){

    add_action( 'restrict_manage_posts', '_filter_by_page_parent' );
    add_action( 'pre_get_posts', '_load_edit_function_query' );

}
add_action( 'load-edit.php' , '_load_edit_functions' );

function _load_edit_function_query( $query ){

    global $pagenow;
    global $post_type;
    
    $possible_post_types = array( 'page' );

    if( !empty( $pagenow ) && $pagenow == 'edit.php' && in_array( $post_type, $possible_post_types ) ) {

        if( isset( $_GET['FILTER_BY_SECTION'] ) ){

            $parent_id = sanitize_text_field( $_GET['FILTER_BY_SECTION'] );

            // get all children of parent

            $posts_in = array( $parent_id );

            $children = get_pages( array( 'depth' => -1, 'child_of' => $parent_id ) );

            foreach( $children as $child ){

                array_push( $posts_in, $child->ID );

            }

            $query->query_vars['post__in'] = $posts_in;

        }

    }

}

function _filter_by_page_parent(){

    global $wpdb;
    global $post_type;

    $field_name = 'FILTER_BY_SECTION';

    // check for post type 'page'

    if( 'page' == $post_type ){

        $sql = "SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_parent = 0"; /* only grab top level pages */
        $results = $wpdb->get_results( $sql, ARRAY_N );

        if( count( $results ) ):

            $current = isset( $_GET[$field_name] ) ? $_GET[$field_name] : '';

?>
<select name="<?php echo $field_name; ?>">
<option value=""><?php _e('Filter by Section', 'fac'); ?></option>
<?php
            foreach( $results as $result ):

                if( count( get_pages( 'child_of=' . $result[0] ) ) ){

                    $selected = $result[0] == sanitize_text_field( $current ) ? ' selected="selected"' : '';

?>
<option value="<?php echo $result[0]; ?>"<?php echo $selected; ?>><?php echo $result[1]; ?></option>
<?php

                }

            endforeach;
?>
</select>
<?php

        endif;

    }

}

?>
