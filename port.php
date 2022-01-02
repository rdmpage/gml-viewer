<?php

// Graphics port

//-------------------------------------------------------------------------------------------------
class Port
{
	var $output = '';
	var $width = 0;
	var $height = 0;
	var $element_id = 0;
	
	//----------------------------------------------------------------------------------------------
	function __construct($element_id, $width, $height)
	{
		$this->element_id 	= $element_id;
		$this->width 		= $width;
		$this->height 		= $height;
		$this->StartPicture();
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawLine($p0, $p1)
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawRect($p0, $p1)
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawCircle($pt, $r)
	{
	}	
	
	//----------------------------------------------------------------------------------------------
	function DrawPolygon($pts, $color = array())
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawText ($pt, $text)
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function GetOutput()
	{
		$this->EndPicture();
		return $this->output;
	}
	
	//----------------------------------------------------------------------------------------------
	function StartPicture ()
	{
	}

	//----------------------------------------------------------------------------------------------
	function EndPicture ()
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function StartGroup($transform = "")
	{
	}	
	
	//----------------------------------------------------------------------------------------------
	function EndGroup()
	{
	}	

	//----------------------------------------------------------------------------------------------
	function Command($cmd)
	{
	}	
	
	
	
}

//-------------------------------------------------------------------------------------------------
class CanvasPort extends Port
{
	
	
	function DrawLine($p0, $p1)
	{
		$this->output .= 'context.moveTo(' . $p0['x'] . ',' . $p0['y'] . ');' . "\n";
		$this->output .= 'context.lineTo(' . $p1['x'] . ',' . $p1['y'] . ');' . "\n";
		$this->output .= 'context.stroke();' . "\n";
	}
	
	function DrawText ($pt, $text)
	{
		$this->output .= 'context.fillText("' . $text . '", ' . $pt['x'] . ', ' . $pt['y'] . ');' . "\n";
	}
	
	function StartPicture ()
	{
		$this->output = '<script type="application/javascript">' . "\n";
		$this->output .= 'var paper = Raphael("' . $this->element_id . '", ' . $this->width . ', ' . $this->height . ');' . "\n";
	}
		
	
	function EndPicture ()
	{
		$this->output .= '</script>';
	}
	
	
}

//-------------------------------------------------------------------------------------------------
class SVGPort extends Port
{
		
	function DrawLine($p0, $p1)
	{
/*		$this->output .= '<path d="M ' 
				. $p0['x'] . ' ' . $p0['y'] . ' ' . $p1['x'] . ' ' . $p1['y'] . '" />';
*/
		$this->output .= '<line  x1="' 
				. $p0['x'] . '" y1="' . $p0['y'] . '" x2="' . $p1['x'] . '" y2="' . $p1['y'] . '"'
				
				. ' vector-effect="non-scaling-stroke" '
				. ' stroke-width="0.5"'
				
				. ' />';
				
		/*		
  <line
     style="stroke:#000000;stroke-width:1;stroke-linecap:square"
     id="line9"
     y2="10"
     x2="19.743589"
     y1="10"
     x1="390"
     vector-effect="non-scaling-stroke" />
	*/			
				
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawCircle($pt, $r)
	{
		$this->output .= '<circle  style="opacity:0.5;fill:yellow;" ' 
				. 'cx="' .$pt['x'] . '" cy="' . $pt['y'] . '" r="' . $r . '"';
		$this->output .= ' />' . "\n";
	}
	
	
	//----------------------------------------------------------------------------------------------
	function Command($cmd)
	{
		$this->output .= $cmd . "\n";
	}	

	
	//----------------------------------------------------------------------------------------------
	function DrawCircleArc($p0, $p1, $radius, $large_arc_flag = false)
	{
		/*
		$path = $this->document->createElement('path');
		
		$path->setAttribute('vector-effect', 'non-scaling-stroke');		
		
		$path_string = 'M ' 
			. $p0['x'] . ' ' . $p0['y'] // start x,y
			. ' A ' . $radius . ' ' . $radius  //
			. ' 0 ';

		if ($large_arc_flag)
		{
			$path_string .= ' 1 ';		
		}
		else
		{
			$path_string .= ' 0 ';
		}
			
		$path_string .=
			' 1 '
			. $p1['x'] . ' ' . $p1['y']; // end x,y
		
		
		$path->setAttribute('d', $path_string);		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($path);
		*/
	
		
		$this->output .= '<path fill="none" vector-effect="non-scaling-stroke" d="M ' 
			. $p0['x'] . ' ' . $p0['y'] // start x,y
			. ' A ' . $radius . ' ' . $radius  //
			. ' 0 ';
			
		if ($large_arc_flag)
		{
			$this->output .= ' 1 ';		
		}
		else
		{
			$this->output .= ' 0 ';
		}
			
		$this->output .=
			' 0 '
			. $p1['x'] . ' ' . $p1['y'] // end x,y
			. '" />' . "\n";
		
	}	
	
	//----------------------------------------------------------------------------------------------
	function DrawRect($p0, $p1, $color = array())
	{
		$this->output .= '<rect';
		
		if (count($color) > 0)
		{
			$this->output .= ' fill="rgb(' . join(",", $color) . ')"';
		}
		else
		{
			//$this->output .= ' fill="#ff0000"';
			$this->output .= ' fill="none"';
		}
		$this->output .= ' style="opacity:0.3;"'; 
		$this->output .= ' stroke="#ff0000"'; 
		$this->output .= ' stroke-width="0.5"'; 
		$this->output .= ' vector-effect="non-scaling-stroke"';

		$this->output .= ' x="' . $p0['x'] . '"';	
		$this->output .= ' y="' . $p0['y'] . '"';	
		$this->output .= ' width="' . ($p1['x'] - $p0['x']) . '"';	
		$this->output .= ' height="' . ($p1['y'] - $p0['y']) . '"';	
		$this->output .= ' />';
		
	}
	
	//------------------------------------------------------------------------------------
	function DrawPolygon($pts, $color = array())
	{
		$this->output .= '<polygon';
		
		if (count($color) > 0)
		{
			$this->output .= ' fill="rgb(' . join(",", $color) . ')"';
		}
		else
		{
			$this->output .= ' fill="#dddddd"';
		}
		
		$this->output .= ' stroke="#999999" points="';
		
		foreach ($pts as $pt)
		{
			$this->output .=  $pt['x'] . ',' . $pt['y'] . ' ';
		}
		$this->output .= '" />';
	}
	
	//------------------------------------------------------------------------------------
	function DrawText ($pt, $text, $fontsize = 0.1, $align = 'left')
	{
		//$this->output .= '<text style="fill:none;fill-opacity:1;stroke:#FFFFFF;stroke-width:4px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;alignment-baseline:middle;font-size:' . $fontsize . 'px;"';
		//$this->output .= ' x="' . $pt['x'] . '" y="' . $pt['y'] . '">' . $text . '</text>' .  "\n";
	
	
		
		//$this->output .= '<text style="color:#000000;alignment-baseline:middle;font-size:' . $fontsize . 'px;"';
		//$this->output .= ' x="' . $pt['x'] . '" y="' . $pt['y'] . '">' . $text . '</text>' .  "\n";

		$this->output .= '<text style="font-size:' . $fontsize . 'px;"';
		$this->output .= ' x="' . $pt['x'] . '" y="' . $pt['y'] . '">' . $text . '</text>' .  "\n";
		
		
/*
	switch (align)
	{
		case 'left':
			text.setAttribute('text-anchor', 'start');
			break;
		case 'centre':
		case 'center':
			text.setAttribute('text-anchor', 'middle');
			break;
		case 'right':
			text.setAttribute('text-anchor', 'end');
			break;
		default:
			text.setAttribute('text-anchor', 'start');
			break;
	}
*/		
	}
	
	//------------------------------------------------------------------------------------
	function StartPicture()
	{
		$this->output = '<?xml version="1.0" ?>
<svg xmlns:xlink="http://www.w3.org/1999/xlink" 
	xmlns="http://www.w3.org/2000/svg"
	width="' . $this->width . 'px" 
    height="'. $this->height . 'px" 
    >';	
    
    	$this->output .= '<style type="text/css">
<![CDATA[

text {
	alignment-baseline:bottom;
	text-anchor: left;
	stroke: none;
	fill: #FFFC79;
	font-family:sans-serif;
}

path {
	stroke:#000000;
	stroke-width:1;
	/*stroke-linecap:square;*/
}

rect {
	stroke: #73FDFF;
	stroke-width:0.5;
	stroke-linecap:square;
}

line {
	stroke: #73FDFF;
	stroke-width:0.5;
	stroke-linecap:square;
}

]]>
</style>';

    
    }

	//------------------------------------------------------------------------------------
	function EndPicture ()
	{		
		$this->output .= '</svg>';
	}
	
	
	//------------------------------------------------------------------------------------
	function StartGroup($transform = '')
	{
		$this->output .= '<g id="' . $this->element_id . '"';
		if ($transform != '')
		{
			$this->output .= ' transform="' . $transform . '"';
		}
		$this->output .= '>';	
	}
	
	function EndGroup()
	{
		$this->output .= '</g>';
	}
	
}

?>