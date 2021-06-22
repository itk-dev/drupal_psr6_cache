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
   * @var \Drupal\Core\Cache\CacheItemInterface[]
   */
  protected $deferred = [];

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

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
  public function getItem($key) {
    $value = $this->cache->get($this->getCid($key));

    return $value !== FALSE
      ? new CacheItem($key, $value->data, TRUE)
      : new CacheItem($key, NULL, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = []) {
    return array_map([$this, 'getItem'], $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem($key) {
    return $this->getItem($key)->isHit();
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->cacheTagsInvalidator->invalidateTags(static::TAGS);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($key) {
    $this->cache->delete($this->getCid($key));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys) {
    return array_map([$this, 'deleteItem'], $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItemInterface $item) {
    $this->cache->set(
      $this->getCid($item->getKey()),
      $item->get(),
      Cache::PERMANENT,
      static::TAGS
    );
  }

  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItemInterface $item) {
    $hash = spl_object_hash($item);
    $this->deferred[$hash] = $item;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    foreach ($this->deferred as $deferred) {
      $this->save($deferred);
    }
  }

  /**
   * Get cache id.
   */
  private function getCid(string $key) {
    return 'drupal_psr6_cache:' . $key;
  }

}
