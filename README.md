# GML viewer

Google maps-style viewer for GML (Graph Modeling Language) files.

![screenshot](https://github.com/rdmpage/gml-viewer/raw/main/screenshot.png)

## How it works

[Tiled map viewer](https://en.wikipedia.org/wiki/Tiled_web_map) such as Google Maps and OpenStreetMap represent large diagrams (such as maps) using tiles of 256 x 256 pixels. At zoom level 0 the whole diagram is on a single tile. At zoom level 1 the diagram covers 2 x 2 = 4 tiles, level 2 has 4 x 4 = 16 tiles and so on.

The approach taken here is to scale the entire graph to a 256 x 256 square and store the edges of the graph in a database that supports geospatial queries. For example, the line (117.61142855993,238.37540137998), (117.44018449462, 238.74852906134) is stored using [Well-known text]()https://en.wikipedia.org/wiki/Well-known_text_representation_of_geometry) (WKT)

```sql
LINESTRING(117.61142855993 238.37540137998,117.44018449462 238.74852906134)
```

For a given zoom level and tile coordinates we compute the corresponding bounding box for that tile (in 0..256 coordinates) and do a spatial query to find all elements of the graph that intersect that bounding box. These elements are then rendered in SVG. To create the tile we render these elements in SVG by scaling them to the current zoom level and translating them such that the top left corner of the bounding box has coordinates (0,0).

This approach enables us to store the graph once at a single level of resolution, then use spatial queries to extract the individual tiles required to display the graph at any zoom level.


## To use

This is a simplified overview that assumes you are running this locally and have both MySQL and a web server on your machine. 

Create a MySQL database with the following schema:

```sql
CREATE TABLE `graph` (
  `g` geometry NOT NULL,
  `type` varchar(32) DEFAULT 'edge',
  `zoom` int(11) DEFAULT '0',
  `label` varchar(255) DEFAULT NULL,
  SPATIAL KEY `g` (`g`),
  KEY `type` (`type`),
  KEY `zoom` (`zoom`),
  KEY `label` (`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

Run `php gml.php` on a GML file. This will generate SQL statements that can be imported into the MySQL database. Then point your web browser at `index.html`. 