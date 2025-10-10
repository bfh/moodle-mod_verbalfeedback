<?php
/**
 * Copyright (C) 2019-2020 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

namespace Goat1000\SVGGraph;

class EmptyGraph extends Graph {

  /**
   * Does nothing, no colours to set up
   */
  protected function setup()
  {
  }

  /**
   * Draws an empty graph
   */
  protected function draw()
  {
    // maybe not completely empty
    return $this->underShapes() . $this->overShapes();
  }

  /**
   * Ignore values, not used on empty graph
   */
  public function values($values)
  {
  }

  /**
   * Drawing nothing, so check nothing
   */
  protected function checkValues()
  {
  }
}

