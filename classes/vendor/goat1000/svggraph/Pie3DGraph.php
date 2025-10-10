<?php
/**
 * Copyright (C) 2010-2022 Graham Breach
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

class Pie3DGraph extends PieGraph {

  protected $edge_class = 'Goat1000\\SVGGraph\\PieSliceEdge';
  protected $depth;

  public function __construct($w, $h, array $settings, array $fixed_settings = [])
  {
    $fs = [
      // for 100% pie the flat sides are all hidden
      'draw_flat_sides' => false,
    ];
    $fs = array_merge($fs, $fixed_settings);
    parent::__construct($w, $h, $settings, $fs);

    $this->depth = $this->getOption('depth');
  }

  protected function draw()
  {
    // modify pad_bottom to make PieGraph do the hard work
    $pb = $this->pad_bottom;
    $space = $this->height - $this->pad_top - $this->pad_bottom;
    if($space < $this->depth)
      $this->depth = $space / 2;
    $this->pad_bottom += $this->depth;
    $this->calc();
    $this->pad_bottom = $pb;

    // see if flat sides are visible
    if(is_numeric($this->end_angle)) {
      $start = fmod(deg2rad($this->start_angle), M_PI * 2.0);
      $end = fmod(deg2rad($this->end_angle), M_PI * 2.0);
      if($this->getOption('reverse')) {
        if(($end > M_PI * 0.5 && $end < M_PI * 1.5) ||
          $start < M_PI * 0.5 || $start > M_PI * 1.5)
          $this->setOption('draw_flat_sides', true);
      } else {
        if(($start > M_PI * 0.5 && $start < M_PI * 1.5) ||
          $end < M_PI * 0.5 || $end > M_PI * 1.5)
          $this->setOption('draw_flat_sides', true);
      }
    }
    return PieGraph::draw();
  }

  /**
   * Returns the SVG markup to draw all slices
   */
  protected function drawSlices($slice_list)
  {
    $edge_list = [];
    foreach($slice_list as $key => $slice) {

      $edges = $this->getEdges($slice);
      if(!empty($edges))
        $edge_list = array_merge($edge_list, $edges);
    }

    // should not be empty - that would mean no sides visible
    if(empty($edge_list))
      return parent::drawSlices($slice_list);

    usort($edge_list, function($a, $b) {
      if($a->y == $b->y)
        return 0;
      return ($a->y < $b->y ? -1 : 1);
    });

    $edges = [];
    $overlay = is_array($this->getOption('depth_shade_gradient'));
    foreach($edge_list as $edge) {

      $edges[] = $this->getEdge($edge, $this->x_centre, $this->y_centre,
        $this->depth, $overlay);
    }

    return implode($edges) . parent::drawSlices($slice_list);
  }

  /**
   * Returns the edges for the slice that face outwards
   */
  protected function getEdges($slice)
  {
    $edges = $this->edge_class::getEdges($this, $slice, $this->s_angle,
      $this->getOption('draw_flat_sides'));
    return $edges;
  }

  /**
   * Returns an edge markup
   */
  protected function getEdge($edge, $x_centre, $y_centre, $depth, $overlay)
  {
    $item = $edge->slice['item'];
    $attr = [
      'fill' => $this->getColour($item, $edge->slice['colour_index'],
        $this->dataset, false, false),
      'id' => $this->newID(),
    ];
    if($this->getOption('show_tooltips'))
      $this->setTooltip($attr, $item, $this->dataset, $item->key, $item->value, true);
    if($this->getOption('show_context_menu'))
      $this->setContextMenu($attr, $this->dataset, $item, true);
    $this->addLabelClient($this->dataset, $edge->slice['original_position'], $attr);

    $content = '';

    // the gradient overlay uses a clip-path
    if($overlay) {
      $clip_id = $this->newID();
      $this->defs->add($edge->getClipPath($this, $x_centre, $y_centre, $depth,
        $clip_id));

      // fill without stroking
      $attr['stroke'] = 'none';
      $content = $edge->draw($this, $x_centre, $y_centre, $depth, $attr);

      // overlay
      $content .= $this->getEdgeOverlay($x_centre, $y_centre, $depth, $clip_id, $edge);

      // stroke without filling
      unset($attr['stroke']);
      $attr['fill'] = 'none';
    }
    $content .= $edge->draw($this, $x_centre, $y_centre, $depth, $attr);
    return $this->getLink($item, $item->key, $content);
  }

  /**
   * Overlays the gradient on the pie sides
   */
  protected function pieExtras()
  {
    // removed the overlay code because it drew over the stroked edges -
    // overlays are always drawn separately now
    return '';
  }

  /**
   * Returns the gradient overlay
   */
  protected function getEdgeOverlay($x_centre, $y_centre, $depth, $clip_path, $edge)
  {
    // use radius of whole pie unless slice values are set
    $radius_x = $this->radius_x;
    $radius_y = $this->radius_y;
    if($edge->slice['radius_x'] && $edge->slice['radius_y']) {
      $radius_x = $edge->slice['radius_x'];
      $radius_y = $edge->slice['radius_y'];
    }

    // clip a rect to the edge shape
    $rect = [
      'x' => $x_centre - $radius_x,
      'y' => $y_centre - $radius_y,
      'width' => $radius_x * 2.0,
      'height' => $radius_y * 2.0 + $this->depth + 2.0,
      'clip-path' => 'url(#' . $clip_path . ')',
    ];

    $gradient_id = $this->defs->addGradient($this->getOption('depth_shade_gradient'));
    if($edge->curve()) {
      $rect['fill'] = 'url(#' . $gradient_id . ')';
    } else {
      $a = $edge->angle();
      if($a > M_PI)
        $a -= M_PI;

      $pos = 50 * cos($a);
      if($pos < 0)
        $pos = 100 + $pos;

      $fill = $this->defs->getGradientColour($gradient_id, $pos);
      $opacity = $fill->opacity();

      if($opacity < 0.01 || $fill->isNone())
        return '';
      if($opacity < 0.99)
        $rect['opacity'] = $opacity;
      $rect['fill'] = $fill;
    }
    return $this->element('rect', $rect);
  }
}

