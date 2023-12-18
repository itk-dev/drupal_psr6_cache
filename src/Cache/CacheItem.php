<?php

namespace Drupal\drupal_psr6_cache\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Cache item implementation.
 */
class CacheItem implements CacheItemInterface {
  /**
   * The key.
   *
   * @var string
   */
  private string $key;

  /**
   * The value.
   *
   * @var mixed
   */
  private mixed $value;

  /**
   * The is hit.
   *
   * @var bool
   */
  private bool $isHit;

  /**
   * The expiry.
   *
   * @var float|null
   */
  private ?float $expiry;

  /**
   * CacheItem constructor.
   *
   * @param string $key
   *   The key.
   * @param mixed $data
   *   The data.
   * @param bool $isHit
   *   The is hit.
   */
  public function __construct(string $key, mixed $data, bool $isHit) {
    $this->key   = $key;
    $this->value = $data;
    $this->isHit = $isHit;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey(): string {
    return $this->key;
  }

  /**
   * {@inheritdoc}
   */
  public function get(): mixed {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isHit(): bool {
    return $this->isHit;
  }

  /**
   * {@inheritdoc}
   */
  public function set($value): static {
    $this->value = $value;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAt($expiration): static {
    if ($expiration === NULL) {
      $this->expiry = NULL;
    }
    else {
      $this->expiry = (float) $expiration->format('U.u');
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAfter($time): static {
    if ($time === NULL) {
      $this->expiry = NULL;
    }
    elseif ($time instanceof \DateInterval) {
      $this->expiry = microtime(TRUE) + (float) \DateTime::createFromFormat('U', '0')->add($time)->format('U.u');
    }
    else {
      $this->expiry = $time + microtime(TRUE);
    }

    return $this;
  }

}
