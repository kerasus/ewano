<?php

class EwanoReport {
    public function __construct(){
        add_action('admin_menu', function() {
            $this->addMenuPage();
        });
    }

    private function addMenuPage () {
        add_menu_page(
            'ایوانو',
            'ایوانو',
            'manage_options',
            'ewano-parent-menu',
            function() {},
            'dashicons-admin-generic'
        );
        // Create first sub menu
        add_submenu_page(
            'ewano-parent-menu',
            'لیست سفارشات ایوانو',
            'لیست سفارشات',
            'manage_options',
            'ewano-orders',
            function() {
                $this->showOrdersPage();
            }
        );
        // Create first sub menu
        add_submenu_page(
            'ewano-parent-menu',
            'لیست کاربران ایوانو',
            'لیست کاربران',
            'manage_options',
            'ewano-users',
            function() {
                $this->showUsersPage();
            }
        );
    }

    public function showOrdersPage () {
        // Security check to verify the user has the required capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        wp_enqueue_style( 'wp-admin' );

        // Start outputting the report page HTML
        echo '<div class="wrap">';
        echo '<h1>لیست سفارش های ایوانو</h1>';


        $per_page = 20;
        $current_page = isset($_GET['page_number']) ? $_GET['page_number'] : 1;
        $offset = ($current_page - 1) * $per_page;

        $args = array(
//            'limit'        => -1, // Set this to the number of orders you want to retrieve, or -1 for no limit.
//            'status'       => 'any', // Get orders of any status, or specify status with 'wc-completed', 'wc-processing', etc.
//            'status'       => array_keys(wc_get_order_statuses()), // Retrieve orders of all statuses or specify which you want
            'payment_method'     => 'WC_Ewano',
//            'meta_value'   => 'WC_Ewano', // Set this to the specific payment method ID you're filtering by
//            'meta_compare' => '=', // You can use '=' to get exact match or 'like' to get similar results.

//            'orderby'      => 'date', // Sort by date created.
//            'order'        => 'DESC', // Use 'ASC' for ascending order, 'DESC' for descending.
//            'type'         => 'shop_order', // Define the type of posts to retrieve, for orders it's 'shop_order'.
//            'return'       => 'ids', // To retrieve only IDs.
        );
        $paginatedArgs = [
            'limit' => $per_page,
            'offset' => $offset
        ];
        $allPagesArgs = [
            'limit' => -1,
        ];


        $orders = wc_get_orders(array_merge($args, $paginatedArgs));
        // Start output buffer for table
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>شماره سفارش</th>
                    <th>مشتری</th>
                    <th>وضعیت</th>
                    <th>مجموع</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Process the orders
            foreach ($orders as $order) {
                $orderId = $order->get_id(); // Get the order ID
                $orderNumber = $order->get_order_number();
                $orderStatus = wc_get_order_status_name($order->get_status());
                $orderFormattedOrderTotal = $order->get_formatted_order_total();
                $customerLastName = $order->get_billing_last_name();
                $customerFirstName = $order->get_billing_first_name();
                $orderDate_created = get_the_time(get_option('date_format').' '.get_option('time_format'), $order->get_id() );
//                $paymentMethod = $order->get_payment_method(); // Get the payment method ID
//                $paymentMethodTitle = $order->get_payment_method_title(); // Get the payment method title

                $orderLink = esc_url( admin_url( 'post.php?post=' . $orderId . '&action=edit' ) );

                ?>
                <tr>
                    <td><?php echo $orderId; ?></td>
                    <td><?php echo $orderNumber; ?></td>
                    <td><?php echo $customerFirstName . ' ' . $customerLastName; ?></td>
                    <td><?php echo $orderStatus; ?></td>
                    <td><?php echo $orderFormattedOrderTotal; ?></td>
                    <td><?php echo $orderDate_created; ?></td>
                    <td>
                        <a href="<?php echo $orderLink; ?>">
                            <input type="submit" class="button action" value="مشاهده">
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
        // Get contents from buffer
        $table_html = ob_get_clean();

        // Page navigation
        $total_orders = wc_get_orders(array_merge($args, $allPagesArgs));
        $totalOrdersCount = count($total_orders);
        $num_pages = ceil($totalOrdersCount / $per_page);

        $pagination_args = array(
            'base' => add_query_arg('page_number','%#%'),
            'format' => '?page_number=%#%',
            'total' => $num_pages,
            'current' => $current_page,
            'show_all' => false,
            'type' => 'plain',
        );

        $paginate_links = paginate_links($pagination_args);

        // Print table
        echo $table_html;

        ?>

        <style>
            /* Pagination */
            .custom-pagination {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 4px;
            }

            .custom-pagination a,
            .custom-pagination span {
                border: 1px solid #2271b1;
                color: #2271b1;
                background: #f6f7f7;
                min-width: 30px;
                min-height: 30px;
                padding: 0 4px;
                font-size: 13px;
                border-radius: 3px;
                font-family: Tahoma,Arial,sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-left: 4px;
            }

            .custom-pagination span,
            .custom-pagination a:hover {
                border: 1px solid #2271b1;
            }

            .custom-pagination span.current {
                font-weight: bold;
                color: #f6f7f7;
                background: #2271b1;
            }
        </style>

        <?php
        if ($paginate_links) {
            echo "<nav class='custom-pagination'>";
            echo $paginate_links;
            echo "</nav>";
        }

        echo '</div>'; // Close the wrap
    }

    public function showUsersPage () {
        // Security check to verify the user has the required capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        wp_enqueue_style( 'wp-admin' );

        // Start outputting the report page HTML
        echo '<div class="wrap">';
        echo '<h1>لیست کاربران ایوانو</h1>';


        $per_page = 2;
        $current_page = isset($_GET['page_number']) ? $_GET['page_number'] : 1;
        $offset = ($current_page - 1) * $per_page;

        $user_args = [
            'number' => $per_page,
            'paged'  => $current_page,
            'meta_query' => [
                [
                    'key' => 'from_ewano',
                    'value' => 1,
                    'compare' => '='
                ]
            ]
        ];
        $paginatedArgs = [
            'limit' => $per_page,
            'offset' => $offset
        ];
        $allPagesArgs = [
            'limit' => -1,
        ];


        $user_query = new WP_User_Query(array_merge($user_args, $paginatedArgs));

        $users = $user_query->get_results();

        // Start output buffer for table
        ob_start();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>نام</th>
                    <th>نام خانوادگی</th>
                    <th>موبایل</th>
                    <th>کد ملی</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($users as $user) {
                $userId = $user->ID;
                $mobile = $user->user_login;
                $userEditLink = get_edit_user_link( $user->ID );
                $lName = get_user_meta( $userId, 'last_name', true );
                $fName = get_user_meta( $userId, 'first_name', true );
                $nationalCode = get_user_meta( $userId, 'national_code', true );
                $registeredAt = date_i18n(get_option('date_format').' '.get_option('time_format'), $user->user_registered);
                ?>
                <tr>
                    <td><?php echo $userId; ?></td>
                    <td><?php echo $fName; ?></td>
                    <td><?php echo $lName; ?></td>
                    <td><?php echo $mobile; ?></td>
                    <td><?php echo $nationalCode; ?></td>
                    <td><?php echo $registeredAt; ?></td>
                    <td>
                        <a href="<?php echo $userEditLink; ?>">
                            <input type="submit" class="button action" value="مشاهده">
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
        // Get contents from buffer
        $table_html = ob_get_clean();

        // Page navigation
        $allUserQuery = new WP_User_Query(array_merge($user_args, $allPagesArgs));
        $totalUsers = $allUserQuery->get_results();
        $totalUsersCount = count($totalUsers);
        $num_pages = ceil($totalUsersCount / $per_page);

        $pagination_args = array(
            'base' => add_query_arg('page_number','%#%'),
            'format' => '?page_number=%#%',
            'total' => $num_pages,
            'current' => $current_page,
            'show_all' => false,
            'type' => 'plain',
        );

        $paginate_links = paginate_links($pagination_args);

        // Print table
        echo $table_html;

        ?>

        <style>
            /* Pagination */
            .custom-pagination {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 4px;
            }

            .custom-pagination a,
            .custom-pagination span {
                border: 1px solid #2271b1;
                color: #2271b1;
                background: #f6f7f7;
                min-width: 30px;
                min-height: 30px;
                padding: 0 4px;
                font-size: 13px;
                border-radius: 3px;
                font-family: Tahoma,Arial,sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-left: 4px;
            }

            .custom-pagination span,
            .custom-pagination a:hover {
                border: 1px solid #2271b1;
            }

            .custom-pagination span.current {
                font-weight: bold;
                color: #f6f7f7;
                background: #2271b1;
            }
        </style>

        <?php
        if ($paginate_links) {
            echo "<nav class='custom-pagination'>";
            echo $paginate_links;
            echo "</nav>";
        }

        echo '</div>'; // Close the wrap
    }
}
