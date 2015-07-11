<?php

namespace fkooman\OAuth;

interface ResourceServerInfoInterface 
{
    public function getResourceServer($bearerToken);
}
