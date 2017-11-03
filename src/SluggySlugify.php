<?php

namespace Drupal\sluggy;

use Cocur\Slugify\Slugify;


/**
 * Class SluggySlugify.
 */
class SluggySlugify extends Slugify{

  protected $text;
  protected $separator;

  /**
   * Constructs a new SluggySlugify object.
   */
  public function __construct() {

  }
}