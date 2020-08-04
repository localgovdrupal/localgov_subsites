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
    $this->assertSession()->pageTextContains('localgov_box_link');
    $this->assertSession()->pageTextContains('localgov_call_out_box');
    $this->assertSession()->pageTextContains('localgov_fact_box');
    $this->assertSession()->pageTextContains('localgov_link_and_summary');
    $this->assertSession()->pageTextContains('localgov_quote');
    $this->assertSession()->pageTextContains('localgov_video');

    // Check 'Box link' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_box_link/fields');
    $this->assertSession()->pageTextContains('localgov_image');
    $this->assertSession()->pageTextContains('localgov_link');

    // Check 'Call out box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_call_out_box/fields');
    $this->assertSession()->pageTextContains('localgov_background_image');
    $this->assertSession()->pageTextContains('localgov_body_text');
    $this->assertSession()->pageTextContains('localgov_button');
    $this->assertSession()->pageTextContains('localgov_header_text');

    // Check 'Fact box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_fact_box/fields');
    $this->assertSession()->pageTextContains('localgov_above_text');
    $this->assertSession()->pageTextContains('localgov_background');
    $this->assertSession()->pageTextContains('localgov_below_text');
    $this->assertSession()->pageTextContains('localgov_fact');

    // Check 'Link and summary' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_link_and_summary/fields');
    $this->assertSession()->pageTextContains('localgov_summary');
    $this->assertSession()->pageTextContains('localgov_link');

    // Check 'Quote' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_quote/fields');
    $this->assertSession()->pageTextContains('localgov_author');
    $this->assertSession()->pageTextContains('localgov_text_plain');

    // Check 'Video' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_video/fields');
    $this->assertSession()->pageTextContains('localgov_video');
  }

}
