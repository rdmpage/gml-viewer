<?php

// one tile

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once (dirname(__FILE__) . '/port.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root', '' , 'bigtree');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

// coordinates of this tile
$zoom 	= $_GET['z'];
$x 		= $_GET['x'];
$y 		= $_GET['y'];

$tilesize = 256;

// zoom-specific variables
$scale = pow(2, $zoom);
$tile_extent = $tilesize/$scale;

/*
define('NORMAL_FONT', 14.0);

// 10px in terms of current coordinates
$normal_font_size = NORMAL_FONT / $scale;
*/

$origin_x = $x * $tile_extent;
$origin_y = $y * $tile_extent;
	
$p = array(
	$origin_x . ' ' . $origin_y,
	$origin_x . ' ' . ($origin_y + $tile_extent),
	($origin_x + $tile_extent) . ' ' . ($origin_y + $tile_extent),
	($origin_x + $tile_extent) . ' ' . $origin_y,
	$origin_x . ' ' . $origin_y
	);	

// get edges that intersect this tile
$sql = 'SELECT AsText(g) AS wkt FROM graph WHERE type="edge" AND MBRIntersects(GeomFromText(\'POLYGON(('. join(",", $p) . '))\'),g)';

//echo $sql . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$port = new SVGPort("tile" . $i, $tilesize, $tilesize );

// start picture, put everything into a group that we then translate to match the local tile coordinates
$port->StartPicture();
$port->StartGroup('scale(' . $scale . ') translate(' . -$origin_x . ',' . -$origin_y .')');

// debugging, show tile  border
if (0)
{
	$port->DrawRect(
		array('x' => $origin_x, 'y' => $origin_y),
		array('x' => $origin_x + $tile_extent, 'y' => $origin_y + $tile_extent)
		);
}


// draw edges that intersect this tile
while (!$result->EOF) 
{	
	$g = $result->fields['wkt'];

	// lines
	if (preg_match('/LINESTRING\((?<x1>\d+(\.\d+)?)\s+(?<y1>\d+(\.\d+)?),(?<x2>\d+(\.\d+)?)\s+(?<y2>\d+(\.\d+)?)\)/', $g, $m))
	{
		$port->DrawLine(
			array('x' => $m['x1'], 'y' => $m['y1']),
			array('x' => $m['x2'], 'y' => $m['y2'])
			);		
	}

	$result->MoveNext();
}

if (0)
{
	// draw text labels whose bounding rect intersects with this tile

	$sql = 'SELECT label, AsText(g) AS wkt FROM graph WHERE type="node" AND MBRIntersects(GeomFromText(\'POLYGON(('. join(",", $p) . '))\'),g) and zoom BETWEEN ' . ($zoom - 1) . ' AND ' . ($zoom + 1);

	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	// draw edges that intersect this tile
	while (!$result->EOF) 
	{	
		$g = $result->fields['wkt'];

		//echo $g . "\n";

		// polygon
		if (preg_match('/POLYGON\(\((?<x1>\d+(\.\d+)?)\s+(?<y1>\d+(\.\d+)?),(?<x2>\d+(\.\d+)?)\s+(\d+(\.\d+)?),(\d+(\.\d+)?)\s+(?<y2>\d+(\.\d+)?)/', $g, $m))	
		{
			//print_r($m);
		
		
			$h = $m['y2'] - $m['y1'];
		
			$w = strlen($result->fields['label']) * $h;

			if (0)
			{
				$port->DrawRect(
					array('x' => $m['x1'], 'y' => $m['y1']),
					array('x' => $m['x2'], 'y' => $m['y2'])
					//array('x' => $m['x1'] + $w, 'y' => $m['y1'] + $h)
					);
			}
			
			if (0)
			{
				$port->DrawText(
					array('x' => $m['x1'], 'y' => $m['y2']),
					$result->fields['label'],
					$m['y2'] - $m['y1']
				);
			}
		}

		$result->MoveNext();
	}
}

$port->EndGroup();

header("Content-type: image/svg+xml");
// Cache for performance
//header("Cache-control: max-age=3600");
echo $port->GetOutput();
exit();

?>
