<?php

namespace fkooman\OAuth;

class InputValidation
{
    public static function redirectUri($redirectUri)
    {
        #   The redirection endpoint URI MUST be an absolute URI as defined by
        #   [RFC3986] Section 4.3.  The endpoint URI MAY include an
        #   "application/x-www-form-urlencoded" formatted (per Appendix B) query
        #   component ([RFC3986] Section 3.4), which MUST be retained when adding
        #   additional query parameters.  The endpoint URI MUST NOT include a
        #   fragment component.

        // MUST be valid absolute URL
        // NOTE: this check is more strict than RFC 3986!
        // NOTE: we also require a PATH here
        if (false === filter_var($redirectUri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return false;
        }

        // MUST not have fragment
        if (null !== parse_url($redirectUri, PHP_URL_FRAGMENT)) {
            return false;
        }

        return $redirectUri;
    }

    public static function scope($scope)
    {
        return $scope;
    }

    public static function state($state)
    {
        if (null === $state) {
            return false;
        }
    }

    public static function code($code)
    {
        if (null === $code) {
            return false;
        }
    }

    public static function token($token)
    {
        if (null === $token) {
            return false;
        }
    }
}
