<?php
declare(strict_types=1);

namespace FriMi\SDK\Transport;

interface TransportInterface {
  public function request(string $method, string $url, array $headers, string $request_body): HTTPResponse;
}
