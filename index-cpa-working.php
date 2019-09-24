<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8' />
    <title>TLCPL Digital Collections Map Visualization</title>
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.2.0/mapbox-gl.js'></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.2.0/mapbox-gl.css' rel='stylesheet' />
    <style>
        body { margin:0; padding:0; }
        #map { position:absolute; top:0; bottom:0; width:100%; }
    </style>
</head>
<body>

<style>
    .mapboxgl-popup {
        max-width: 400px;
        font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
    }
</style>
<div id='map'></div>


<?php

// THIS IS CDM DIGITAL COLLECTIONS
$xmlData2 = file_get_contents('http://open.toledolibrary.org/digitization/map/xml_data_map_cpa.xml');
$xml2 = simplexml_load_string($xmlData2);
$result2 = array();
$i2=1;
$itemkey2 = '//item';

// Get the nodes and loop them
foreach ($xml2->xpath($itemkey2) as $record2) {
        $result2[] = array(
                'latitu' => (string) $record2->latitu,
                'longit' => (string) $record2->longit,
                'descri' => (string) $record2->descri,
                'title' => (string) $record2->title,
                'pointer' => (string) $record2->pointer
        );

}
/*
$stringofdata = '{
                    "type": "Feature",
                    "properties": {
                        "description": "<strong>Truckeroo</strong><p><a href=\"http://www.truckeroodc.com/www/\" target=\"_blank\">Truckeroo</a> brings dozens of food trucks, live music, and games to half and M Street SE (across from Navy Yard Metro Station) today from 11:00 a.m. to 11:00 p.m.</p>",
                        "icon": "music"
                    },
                    "geometry": {
                        "type": "Point",
                        "coordinates": [-83.539000, 41.651000]
                    }
                }';
*/
$stringofdata2 = '
                {
                    "type": "Feature",
                    "properties": {
                        "description": "<strong>100 Block of Erie Street</strong><a href=https://www.ohiomemory.org/digital/collection/p16007coll33/id/109432/rec/1><img src=http://ohiomemory.org/utils/getthumbnail/collection/p16007coll33/id/109432 /></a><br/>A black and white snapshot of the 100 block of Erie Street between Monroe Street and Jefferson Avenue in downtown Toledo, Ohio. Visible in the photo are Hellas Restaurant and Regers Church Supplies. The photo was taken by Howard MacKenzie in August of 1981.&nbsp; <a href=https://www.ohiomemory.org/digital/collection/p16007coll33/id/109432/rec/1>View Full Image Record</a>",
                        "icon": "attraction"
                    },
                    "geometry": {
                        "type": "Point",
                        "coordinates": [-83.539545, 41.651046]
                    }
                }';

$resultCount2 = count($result2)-1;    
//$resultCount = 5903;       
//  $resultCount = htmlspecialchars($_GET["number"]);
           
for ($i2=0;$i2<=$resultCount2;$i2++) {
        $latitude2 = $result2[$i2]["latitu"];
        $longitude2 = $result2[$i2]["longit"];
        $title2 = $result2[$i2]["title"];
        $title2 = str_replace('"', "", $title2);
        
        $description2 = $result2[$i2]["descri"];
//        $description = strip_tags($description, "<a>");
        
        $pointer2 = $result2[$i2]["pointer"];
        
        $url2 = "https://www.ohiomemory.org/digital/collection/p16007coll31/id/$pointer2/rec/1";
        
        $imgurl2 = "http://ohiomemory.org/utils/getthumbnail/collection/p16007coll31/id/$pointer2";
        
        $descriptioncombo2 = "$description2";
        
        $descriptioncombo2 = str_replace('"', "", $descriptioncombo2);
		$descriptioncombo2 = str_replace("'", "", $descriptioncombo2);
		
		
		$descriptioncombo2 = "<strong>$i2: $title2</strong><a href=$url2><img src=$imgurl2 /></a><br/>" . $descriptioncombo2 . "&nbsp; <a href=$url2>View Full Image Record</a>";
        
        //debugToConsole($latitude);
        //debugToConsole($longitude);
        if ($latitude2 != '') {
       		 $stringofdata2 .= ', {
                    "type": "Feature",
                    "properties": {
                        "description": "'.$descriptioncombo2.'",
                        "icon": "attraction"
                    },
                    "geometry": {
                        "type": "Point",
                        "coordinates": ['.$longitude2.', '.$latitude2.']
                    }
                }';
        //debugToConsole($stringofdata);
         }
}

debugToConsole($stringofdata2);

function debugToConsole($msg2) { 
        echo "<script>console.log(".json_encode($msg2).")</script>";
}


?>

<script>
mapboxgl.accessToken = 'pk.eyJ1IjoibGVjaGxhayIsImEiOiJjanl0NDJjYnEwMDZsM2xtaDB5dHVyYjZlIn0.mvcJD_SpLI3nzKSH1P7G2Q';

var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/lechlak/cjz0b7t6m17xv1cmomkta97o4',
    center: [-83.539, 41.651],
    zoom: 10.5
});

map.on('load', function () {
//

    // Add a layer showing the places.
    map.addLayer({
        "id": "places2",
        "type": "symbol",
        "source": {
            "type": "geojson",
            "data": {
                "type": "FeatureCollection",
                "features": [<?php echo $stringofdata2 ?>]
            }
        },
        "layout": {
            "icon-image": "{icon}-11",
            "icon-allow-overlap": true,
            "icon-ignore-placement" : true
        },
        "paint": {
	        "icon-color" : "#CD202C"
//	        "fill-color" : "#CD202C"
    	}
    });



    // When a click event occurs on a feature in the places layer, open a popup at the
    // location of the feature, with description HTML from its properties.
    map.on('click', 'places', function (e) {
        var coordinates = e.features[0].geometry.coordinates.slice();
        var description = e.features[0].properties.description;

        // Ensure that if the map is zoomed out such that multiple
        // copies of the feature are visible, the popup appears
        // over the copy being pointed to.
        while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
            coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
        }

        new mapboxgl.Popup()
            .setLngLat(coordinates)
            .setHTML(description)
            .addTo(map);
    });

    // Change the cursor to a pointer when the mouse is over the places layer.
    map.on('mouseenter', 'places', function () {
        map.getCanvas().style.cursor = 'pointer';
    });

    // Change it back to a pointer when it leaves.
    map.on('mouseleave', 'places', function () {
        map.getCanvas().style.cursor = '';
    });
});

map.addControl(new mapboxgl.NavigationControl());

</script>

</body>
</html>