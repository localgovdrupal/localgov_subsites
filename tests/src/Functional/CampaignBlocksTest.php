<?php

namespace Drupal\Tests\localgov_campaigns\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests user blocks.
 *
 * @group localgov_campaigns
 */
class CampaignBlocksTest extends BrowserTestBase {

  use NodeCreationTrait;
  use TestFileCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_campaigns',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

    $this->adminUser = $this->drupalCreateUser(['administer blocks', 'edit any localgov_campaigns_overview content']);
  }

  /**
   * Test banner block.
   */
  public function testCampaignBannerBlock() {
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_campaign_banner');

    // Create some nodes.
    $overview_title = $this->randomMachineName(8);
    $overview = $this->createNode([
      'title' => $overview_title,
      'type' => 'localgov_campaigns_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    // Would be good to work out how to upload images without submitting a form.
    $image = current($this->getTestFiles('image'));
    $edit['files[field_banner_0]'] = \Drupal::service('file_system')->realpath($image->uri);
    $this->drupalPostForm('/node/' . $overview->id() . '/edit', $edit, 'Save');
    $page_title = $this->randomMachineName(8);
    $page = $this->createNode([
      'title' => $page_title,
      'type' => 'localgov_campaigns_page',
      'status' => NodeInterface::PUBLISHED,
      'field_campaign' => ['target_id' => $overview->id()],
    ]);
    $article = $this->createNode([
      'title' => 'Test article',
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->drupalLogout();

    // Test campaign overview.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-campaign-banner');
    $this->assertSession()->responseContains('<h1>' . $overview_title . '</h1>');
    $this->assertSession()->responseContains($image->filename);

    // Test campaign page.
    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-campaign-banner');
    $this->assertSession()->responseContains('<h1>' . $page_title . '</h1>');
    $this->assertSession()->responseContains('<h2>' . $page_title . '</h2>');
    $this->assertSession()->responseContains($image->filename);

    // Test article.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-campaign-banner');
    $this->assertSession()->responseNotContains($image->filename);
  }

  /**
   * Test navigation block.
   */
  public function testCampaignNavigationBlock() {
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_campaign_navigation');
    $this->drupalLogout();

    // Create some nodes.
    $overview_title = $this->randomMachineName(8);
    $overview = $this->createNode([
      'title' => $overview_title,
      'type' => 'localgov_campaigns_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $page1_title = $this->randomMachineName(8);
    $page1 = $this->createNode([
      'title' => $page1_title,
      'type' => 'localgov_campaigns_page',
      'status' => NodeInterface::PUBLISHED,
      'field_campaign' => ['target_id' => $overview->id()],
    ]);
    $page2_title = $this->randomMachineName(8);
    $page2 = $this->createNode([
      'title' => $page2_title,
      'type' => 'localgov_campaigns_page',
      'status' => NodeInterface::PUBLISHED,
      'field_campaign' => ['target_id' => $overview->id()],
    ]);
    $article = $this->createNode([
      'title' => 'Test article',
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Test campaign overview.
    $xpath = '//ul[@class="navigation-links"]/li';
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-campaign-navigation');
    $results = $this->xpath($xpath);
    $this->assertEquals(3, count($results));
    $this->assertContains($overview_title, $results[0]->getText());
    $this->assertNotContains($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertContains($page1_title, $results[1]->getText());
    $this->assertContains($page1->toUrl()->toString(), $results[1]->getHtml());
    $this->assertContains($page2_title, $results[2]->getText());
    $this->assertContains($page2->toUrl()->toString(), $results[2]->getHtml());

    // Test campaign page.
    $this->drupalGet($page1->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-campaign-navigation');
    $results = $this->xpath($xpath);
    $this->assertEquals(3, count($results));
    $this->assertContains($overview_title, $results[0]->getText());
    $this->assertContains($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertContains($page1_title, $results[1]->getText());
    $this->assertNotContains($page1->toUrl()->toString(), $results[1]->getHtml());
    $this->assertContains($page2_title, $results[2]->getText());
    $this->assertContains($page2->toUrl()->toString(), $results[2]->getHtml());

    // Test article.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-campaign-navigation');
    $this->assertSession()->pageTextNotContains($overview_title);
    $this->assertSession()->pageTextNotContains($page1_title);
    $this->assertSession()->pageTextNotContains($page2_title);

    // Test hide sidebar field.
    $overview->set('field_hide_sidebar', ['value' => 1]);
    $overview->save();
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-campaign-navigation');
    $this->drupalGet($page1->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-campaign-navigation');
  }

}
