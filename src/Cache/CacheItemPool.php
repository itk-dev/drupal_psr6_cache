<?php

namespace Drupal\drupal_psr6_cache\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Cache item pool implementation.
 */
class CacheItemPool implements CacheItemPoolInterface {
  private const TAGS = ['drupal_psr6_cache'];

  /**
   * Deferred cache items.
   *
   * @var \Psr\Cache\CacheItemInterface[]
   */
  protected array $deferred = [];

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private CacheBackendInterface $cache;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private CacheTagsInvalidatorInterface $cacheTagsInvalidator;

  /**
   * Constructor.
   */
  public function __construct(CacheBackendInterface $cache, CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    $this->cache = $cache;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public function getItem($key): CacheItemInterface {
    $value = $this->cache->get($this->getCid($key));

    return $value !== FALSE
      ? new CacheItem($key, $value->data, TRUE)
      : new CacheItem($key, NULL, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = []): iterable {
    return array_map([$this, 'getItem'], $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem($key): bool {
    return $this->getItem($key)->isHit();
  }

  /**
   * {@inheritdoc}
   */
  public function clear(): bool {
    $this->cacheTagsInvalidator->invalidateTags(static::TAGS);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($key): bool {
    $this->cache->delete($this->getCid($key));

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys): bool {
    array_map([$this, 'deleteItem'], $keys);

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItemInterface $item): bool {
    $this->cache->set(
      $this->getCid($item->getKey()),
      $item->get(),
      Cache::PERMANENT,
      static::TAGS
    );

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItemInterface $item): bool {
    $hash = spl_object_hash($item);
    $this->deferred[$hash] = $item;

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function commit(): bool {
    foreach ($this->deferred as $deferred) {
      $this->save($deferred);
    }

    return TRUE;
  }

  /**
   * Get cache id.
   */
  private function getCid(string $key) {
    return 'drupal_psr6_cache:' . $key;
  }

}
