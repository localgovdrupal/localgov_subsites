<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_subsites\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\localgov_subsites\Plugin\Block\SubsitesHierarchyTrait;
use Drupal\node\NodeInterface;

/**
 * Test SubsitesHierarchyTrait methods.
 *
 * @group localgov_subsites
 */
class SubsitesHierarchyTraitTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;
  use SubsitesHierarchyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dbal',
    'field',
    'filter',
    'entity_hierarchy',
    'language',
    'link',
    'node',
    'path',
    'system',
    'text',
    'token',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig([
      'filter',
      'system',
      'user',
      'node',
    ]);

    // Create subsite content types and entity hierarchy field.
    $this->createContentType([
      'type' => 'localgov_subsites_overview',
      'name' => 'Subsites Overview',
    ]);
    $this->createContentType([
      'type' => 'localgov_subsites_page',
      'name' => 'Subsites Page',
    ]);
    $storage = FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'localgov_subsites_parent',
      'id' => 'node.localgov_subsites_parent',
      'type' => 'entity_reference_hierarchy',
      'settings' => [
        'target_type' => 'node',
      ],
    ]);
    $storage->save();
    $config = FieldConfig::create([
      'field_name' => 'localgov_subsites_parent',
      'entity_type' => 'node',
      'bundle' => 'localgov_subsites_page',
      'id' => 'node.localgov_subsites_page.localgov_subsites_parent',
      'label' => 'Parent',
    ]);
    $config->save();
  }

  /**
   * Test callback.
   */
  public function testGetFlattenedSubsiteHierarchy(): void {

    // Create subsite hierarchy.
    $subsite_overview = $this->createNode([
      'title' => $this->randomString(),
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $subsite_overview->save();
    $subsite_page1 = $this->createNode([
      'title' => $this->randomString(),
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => [
        'target_id' => $subsite_overview->id(),
        'weight' => 0,
      ],
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $subsite_page1->save();
    $subsite_page2 = $this->createNode([
      'title' => $this->randomString(),
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => [
        'target_id' => $subsite_overview->id(),
        'weight' => 0,
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $subsite_page2->save();
    $subsite_page3 = $this->createNode([
      'title' => $this->randomString(),
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => [
        'target_id' => $subsite_page2->id(),
        'weight' => 0,
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $subsite_page3->save();

    $this->assertCount(4, $this->getFlattenedSubsiteHierarchy($subsite_overview));
    $this->assertCount(4, $this->getFlattenedSubsiteHierarchy($subsite_page1));
    $this->assertCount(4, $this->getFlattenedSubsiteHierarchy($subsite_page2));
    $this->assertCount(4, $this->getFlattenedSubsiteHierarchy($subsite_page3));
  }

}
