<?php
include_once('EwanoAssist.php');

class EwanoApi {
    public function __construct(){
        $this->assist = new EwanoAssist();
        $this->development = $this->assist->development;
        $this->baseAddress = 'https://office.alaa.tv:4007/api/v1';
        $this->apiAddresses = [
            'pay' => $this->baseAddress . '/third-party/pay',
            'getUser' => $this->baseAddress . '/third-party',
            'makeOrder' => $this->baseAddress . '/third-party/order',
        ];
    }

    public function getUserFromService ($ewanoUserId)
    {
        $response = $this->sendGetRequest($this->apiAddresses['getUser'], [
            'id' => $ewanoUserId
        ]);


        // Error checking
        if ($response['wp_error'] || ($response['status'] !== 200 && $response['status'] !== 201)) {
            return null;
        }

        // You can then use json_decode to convert JSON response to an array.
        $data = json_decode($response['body'], true);
//        $sampleResponse = [
//            'first_name' => '',
//            'last_name' => '',
//            'mobile' => '',
//            'name_slug' => '',
//            'mobile_verified_at' => '',
//            'national_code' => '',
//            'photo' => '',
//            'kartemeli' => '',
//            'province' => '',
//            'city' => '',
//            'address' => '',
//            'postal_code' => '',
//            'school' => '',
//            'email' => '',
//            'bio' => '',
//            'info' => '',
//            'major' => '',
//            'grade' => '',
//            'gender' => '',
//            'birthdate' => '',
//            'shahr' => '',
//            'phone' => '',
//        ];

        $data['data']['username'] = $data['data']['mobile'];
        $data['data']['password'] = $data['data']['national_code'];
        return $data['data'];
    }

    public function makeOrder ($data)
    {
//        $sampleData = [
//            'msisdn' => $username, // Number
//            'id' => $order_id, // Number
//            'description' => '', // String
//            'discountAmount' => $totalDiscountOfOrder, // Number
//            'items' => [
//                [
//                    'name' => $item['name'],
//                    'quantity' => $item['quantity'],
//                    'unit_price' => $item['baseUnitPrice']
//                ],
//                .
//                .
//                .
//            ]
//        ];
//        if ($this->development) {
//            return '123456789'; // $ewanoOrderId
//        }
        $response = $this->sendPostRequest($this->apiAddresses['makeOrder'], $data);

        // Error checking
        if ($response['wp_error'] || ($response['status'] !== 200 && $response['status'] !== 201)) {
            return null;
        }

        // You can then use json_decode to convert JSON response to an array.
        $data = json_decode($response['body'], true);
//        $sampleRsponse = [
//            "third_party_order_id" => "3b3c91b3-8355-401d-b93d-c34160f2aa79",
//            "client_order_id" => "1",
//            "total_amount" => 6000000
//            "payable_amount" => 5000000
//        ];
        return $data['data'];
    }

    public function pay ($ewanoOrderId)
    {
//        $sampleData = [
//            'msisdn' => $username, // Number
//            'id' => $order_id, // Number
//            'description' => '', // String
//            'discountAmount' => $totalDiscountOfOrder, // Number
//            'items' => [
//                [
//                    'name' => $item['name'],
//                    'quantity' => $item['quantity'],
//                    'unit_price' => $item['baseUnitPrice']
//                ],
//                .
//                .
//                .
//            ]
//        ];
//        if ($this->development) {
//            return [
//                'ref_id' => date('ymdHis'),
//                'message' => 'success!',
//                'status' => '1',
//            ];
//        }
        $response = $this->sendPostRequest($this->apiAddresses['pay'], [
            'id' => $ewanoOrderId
        ]);

        // Error checking
        if ($response['wp_error'] || ($response['status'] !== 200 && $response['status'] !== 201)) {
            return null;
        }

        // You can then use json_decode to convert JSON response to an array.
        $data = json_decode($response['body'], true);
//        $sampleRsponse = [
//            "ref_id" => 2",
//            "status" => "OK",
//            "message" => "پرداخت موفقیت آمیز"
//        ];
        return $data['data'];
    }

    private function sendPostRequest ($endpoint, $params) {
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'body' => json_encode($params),
            'method' => 'POST',
            'data_format' => 'body'
        );
        $response = wp_remote_post($endpoint, $args);

        return [
            'raw_response' => $response,
            'wp_error' => is_wp_error($response),
            'body' => wp_remote_retrieve_body($response), // Retrieve the response body as an array.
            'status' => wp_remote_retrieve_response_code($response)
        ];
    }

    private function sendGetRequest ($endpoint, $params) {
        // Build the URL with the query parameters.
        $url = add_query_arg($params, $endpoint);
        $response = wp_remote_get($url);
        return [
            'row_response' => $response,
            'wp_error' => is_wp_error($response),
            'body' => wp_remote_retrieve_body($response), // Retrieve the response body as an array.
            'status' => wp_remote_retrieve_response_code($response)
        ];
    }
}
