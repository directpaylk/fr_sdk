<?php
declare(strict_types=1);

namespace FriMi\SDK\APIInfo\Server;

interface APIServerInterface {
  public function getTokenRequestUrl(): string;
  public function getPaymentRequestUrl(): string;
  public function getPaymentReversalUrl(): string;
  public function isRefundAllowed(int $timestamp): bool;
  public static function getName(): string;
}
