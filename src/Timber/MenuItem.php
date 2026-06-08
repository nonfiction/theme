<?php

namespace Nonfiction\Theme\Timber;

class MenuItem extends \Timber\MenuItem
{
  private $_children = null;
  private $_current_item_parent = null;
  private $_current_item_ancestor = null;

  // Return menu item children.
  public function children()
  {
    if ($this->_children === null) {
      $this->_children = parent::children() ?? [];
    }

    return $this->_children;
  }

  // Check regular menu item parentage.
  public function current_item_parent()
  {

    if ($this->_current_item_parent === null) {
      if ($this->current_item_parent) {
        $this->_current_item_parent = true;
      }

      if ($this->_current_item_parent === null) {
        $this->_current_item_parent = false;
      }
    }

    return $this->_current_item_parent;
  }

  // Check regular menu item ancestry.
  public function current_item_ancestor()
  {

    if ($this->_current_item_ancestor === null) {
      if ($this->current_item_ancestor) {
        $this->_current_item_ancestor = true;
      }

      if ($this->_current_item_ancestor === null) {
        $this->_current_item_ancestor = false;
      }
    }

    return $this->_current_item_ancestor;
  }

  // Derive menu item classes from Timber state.
  public function get_classes()
  {
    $classes = [ 'leaf' ];
    $classes[] = 'menu-' . $this->slug;
    if ($this->current) {
      $classes[] = 'is-current';
    }
    if ($this->current_item_parent()) {
      $classes[] = 'is-parent';
    }
    if ($this->current_item_ancestor()) {
      $classes[] = 'is-ancestor';
    }
    if (count(array_intersect(['is-current', 'is-parent', 'is-ancestor'], $classes)) > 0) {
      $classes[] = 'is-open';
    }

    return array_unique($classes);
  }

  // Alias to children method
  public function get_items()
  {
    return $this->children();
  }

  // Alias to children method
  public function items()
  {
    return $this->children();
  }
}
