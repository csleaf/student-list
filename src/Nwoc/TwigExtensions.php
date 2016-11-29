<?php
namespace Nwoc;

/**
* Provides extensions for Twig template engine.
*/
class TwigExtensions {
    /**
    * Generates URI with base URL $uri_base with URL GET parameters $query_data, filtered to
    * only contain keys from $accept_only array.
    */
    public static function generate_uri(string $uri_base, array $query_data, array $accept_only): string {
        if (count($accept_only) > 0) {
            $get = array_filter($_GET, function($key) use ($accept_only) {
                if (in_array($key, $accept_only))
                    return TRUE;
                return FALSE;
            }, ARRAY_FILTER_USE_KEY);
        } else {
            $get = $_GET;
        }
        // @TODO
        return $uri_base . '?' . \http_build_query(array_merge($get, $query_data));
    }
}
