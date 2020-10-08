<?php
declare(strict_types=1);

namespace FriMi\SDK\Transport;

class HTTPResponse {
  public $response_code;
  public $response_contents;

  public function isSuccess(): bool {
  	return $this->response_code === 200;
  }

  public function __construct(int $response_code, ?string $response_contents = null) {
    $this->response_code = $response_code;
    $this->response_contents = $response_contents;
  }
}
