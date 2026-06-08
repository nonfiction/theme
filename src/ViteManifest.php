<?php

namespace Nonfiction\Theme;

class ViteManifest
{
  public $head;
  public $body;
  public $blocks;
  public $editor;
  public $admin;

  // Load the manifest as soon as the wrapper is created.
  public function __construct($manifest_path)
  {
    $this->load($manifest_path);
  }

  // Read the Vite manifest into asset group properties.
  public function load($manifest_path)
  {
    $manifest = json_decode(file_get_contents($manifest_path));

    $this->head = $manifest->head ?? null;
    $this->body = $manifest->body ?? null;
    $this->blocks = $manifest->blocks ?? null;
    $this->editor = $manifest->editor ?? null;
    $this->admin = $manifest->admin ?? null;
  }
}
