<?php

namespace CommonKnowledge\WordpressStarterTemplate;

class API
{
    const NAMESPACE = "wordpress-starter-theme/v1";

    public static function register()
    {
        register_rest_route(self::NAMESPACE, '/example/', [
            'methods'  => 'GET',
            'callback' => self::class . "::example",
            "permission_callback" => "__return_true",
        ]);
    }

    public static function example(\WP_REST_Request $request)
    {
        return new \WP_REST_Response(['message' => 'Hello, World!'], 200);
    }
}
