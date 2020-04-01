<?php
define('MB_PERSONALIZED_URL', plugin_dir_url(__FILE__));
class MB_Personalized{
    function __construct(){
        //$this->load();
    }
    function load(){

        add_action('woocommerce_before_add_to_cart_button', array( $this, 'render_personalize_fields' ));

        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'pin_data_add_to_cart' ), 10, 6 );

        add_action( 'woocommerce_get_item_data', array( $this, 'print_data_add_to_cart' ), 10, 4);
        
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'print_data_add_to_bill' ), 10, 4);

        add_filter( 'woocommerce_add_to_cart_validation',array( $this, 'validate_form_custom' ), 10, 3 );

    }

    function render_personalize_fields() {
        
        wp_enqueue_style( 'my-css', plugins_url('/style_01.css', __FILE__) );

        //load content

        $string1 = file_get_contents(__DIR__.'/data.json'); 
        $json = json_decode($string1, true);
        
            foreach ( $json as $key => $value) {
                ?>
                <div class="container">
                 <?php if ( $value['field_type'] === 'text'|| $value['field_type'] === 'number' ) { ?>
                        <div class = "<?php echo esc_attr( $value['title'] ) ?>">
                            <label for = ""><?php echo esc_html( $value['title'] ) ?></label>:<br>
                            <input type = "<?php echo esc_attr( $value['field_type'] ) ?>"class = "<?php echo esc_attr( $value['field_name'] ) ?>" id = "<?php echo esc_attr( $value['field_name'] ) ?>" name = "pm_personalized[<?php echo esc_attr( $value['field_name'] ) ?>]" placeholder = "<?php echo esc_attr( $value['title'] ) ?>" <?php if(!empty( $value['configs']['settings']['min'] )||!empty( $value['configs']['settings']['max'] )){ $a = $value['configs']['settings']['min']; $b=$value['configs']['settings']['max'];echo "min= '$a'max= '$b'";}?>>
                        </div>
                        <br>
                <?php } elseif( $value['field_type'] === 'textarea' ){ ?>
                    <div class = "<?php echo esc_attr( $value['title'] ) ?>">
                        <label for = ""><?php echo esc_html( $value['title'] ) ?></label>:<br>
                        <textarea class = "<?php echo esc_attr( $value['field_name'] ) ?>" id = "<?php echo esc_attr( $value['field_name'] ) ?>" name = "pm_personalized[<?php echo esc_attr( $value['field_name'] ) ?>]"></textarea>
                    </div>
                    <br>
                <?php } elseif( $value['field_type'] === 'checkbox' ){ ?>
                    <div class = "<?php echo esc_attr( $value['title'] ) ?>" style = "text-align:justify">
                    <label for = ""><?php echo esc_html( $value['title'] ) ?></label>:<br>
                    <?php foreach( $value['configs']['settings']['options'] as $key => $val){
                        ?>
                            <input type = "<?php echo esc_attr( $value['field_type'] ) ?>"class = "<?php echo esc_attr( $value['field_name'] ) ?>" id = "<?php echo esc_attr( $value['field_name'] ) ?>" name = "pm_personalized[<?php echo esc_attr( $value['field_name'] ) ?>][]" placeholder = "<?php echo esc_attr( $value['title'] ) ?>" <?php if( $val=== 'Orange' ){ echo 'checked';}?> value = "<?php echo esc_attr( $val) ?>">
                            <span ><?php echo esc_html( $val) ?></span>
                        <?php
                        }
                    ?>
                    </div>
                    <br>
                <?php } elseif( $value['field_type'] === 'radio' ){ ?>
                    <div class = "<?php echo esc_attr( $value['title'] ) ?>">
                        <label for = ""><?php echo esc_html( $value['title'] ) ?></label>:<br>
                        <ul id="checkbox">
                         <?php foreach ( $value['configs']['settings']['options'] as $key => $val){?>
                            <li>
                                    <input type="radio" id="<?php echo esc_attr( $value['field_name'] ) ?>_<?php echo esc_attr($val); ?>" value="<?php echo esc_attr($val); ?>" name="pm_personalized[<?php echo esc_attr( $value['field_name'] );?>]"/>
                                    <label for="<?php echo esc_attr( $value['field_name'] ) ?>_<?php echo esc_attr($val); ?>" class="label"><img src='http://localhost/wordpress/wp-content/uploads/2020/02/Screen-Shot-2019-12-09-at-23.29.45.png' class='<?php echo esc_attr( $value['field_name'] ) ?>'/></label>
                            </li>    
                            <?php
                            } 
                           ?>
                        </ul>
                    </div>
                    <br>
                <?php } else { ?>
                    <div class = "<?php echo esc_attr( $value['title'] ) ?>">
                        <label for = ""><?php echo esc_html( $value['title'] ) ?></label>:<br>
                        <select name = "pm_personalized[<?php echo esc_attr( $value['field_name'] ) ?>]">
                            <?php foreach( $value['configs']['settings']['options'] as $key => $val){
                                ?>
                                  <br>
                                    <option value = "<?php echo esc_attr( $val['value'] ) ?>"><?php echo esc_html( $val['label'] ) ?></option>
                                <?php
                            }
                           ?>
                        </select>
                    </div>
                <?php } ?> 
            </div>
            <br>
            <?php
            }
    }

    function validate_form_custom( $passed, $product_id, $quantity ) {
        if(isset($_POST[ 'pm_personalized' ])){
            
            $data = $_POST[ 'pm_personalized' ];

            foreach ( $data as $key => $value ) {
                if( !is_array( $value ) && empty( $value ) )
                {
                   
                    $passed = false;
                    wc_add_notice( __( 'Please enter a value into the '.$key.'', 'cfwc' ), 'error' );
    
                } elseif ( 32 <= strlen( ( string )$value ) )
                {
                    
                    $passed = false;
                    wc_add_notice( __( 'Max a value into the '.$key.' is 32.', 'cfwc' ), 'error' );
                }
                
            }
        }
        
        return $passed;
    }

    function pin_data_add_to_cart( $cart_item_data, $product_id, $variation_id, $quantity ){
        
        foreach( $_POST as $key => $value ){
            $cart_item_data[ $key ] = $value;
        }
        return $cart_item_data;
    }

    function print_data_add_to_cart( $item_data, $cart_item_data ) {
        if (isset($cart_item_data[ 'pm_personalized' ])) {
            $data = $cart_item_data[ 'pm_personalized' ];

            foreach ( $data as $key => $value )  {
    
                if ( is_array( $value ) ) {
                    $data = implode( ', ', $value );
                    $item_data[ $keys ] = array(
                                        'key' => __( $key),
                                        'value' => wc_clean( $data ),
                                        'display' => '',
                    );
                }
    
                else{
    
                    $item_data[$key] = array(
                                        'key' => __( $key),
                                        'value' => wc_clean( $value ),
                                        'display' => '',
                                    );
                }
                
            }
        }
        return $item_data;
    }
    
    function print_data_add_to_bill(  $item, $item_data, $values, $order ) {

        if ( is_array( $values[ 'pm_personalized' ] ) && ! empty( $values[ 'pm_personalized' ] ) )  {

            update_option( 'update_otion_item', $values[ 'pm_personalized' ] );

            foreach( $values[ 'pm_personalized' ] as $key => $value ) {
                
                $data = $value;

                if ( is_array( $value ) ) {
                    $data = implode( ', ', $value );
                }
                $item->add_meta_data( $key, $data, true );

            }
        }
    }
    
}
$mycustom = new MB_Personalized;

$mycustom->load();