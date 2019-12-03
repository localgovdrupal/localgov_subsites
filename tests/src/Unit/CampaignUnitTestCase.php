<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\bhcc_campaign\Node\CampaignMaster;
use Drupal\bhcc_campaign\Node\CampaignSingleton;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Tests\UnitTestCase;

/**
 * Abstract test methods for bhcc_campaign.
 */
abstract class CampaignUnitTestCase extends UnitTestCase {

  /**
   * This is what we are testing.
   *
   * @var Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock
   */
  protected $testTarget;

  /**
   * Campaign Overview nodes.
   *
   * @var Drupal\bhcc_campaign\Node\CampaignMaster
   */
  protected $campaignOverviewNode10;
  protected $campaignOverviewNode100;
  protected $campaignOverviewNode200;

  /**
   * Campaign page nodes.
   *
   * @var Drupal\bhcc_campaign\Node\CampaignSingleton
   */
  protected $campaignPageNode20;
  protected $campaignPageNode30;
  protected $campaignPageNode220;

  /**
   * Generic (non campaign) Node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $genericNode;

  /**
   * Mock a simple node object.
   *
   * @param int $nid
   *   Node ID.
   * @param string $type
   *   Either 'campaign_overview' or 'campaign_page'.
   */
  protected function mockNodeObject(int $nid, string $type) {

    // Create a mock node.
    $mockNode = $this->getMockBuilder($type == 'campaign_overview' ? CampaignMaster::class : CampaignSingleton::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Add the id property.
    $mockNode->expects($this->any())
      ->method('id')
      ->willReturn($nid);

    // Add the label property.
    $mockNode->expects($this->any())
      ->method('label')
      ->willReturn('Campaign ' . ($type == 'campaign_overview' ? 'Overview ' : 'Child Page ') . $nid);

    // Add the url property.
    $mockNode->expects($this->any())
      ->method('toUrl')
      ->willReturn(Url::fromUri("entity:node/$nid"));

    // Add the cache property.
    $mockNode->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['node:' . $nid]);

    return $mockNode;
  }

  /**
   * Mock the getParent menthod on campaign child page.
   *
   * @param Drupal\bhcc_campaign\Node\CampaignSingleton $node
   *   Campaign Child Node.
   * @param Drupal\bhcc_campaign\Node\CampaignMaster $parent
   *   Campaign Parent Node.
   */
  protected function mockCampaignParent(CampaignSingleton $node, CampaignMaster $parent) {

    $node->expects($this->any())
      ->method('getParent')
      ->willReturn($parent);
  }

  /**
   * Mock field_banner Image fields.
   *
   * CampaignOverviewNode10 will have image 'public://campaign_banner_10.jpg'
   * CampaignOverviewNode200 witll not have an image set.
   */
  protected function mockBannerImageFields() {

    // Mock the file entity, only need a simplified version for the test.
    $mockBannerImageField = $this->getMockBuilder(FileFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockBannerImageField->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn((object) ['uri' => (object) ['value' => 'public://campaign_banner_10.jpg']]);

    $this->campaignOverviewNode10->expects($this->any())
      ->method('get')
      ->with('field_banner')
      ->willReturn($mockBannerImageField);

    // Mock empty image field banner, even though there is no image,
    // we still need to mock an empty file field item list.
    $mockEmptyBannerImageField = $this->getMockBuilder(FileFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->campaignOverviewNode200->expects($this->any())
      ->method('get')
      ->with('field_banner')
      ->willReturn($mockEmptyBannerImageField);
  }

  /**
   * Mock the entity reference fields on the campaign overviews.
   *
   * CampaignOverviewNode10 will have nodes 20 and 30 in field_campaign_pages.
   * CampaignOverviewNode100 will have no nodes set infield_campaign_pages.
   */
  protected function mockEntityRefFields() {

    // Add child Campaign pages to Campaign Overview 10.
    foreach ([20, 30] as $nid) {
      $mockKey = 'mockEntityRefItemForNode' . $nid;
      $nodeKey = 'campaignPageNode' . $nid;
      $$mockKey = $this->getMockBuilder(EntityReferenceItem::class)
        ->disableOriginalConstructor()
        ->getMock();
      $$mockKey->expects($this->any())
        ->method('__get')
        ->with('entity')
        ->willReturn($this->$nodeKey);
    }

    // Also add a reference to a Campaign page that no longer exists.
    $mockEntityRefItemForDeletedNode = $this->getMockBuilder(EntityReferenceItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityRefItemForDeletedNode->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn(NULL);

    $mockEntityRefList = $this->getMockBuilder(EntityReferenceFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityRefList->expects($this->any())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([
        $mockEntityRefItemForDeletedNode,
        $mockEntityRefItemForNode20,
        $mockEntityRefItemForNode30,
      ]));

    // Mock the returned value of an entity reference field.
    $mockEntityRefList->expects($this->any())
      ->method('getValue')
      ->willReturn([
        ['target_id' => $this->campaignPageNode20->id()],
        ['target_id' => $this->campaignPageNode30->id()],
      ]);
    $this->campaignOverviewNode10->expects($this->any())
      ->method('__get')
      ->with('field_campaign_pages')
      ->willReturn($mockEntityRefList);
    $this->campaignOverviewNode10->expects($this->any())
      ->method('get')
      ->with('field_campaign_pages')
      ->willReturn($mockEntityRefList);

    // Campaign Overview 100 has no child Campaign page.
    $mockEmptyEntityRefList = $this->getMockBuilder(EntityReferenceFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEmptyEntityRefList->expects($this->any())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([]));
    $mockEmptyEntityRefList->expects($this->any())
      ->method('getValue')
      ->willReturn([]);
    $this->campaignOverviewNode100->expects($this->any())
      ->method('__get')
      ->with('field_campaign_pages')
      ->willReturn($mockEmptyEntityRefList);
    $this->campaignOverviewNode100->expects($this->any())
      ->method('get')
      ->with('field_campaign_pages')
      ->willReturn($mockEmptyEntityRefList);
  }

  /**
   * Mock a generic non campaign node.
   *
   * Use for 'wrong node type' tests.
   */
  protected function mockGenericNonCampaignNode() {
    return $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Mock Drupal Entity Services.
   *
   * We require this as formatLinks uses Node::load.
   */
  protected function mockDrupalEntityServices() {

    // Create a new container object.
    $container = new ContainerBuilder();

    // Mock node storage, which Node::load will eventually call.
    $nodeStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $nodeStorage->expects($this->any())
      ->method('load')
      ->willReturnCallback(
          function (int $nid) {
            if ($nid == 10) {
              return $this->campaignOverviewNode10;
            }
            else {
              $nodeKey = 'campaignPageNode' . $nid;
              return $this->$nodeKey;
            }
          }
        );

    // Mock the entity type manager service - required by Node::load.
    $entityTypeManager = $this->getMockBuilder(EntityTypeManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('node')
      ->willReturn($nodeStorage);
    $container->set('entity_type.manager', $entityTypeManager);

    // Mock the entity type repository service - required by Node::load.
    $entityTypeRepository = $this->getMockBuilder(EntityTypeRepository::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeRepository->expects($this->any())
      ->method('getEntityTypeFromClass')
      ->willReturn('node');
    $container->set('entity_type.repository', $entityTypeRepository);

    // Let Drupal use the mock container.
    \Drupal::setContainer($container);
  }

}
