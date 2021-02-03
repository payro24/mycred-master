<?php

add_action( 'plugins_loaded', 'mycred_payro24_plugins_loaded' );

function mycred_payro24_plugins_loaded() {
    add_filter( 'mycred_setup_gateways', 'Add_payro24_to_Gateways' );
    function Add_payro24_to_Gateways( $installed ) {
        $installed['payro24'] = [
            'title'    => get_option( 'payro24_display_name' ) ? get_option( 'payro24_display_name' ) : __( 'payro24 payment gateway', 'payro24-mycred' ),
            'callback' => [ 'myCred_payro24' ],
        ];
        return $installed;
    }

    add_filter( 'mycred_buycred_refs', 'Add_payro24_to_Buycred_Refs' );
    function Add_payro24_to_Buycred_Refs( $addons ) {
        $addons['buy_creds_with_payro24'] = __( 'payro24 Gateway', 'payro24-mycred' );

        return $addons;
    }

    add_filter( 'mycred_buycred_log_refs', 'Add_payro24_to_Buycred_Log_Refs' );
    function Add_payro24_to_Buycred_Log_Refs( $refs ) {
        $payro24 = [ 'buy_creds_with_payro24' ];

        return $refs = array_merge( $refs, $payro24 );
    }

    add_filter( 'wp_body_open', 'payro24_success_message_handler' );
    function payro24_success_message_handler( $template ){
        if( !empty( $_GET['mycred_payro24_nok'] ) )
            echo '<div class="mycred_payro24_message error">'. $_GET['mycred_payro24_nok'] .'</div>';

        if( !empty( $_GET['mycred_payro24_ok'] ) )
            echo '<div class="mycred_payro24_message success">'. $_GET['mycred_payro24_ok'] .'</div>';

        if( !empty( $_GET['mycred_payro24_nok'] ) || !empty( $_GET['mycred_payro24_ok'] ))
            echo '<style>
                .mycred_payro24_message {
                    position: absolute;
                    z-index: 9;
                    top: 40px;
                    right: 15px;
                    color: #fff;
                    padding: 15px;
                }
                .mycred_payro24_message.error {
                    background: #F44336;
                }
                .mycred_payro24_message.success {
                    background: #4CAF50;
                }
            </style>';
    }
}

spl_autoload_register( 'mycred_payro24_plugin' );

function mycred_payro24_plugin() {
    if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {
        return;
    }

    if ( ! class_exists( 'myCred_payro24' ) ) {
        class myCred_payro24 extends myCRED_Payment_Gateway {

            function __construct( $gateway_prefs ) {
                $types            = mycred_get_types();
                $default_exchange = [];

                foreach ( $types as $type => $label ) {
                    $default_exchange[ $type ] = 1000;
                }

                parent::__construct( [
                    'id'                => 'payro24',
                    'label'             => get_option( 'payro24_display_name' ) ? get_option( 'payro24_display_name' ) : __( 'payro24 payment gateway', 'payro24-mycred' ),
                    'documentation'     => 'https://blog.payro24.ir/helps/171',
                    'gateway_logo_url'  => plugins_url( '/assets/logo.svg', __FILE__ ),
                    'defaults'          => [
                        'api_key'            => NULL,
                        'sandbox'            => FALSE,
                        'payro24_display_name' => __( 'payro24 payment gateway', 'payro24-mycred' ),
                        'currency'           => 'rial',
                        'exchange'           => $default_exchange,
                        'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
                    ],
                ], $gateway_prefs );
            }

            public function payro24_Iranian_currencies( $currencies ) {
                unset( $currencies );

                $currencies['rial']  = __( 'Rial', 'payro24-mycred' );
                $currencies['toman'] = __( 'Toman', 'payro24-mycred' );

                return $currencies;
            }

            function preferences() {
                add_filter( 'mycred_dropdown_currencies', [
                    $this,
                    'payro24_Iranian_currencies',
                ] );

                $prefs = $this->prefs;
                ?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'api_key' ); ?>"><?php _e( 'API Key', 'payro24-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'api_key' ); ?>"
                                   name="<?php echo $this->field_name( 'api_key' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['api_key']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'sandbox' ); ?>"><?php _e( 'Sandbox', 'payro24-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'sandbox' ); ?>"
                                   name="<?php echo $this->field_name( 'sandbox' ); ?>"
                                   <?php echo $prefs['sandbox'] == "on"? 'checked="checked"' : '' ?>
                                   type="checkbox"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'payro24_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'payro24_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'payro24_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['payro24_display_name'] ? $prefs['payro24_display_name'] : __( 'payro24 payment gateway', 'payro24-mycred' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->currencies_dropdown( 'currency', 'mycred-gateway-payro24-currency' ); ?>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
                <?php
            }

            public function sanitise_preferences( $data ) {
                $new_data['api_key']            = sanitize_text_field( $data['api_key'] );
                $new_data['payro24_display_name'] = sanitize_text_field( $data['payro24_display_name'] );
                $new_data['currency']           = sanitize_text_field( $data['currency'] );
                $new_data['item_name']          = sanitize_text_field( $data['item_name'] );
                $new_data['sandbox']            = sanitize_text_field( $data['sandbox'] );

                if ( isset( $data['exchange'] ) ) {
                    foreach ( (array) $data['exchange'] as $type => $rate ) {
                        if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), ['.', ',',] ) ) {
                            $data['exchange'][ $type ] = (float) '0' . $rate;
                        }
                    }
                }

                $new_data['exchange'] = $data['exchange'];
                update_option( 'payro24_display_name', $new_data['payro24_display_name'] );
                return $data;
            }

            public function process() {

                $pending_post_id = sanitize_text_field( $_REQUEST['payment_id'] );
                $org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );
                $mycred = mycred( $org_pending_payment->point_type );

                $status    = !empty($_POST['status'])  ? sanitize_text_field($_POST['status'])   : (!empty($_GET['status'])  ? sanitize_text_field($_GET['status'])   : NULL);
                $track_id  = !empty($_POST['track_id'])? sanitize_text_field($_POST['track_id']) : (!empty($_GET['track_id'])? sanitize_text_field($_GET['track_id']) : NULL);
                $id        = !empty($_POST['id'])      ? sanitize_text_field($_POST['id'])       : (!empty($_GET['id'])      ? sanitize_text_field($_GET['id'])       : NULL);
                $order_id  = !empty($_POST['order_id'])? sanitize_text_field($_POST['order_id']) : (!empty($_GET['order_id'])? sanitize_text_field($_GET['order_id']) : NULL);
                $params    = !empty($_POST['id']) ? $_POST : $_GET;

                if ( $status == 10 ) {
                    $api_key = $api_key = $this->prefs['api_key'];
                    $sandbox = $this->prefs['sandbox'];

                    $data = [
                        'id'       => $id,
                        'order_id' => $order_id,
                    ];
                    $headers = [
                        'Content-Type' => 'application/json',
                        'P-TOKEN'    => $api_key,
                        'P-SANDBOX'    => $sandbox,
                    ];
                    $args = [
                        'body'    => json_encode( $data ),
                        'headers' => $headers,
                        'timeout' => 30,
                    ];

                    $response = $this->call_gateway_endpoint( 'https://api.payro24.ir/v1.1/payment/verify', $args );
                    if ( is_wp_error( $response ) ) {
                        $log = $response->get_error_message();
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_payro24',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_payro24_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }
                    $http_status = wp_remote_retrieve_response_code( $response );
                    $result      = wp_remote_retrieve_body( $response );
                    $result      = json_decode( $result );

                    if ( $http_status != 200 ) {
                        $log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'payro24-mycred' ), $http_status, $result->error_code, $result->error_message );
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_payro24',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_payro24_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }

                    if ( $result->status = 100 ) {
                        $message = sprintf( __( 'Payment succeeded. Status: %s, Track id: %s, Order no: %s', 'payro24-mycred' ), $result->status, $result->track_id, $result->order_id );
                        $log = $message . ", card-no: " . $result->payment->card_no . ", hashed-card-no: " . $result->payment->hashed_card_no;
                        add_filter( 'mycred_run_this', function( $filter_args ) use ( $log ) {
                            return $this->mycred_payro24_success_log( $filter_args, $log );
                        } );

                        if ( $this->complete_payment( $org_pending_payment, $id ) ) {

                            $this->log_call( $pending_post_id, $message );
                            $this->trash_pending_payment( $pending_post_id );

                            $return = add_query_arg( 'mycred_payro24_ok', $message, $this->get_thankyou() );
                            wp_redirect( $return );
                            exit;
                        } else {

                            $log = sprintf( __( 'An unexpected error occurred when completing the payment but it is done at the gateway. Track id is: %s', 'payro24-mycred', $result->track_id ) );
                            $this->log_call( $pending_post_id, $log );
                            $mycred->add_to_log(
                                'buy_creds_with_payro24',
                                $pending_payment->buyer_id,
                                $pending_payment->amount,
                                $log,
                                $pending_payment->buyer_id,
                                $result
                            );

                            $return = add_query_arg( 'mycred_payro24_nok', $log, $this->get_cancelled() );
                            wp_redirect( $return );
                            exit;
                        }
                    }

                    $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s, Card no: %s', 'payro24-mycred' ), $result->status, $result->track_id, $result->payment->card_no );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_payro24',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $result
                    );

                    $return = add_query_arg( 'mycred_payro24_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;

                } else {
                    $error = $this->getStatus($status);

                    $log = sprintf( __( '%s (Code: %s), Track id: %s', 'payro24-mycred' ), $error, $status, $track_id );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_payro24',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $params
                    );

                    $return = add_query_arg( 'mycred_payro24_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;
                }
            }

            public function returning() {}

            public function mycred_payro24_success_log( $request, $log ){
                if( $request['ref'] == 'buy_creds_with_payro24' )
                    $request['entry'] = $log;

                return $request;
            }
            /**
             * Prep Sale
             *
             * @since   1.8
             * @version 1.0
             */
            public function prep_sale( $new_transaction = FALSE ) {

                // Point type
                $type   = $this->get_point_type();
                $mycred = mycred( $type );

                // Amount of points
                $amount = $mycred->number( $_REQUEST['amount'] );

                // Get cost of that points
                $cost = $this->get_cost( $amount, $type );
                $cost = abs( $cost );

                $to   = $this->get_to();
                $from = $this->current_user_id;

                // Revisiting pending payment
                if ( isset( $_REQUEST['revisit'] ) ) {
                    $this->transaction_id = strtoupper( $_REQUEST['revisit'] );
                } else {
                    $post_id = $this->add_pending_payment( [
                        $to,
                        $from,
                        $amount,
                        $cost,
                        $this->prefs['currency'],
                        $type,
                    ] );
                    $this->transaction_id = get_the_title( $post_id );
                }

                $is_ajax    = ( isset( $_REQUEST['ajax'] ) && $_REQUEST['ajax'] == 1 ) ? true : false;
                $callback = add_query_arg( 'payment_id', $this->transaction_id, $this->callback_url() );
                $api_key  = $this->prefs['api_key'];
                $sandbox  = $this->prefs['sandbox'];

                $data = [
                    'order_id' => $this->transaction_id,
                    'amount'   => ( $this->prefs['currency'] == 'toman' ) ? ( $cost * 10 ) : $cost,
                    'name'     => '',
                    'phone'    => '',
                    'mail'     => '',
                    'desc'     => '',
                    'callback' => $callback,
                ];
                $headers = [
                    'Content-Type' => 'application/json',
                    'P-TOKEN'    => $api_key,
                    'P-SANDBOX'    => $sandbox,
                ];
                $args = [
                    'body'    => json_encode( $data ),
                    'headers' => $headers,
                    'timeout' => 30,
                ];

                $response = $this->call_gateway_endpoint( 'https://api.payro24.ir/v1.1/payment', $args );
                if ( is_wp_error( $response ) ) {
                    $error = $response->get_error_message();
                    $mycred->add_to_log(
                        'buy_creds_with_payro24',
                        $from,
                        $amount,
                        $error,
                        $from,
                        $data,
                        'point_type_key'
                    );

                    if($is_ajax){
                        $this->errors[] = $error;
                    }
                    else if( empty( $_GET['payro24_error'] ) ){
                        wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&payro24_error='. $error );
                        exit;
                    }
                }

                $http_status = wp_remote_retrieve_response_code( $response );
                $result      = wp_remote_retrieve_body( $response );
                $result      = json_decode( $result );

                if ( $http_status != 201 || empty( $result ) || empty( $result->id ) || empty( $result->link ) ) {
                    if ( ! empty( $result->error_code ) && ! empty( $result->error_message ) ) {
                        $error = $result->error_message;

                        $mycred->add_to_log(
                            'buy_creds_with_payro24',
                            $from,
                            $amount,
                            $error,
                            $from,
                            $data,
                            'point_type_key'
                        );

                        if($is_ajax){
                            $this->errors[] = $error;
                        }
                        else if( empty( $_GET['payro24_error'] ) ){
                            wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&payro24_error='. $error );
                            exit;
                        }
                    }
                }

                $item_name = str_replace( '%number%', $this->amount, $this->prefs['item_name'] );
                $item_name = $this->core->template_tags_general( $item_name );

                $redirect_fields = [
                    //'pay_to_email'        => $this->prefs['account'],
                    'transaction_id'      => $this->transaction_id,
                    'return_url'          => $this->get_thankyou(),
                    'cancel_url'          => $this->get_cancelled( $this->transaction_id ),
                    'status_url'          => $this->callback_url(),
                    'return_url_text'     => get_bloginfo( 'name' ),
                    'hide_login'          => 1,
                    'merchant_fields'     => 'sales_data',
                    'sales_data'          => $this->post_id,
                    'amount'              => $this->cost,
                    'currency'            => $this->prefs['currency'],
                    'detail1_description' => __( 'Item Name', 'mycred' ),
                    'detail1_text'        => $item_name,
                ];

                // Customize Checkout Page
                if ( isset( $this->prefs['account_title'] ) && ! empty( $this->prefs['account_title'] ) ) {
                    $redirect_fields['recipient_description'] = $this->core->template_tags_general( $this->prefs['account_title'] );
                }

                if ( isset( $this->prefs['account_logo'] ) && ! empty( $this->prefs['account_logo'] ) ) {
                    $redirect_fields['logo_url'] = $this->prefs['account_logo'];
                }

                if ( isset( $this->prefs['confirmation_note'] ) && ! empty( $this->prefs['confirmation_note'] ) ) {
                    $redirect_fields['confirmation_note'] = $this->core->template_tags_general( $this->prefs['confirmation_note'] );
                }

                // If we want an email receipt for purchases
                if ( isset( $this->prefs['email_receipt'] ) && ! empty( $this->prefs['email_receipt'] ) ) {
                    $redirect_fields['status_url2'] = $this->prefs['account'];
                }

                // Gifting
                if ( $this->gifting ) {
                    $user                                   = get_userdata( $this->recipient_id );
                    $redirect_fields['detail2_description'] = __( 'Recipient', 'mycred' );
                    $redirect_fields['detail2_text']        = $user->display_name;
                }

                $this->redirect_fields = $redirect_fields;
                $this->redirect_to = empty( $_GET['payro24_error'] )? $result->link : $_SERVER['REQUEST_URI'];
            }

            /**
             * AJAX Buy Handler
             *
             * @since   1.8
             * @version 1.0
             */
            public function ajax_buy() {
                // Construct the checkout box content
                $content = $this->checkout_header();
                $content .= $this->checkout_logo();
                $content .= $this->checkout_order();
                $content .= $this->checkout_cancel();
                $content .= $this->checkout_footer();

                // Return a JSON response
                $this->send_json( $content );
            }

            /**
             * Checkout Page Body
             * This gateway only uses the checkout body.
             *
             * @since   1.8
             * @version 1.0
             */
            public function checkout_page_body() {
                echo $this->checkout_header();
                echo $this->checkout_logo( FALSE );
                echo $this->checkout_order();
                echo $this->checkout_cancel();
                if( !empty( $_GET['payro24_error'] ) ){
                    echo '<div class="alert alert-error payro24-error">'. $_GET['payro24_error'] .'</div>';
                    echo '<style>
                        .checkout-footer, .payro24-logo, .checkout-body > img {display: none;}
                        .payro24-error {
                            background: #F44336;
                            color: #fff;
                            padding: 15px;
                            margin: 10px 0;
                        }
                    </style>';
                }
                else {
                    echo '<style>.checkout-body > img {display: none;}</style>';
                }
                echo $this->checkout_footer();
                echo sprintf(
                    '<span class="payro24-logo" style="font-size: 12px;padding: 5px 0;"><img src="%1$s" style="display: inline-block;vertical-align: middle;width: 70px;">%2$s</span>',
                    plugins_url( '/assets/logo.svg', __FILE__ ), __( 'Pay with payro24', 'payro24-mycred' )
                );

            }

            /**
             * Calls the gateway endpoints.
             *
             * Tries to get response from the gateway for 4 times.
             *
             * @param $url
             * @param $args
             *
             * @return array|\WP_Error
             */
            private function call_gateway_endpoint( $url, $args ) {
                $number_of_connection_tries = 4;
                while ( $number_of_connection_tries ) {
                    $response = wp_safe_remote_post( $url, $args );
                    if ( is_wp_error( $response ) ) {
                        $number_of_connection_tries --;
                        continue;
                    } else {
                        break;
                    }
                }
                return $response;
            }

            /**
             * return description for status.
             *
             * Tries to get response from the gateway for 4 times.
             *
             * @param $url
             * @param $args
             *
             * @return array|\WP_Error
             */
            public function getStatus($status_code){
                switch ($status_code){
                    case 1:
                        return 'پرداخت انجام نشده است';
                        break;
                    case 2:
                        return 'پرداخت ناموفق بوده است';
                        break;
                    case 3:
                        return 'خطا رخ داده است';
                        break;
                    case 4:
                        return 'بلوکه شده';
                        break;
                    case 5:
                        return 'برگشت به پرداخت کننده';
                        break;
                    case 6:
                        return 'برگشت خورده سیستمی';
                        break;
                    case 7:
                        return 'انصراف از پرداخت';
                        break;
                    case 8:
                        return 'به درگاه پرداخت منتقل شد';
                        break;
                    case 10:
                        return 'در انتظار تایید پرداخت';
                        break;
                    case 100:
                        return 'پرداخت تایید شده است';
                        break;
                    case 101:
                        return 'پرداخت قبلا تایید شده است';
                        break;
                    case 200:
                        return 'به دریافت کننده واریز شد';
                        break;
                }
            }
        }
    }
}
