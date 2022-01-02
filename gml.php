<?php

// Parse a GML file and generate SQL to support a tile-based viewer

//----------------------------------------------------------------------------------------

$filename = 'mdd17-layout.gml';

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");


$last_cmd 	= '';
$cmd		= array();

$data 		= array();
$level 		= 0;

$max_x 		= 0;
$max_y 		= 0;

$nodes 	= array();
$edges 	= array();
$out 	= array();

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	switch ($line)
	{
		case 'edge':
		case 'graph':
		case 'graphics':
		case 'LabelGraphics':
		case 'Line':
		case 'node':
			$last_cmd = $line;
			break;
			
		case '[':
			$cmd[] = $last_cmd;
			$data[] = new stdclass;
			$level = count($data);
			break;
			
		case ']':
			array_pop($cmd);
			$d = array_pop($data);
			
			// node
			if (isset($d->id))
			{
				$nodes[$d->id] = $d;
			}
			
			// edge
			if (isset($d->source))
			{
				$edges[] = $d;
				
				if (!isset($out[$d->source]))
				{
					$out[$d->source] = array();
				}
				$out[$d->source][] = $d->target;
			}			
			
			if (isset($d->id) || isset($d->source))
			{
				// print_r($d);
				
				if (isset($d->x))
				{
					$max_x = max($max_x, $d->y);
				}
				if (isset($d->y))
				{
					$max_y = max($max_y, $d->y);
				}
				
			}
			break;
			
		default:
			break;
	
	}
	
	// node
	if (preg_match('/^label\s+"(.*)"$/', $line, $m))
	{
		$data[$level - 1]->label = $m[1];
	}
	if (preg_match('/^id\s+(\d+)/', $line, $m))
	{
		$data[$level - 1]->id = $m[1];
	}
	
	
	// edge
	if (preg_match('/^source\s+(\d+)/', $line, $m))
	{
		$data[$level - 1]->source = $m[1];
	}
	if (preg_match('/^target\s+(\d+)/', $line, $m))
	{
		$data[$level - 1]->target = $m[1];
	}
	
	// graphics
	if (preg_match('/^x\s+(\d+(\.\d+)?)/', $line, $m))
	{
		$data[$level - 2]->x = $m[1];
	}
	if (preg_match('/^y\s+(\d+(\.\d+)?)/', $line, $m))
	{
		$data[$level - 2]->y = $m[1];
	}
	if (preg_match('/^w\s+(\d+(\.\d+)?)/', $line, $m))
	{
		$data[$level - 2]->w = $m[1];
	}
	if (preg_match('/^h\s+(\d+(\.\d+)?)/', $line, $m))
	{
		$data[$level - 2]->h = $m[1];
	}
	
	
	
	//echo $line . "\n";
	
	//print_r($cmd);


}

//echo $max_x . "\n";
//echo $max_y . "\n";

// tile size for map viewer
$TILESIZE = 256;

// scale diagram to fit on a single tile
$scale = $TILESIZE / max($max_x, $max_y);

foreach ($nodes as $id => $node)
{
	$nodes[$id]->x *= $scale;
	$nodes[$id]->y *= $scale;
	$nodes[$id]->w *= $scale;
	$nodes[$id]->h *= $scale;			
}

// generate SQL statements for edges
foreach ($edges as $edge)
{
	// edge as simple LINESTRING
	$wkt = 'GeomFromText(\'LINESTRING(' 
		. $nodes[$edge->source]->x . ' ' . $nodes[$edge->source]->y 
		. ','
		. $nodes[$edge->target]->x . ' ' . $nodes[$edge->target]->y 
		. ')\')';
	
	$svg = '<line vector-effect="non-scaling-stroke" x1="' . $nodes[$edge->source]->x . '" y1="' . $nodes[$edge->source]->y . '"'
		. ' x2="' . $nodes[$edge->target]->x . '" y2="' . $nodes[$edge->target]->y . '" stroke="black" />';
	
	$sql = 'INSERT INTO graph(type, g) VALUES("edge", ' . $wkt . ');';
	
	echo $sql . "\n";
}

// generate SQL statements for labels (to do)
if (0)
{

	$nodes[0]->depth = 0;

	$stack = array();

	$stack[] = 0;

	$depth = 0;

	$font_size = 10;

	// 0 1
	// 1 2
	// 2 4
	// 3 8

	while(!empty($stack))
	{
		$source = array_pop($stack);
	
		//echo $nodes[$source]->depth . ' ' . $nodes[$source]->label . "\n";
	
		// need to store text label and a polygon that will enclose the text at the appropriate zoom level
	
		$len = strlen($nodes[$source]->label);
	
		// size of label on screen
		$pixel_width 	= $len * $font_size; 
		$pixel_height 	= 2 * $font_size;
	
		// size of label in 0..256 coordinate space
		$zoom 			= $nodes[$source]->depth; 
	
		// hack
		if (strtoupper($nodes[$source]->label) == $nodes[$source]->label)
		{
			$zoom = 4;
		}
	
	
		$map_extent 	= $TILESIZE * pow(2, $zoom);
		$w 				= $pixel_width / $map_extent * $TILESIZE;
		$h 				= $pixel_height / $map_extent * $TILESIZE;
	
		// label as POLYGON
		$wkt = 'GeomFromText(\'POLYGON((' 
			. $nodes[$source]->x      . ' ' . $nodes[$source]->y 
			. ','
			. ($nodes[$source]->x + $w) . ' ' . $nodes[$source]->y 
			. ','
			. ($nodes[$source]->x + $w) . ' ' . ($nodes[$source]->y + $h) 
			. ','
			. $nodes[$source]->x      . ' ' . ($nodes[$source]->y + $h) 
			. ','
			. $nodes[$source]->x      . ' ' . $nodes[$source]->y 		
			. '))\')';

		$sql = 'INSERT INTO graph(g, type, zoom, label) VALUES(' . $wkt . ', "node", ' . $nodes[$source]->depth . ', "' . addcslashes($nodes[$source]->label, '"') . '");';
	
		echo $sql . "\n";
	
		if (isset($out[$source]))
		{
			foreach ($out[$source] as $target)
			{
				$nodes[$target]->depth = $nodes[$source]->depth + 1;
		
				$stack[] = $target;
			}
		}

	}
}




?>
