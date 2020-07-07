<?php

namespace Drupal\Tests\localgov_campaigns_paragraphs\Functional;

use Drupal\Tests\paragraphs\Functional\Classic\ParagraphsTestBase;

/**
 * Tests the configuration of localgov_paragraphs.
 */
class CampaignsParagraphsAdministrationTest extends ParagraphsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_campaigns_paragraphs',
  ];

  /**
   * Tests the LocalGovDrupal core paragraph types.
   */
  public function testCampaignParagraphsTypes() {
    $this->loginAsAdmin([
      'administer paragraphs types',
    ]);

    // Check paragraph types installed.
    $this->drupalGet('/admin/structure/paragraphs_type');
    $this->assertSession()->pageTextContains('Box link (Page builder)');
    $this->assertSession()->pageTextContains('Call out box (Page builder)');
    $this->assertSession()->pageTextContains('Fact boxes (Page builder)');
    $this->assertSession()->pageTextContains('Image (Page builder)');
    $this->assertSession()->pageTextContains('Link and summary (Page builder)');
    $this->assertSession()->pageTextContains('Quote box (Page builder)');
    $this->assertSession()->pageTextContains('Text (two columns)');
    $this->assertSession()->pageTextContains('Text and image (50/50)');
    $this->assertSession()->pageTextContains('Text field (Page builder)');
    $this->assertSession()->pageTextContains('Video (Page builder)');

    // Check 'Box link' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/box_link/fields');
    $this->assertSession()->pageTextContains('Image');
    $this->assertSession()->pageTextContains('Title link');

    // Check 'Call out box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/call_out_box/fields');
    $this->assertSession()->pageTextContains('Background image');
    $this->assertSession()->pageTextContains('Body text');
    $this->assertSession()->pageTextContains('Button');
    $this->assertSession()->pageTextContains('Header text');

    // Check 'Fact box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/fact_box/fields');
    $this->assertSession()->pageTextContains('Above text');
    $this->assertSession()->pageTextContains('Background');
    $this->assertSession()->pageTextContains('Below text');
    $this->assertSession()->pageTextContains('Fact');

    // Check 'Link and summary' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/link_and_summary/fields');
    $this->assertSession()->pageTextContains('Summary');
    $this->assertSession()->pageTextContains('Title link');

    // Check 'Quote' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/quote/fields');
    $this->assertSession()->pageTextContains('Author');
    $this->assertSession()->pageTextContains('Text');

    // Check 'Text (two columns)' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/text/fields');
    $this->assertSession()->pageTextContains('Left column of text');
    $this->assertSession()->pageTextContains('Right column of text');

    // Check 'Text and image' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/text_and_image_50_50/fields');
    $this->assertSession()->pageTextContains('Image');
    $this->assertSession()->pageTextContains('Text');

    // Check 'Video' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/video_page_builder/fields');
    $this->assertSession()->pageTextContains('Video URL');
  }

}
