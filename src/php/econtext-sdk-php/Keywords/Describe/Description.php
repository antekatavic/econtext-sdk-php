<?php

namespace eContext\Keywords\Describe;
use eContext\Keywords\Describe\Describe;
use eContext\Client;

/**
 * A common interface for classification results.  Will always contain a
 * "categories" dictionary, may contain an "overlay" dictionary, and then a list
 * of results associated with each input.
 * 
 * The result set will use temporary files to store results - this will allow
 * us to send through a large list of keywords, and not maintain them and all
 * the associated data in memory.  Each time you switch to a new temp file set
 * of results, they will be pulled into memory, and categories, overlays, and
 * results will be overwritten.
 */
class Description extends \eContext\Result {
        
    protected function loadPage($data) {
        if($data === null) {
            return null;
        }
        parent::loadPage($data);
        $this->results = $this->get(Describe::JSON_INNER_ELEMENT, $data[Client::JSON_OUTER_ELEMENT], array());
        return True;
    }
}