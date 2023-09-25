<?php

namespace Drupal\localgov_subsites;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\localgov_subsites\Plugin\Block\SubsitesHierarchyTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Subsite.
 *
 * Provide methods for interacting with subsite entities.
 */
class Subsite implements ContainerInjectionInterface {
  use SubsitesHierarchyTrait;

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Node being displayed.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Initialise new subsite instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get subsite.
   *
   * @return null|\Drupal\node\NodeInterface
   *   Node object of Subsite overview page.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getSubsite($node) {
    $entity = NULL;

    if ($node instanceof NodeInterface) {
      if ($node->bundle() === 'localgov_subsites_overview') {
        $entity = $node;
      }
      elseif ($id = $this->getRootId($node)) {
        $entity = $this->entityTypeManager->getStorage('node')->load($id);
      }
    }

    return $entity;
  }

}
