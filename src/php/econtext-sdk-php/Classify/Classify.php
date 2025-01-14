<?php

namespace eContext\Classify;
use eContext\ApiCall;

abstract class Classify extends ApiCall {
    
    const JSON_INNER_ELEMENT = "classify";
    const URL_REQUEST_BASE = "/v2/classify";
    const ARRAY_LIMIT = 1000;
    
    protected $method;
    protected $data;
    protected $result;
    protected $request;
    protected $response;
    protected $requestUrl;
    protected $start;

    /**
     * Yield Guzzle client promises
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function yieldAsyncCalls() {
        $i = 0;
        while(true) {
            $data = $this->chunkData($this->data);
            if($data == false) {
                return;
            }
            $callSize = is_array($data) ? count($data) : 1;

            $this->addCallSize($callSize);
            $input = $this->input;
            $input['async'] = false;
            $input[static::CLASSIFY_TYPE] = $data;
            $body = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($input));
            yield $i++ => function() use ($body, $data) {
                return $this->client->getGuzzleClient()->postAsync(static::URL_REQUEST_BASE.static::URL_REQUEST_CLASS, ['body' => \GuzzleHttp\Psr7\Utils::streamFor($body)]);
            };
        }
    }
    
    /**
     * Create a new result set
     * 
     * @return \eContext\Classify\Result
     */
    protected function newResultSet() {
        return new Result($this->client->getTempDir());
    }
    
    /**
     * Classify the content
     * 
     * @param int $concurrency How large of pool to use
     * @param array $params A dictionary of base parameters to pass into the classification call (e.g. ['flags'=>true])
     * @throws \Exception You gotta have a requestUrl and data
     * @return \eContext\Classify\Result A Result Set
     */
    public function classify($concurrency=1, array $params=array()) {
        if($this->data == null) {
            throw new \Exception("Can't classify an empty dataset");
        }
        if(!empty($params)) {
            $this->input = $params;
        }
        $resultSet = $this->client->runPool($this->yieldAsyncCalls(), $this->newResultSet(), $concurrency);
        $resultSet->addCallSizes($this->callSizes);
        return $resultSet;
    }
}