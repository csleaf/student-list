<?php
namespace Nwoc;

/**
* Provides extensions for Twig template engine.
*/
class TwigExtensions {
    /**
    * Generates URI with base URL $uri_base with URL GET parameters $query_data, filtered to
    * only contain keys from $accept_only array. Accesses $_GET superglobal.
    * @uri_base: URL base (eg. http://example.org/)
    * @query_data: additional query parameters. 
    * @accept_only: restrict generated query parameters to only keys from this array. Can be null.
    */
    public static function generate_uri(string $uri_base, array $query_data, $accept_only): string {
        if (isset($accept_only) && count($accept_only) > 0) {
            $get = array_filter($_GET, function($key) use ($accept_only) {
                if (in_array($key, $accept_only))
                    return TRUE;
                return FALSE;
            }, ARRAY_FILTER_USE_KEY);
        } else {
            $get = $_GET;
        }
        $query_array = array_merge($get, $query_data);
        if (count($query_array == 0)) {
            return $uri_base;
        } else {
            return $uri_base . '?' . \http_build_query($query_array);
        }
    }
}
