<?php
	if (!isset($_SESSION['logged'])||$_SESSION['logged']!==true)
		exit;
	if (isset($_POST['geojson'])&&isset($_SESSION['dash_token-area'])&&isset($_POST['token'])&&
		$_SESSION['dash_token-area']==$_POST['token'])
		$dash->setArea($_POST['geojson']);
	$_SESSION['dash_token-area'] = md5(uniqid());
	?>
	<form method="POST" style="display: flex">
		<h3 style="width: 200px;">Map Area</h3>
		<div style="width: calc(100%/1 - 280px/1)"></div>
		<input id="geoj" type="hidden" name="geojson" value="" />
		<input type="hidden" name="token" value="<?=$_SESSION['dash_token-area']?>"/>
		<button class="btn btn-success btn-icon" data-icon="save" >SAVE</button>
	</form>
	<div id="map"></div>
	<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.js'></script>
	<script src='https://api.tiles.mapbox.com/mapbox.js/plugins/turf/v3.0.11/turf.min.js'></script>
	<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.0.9/mapbox-gl-draw.js'></script>
	<script type="text/javascript">
		mapboxgl.accessToken = '<?=API::getAPIKey('mapbox')?>';
		var map = new mapboxgl.Map({
			container: 'map',
			style: 'mapbox://styles/la179331/cjy7ms8rd04331cpfdypsc7ff',
			center: [3.752076, 51.074765],
			zoom: 11.0
		});
		var draw = new MapboxDraw({
			displayControlsDefault: false,
			controls: {
				polygon: true,
				trash: true
			}
		});
		map.addControl(draw);
		function setInputGeoJSON() {
			geoj.value = JSON.stringify(draw.getAll());
		}
		map.on('draw.create', setInputGeoJSON);
		map.on('draw.delete', setInputGeoJSON);
		map.on('draw.update', setInputGeoJSON);
		window.addEventListener("load", _=>window.setTimeout(_=>map.resize(), 300));
		draw.set(<?=$dash->getArea()?>);
	</script>
	<link href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.css' rel='stylesheet' />
	<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-draw/v1.0.9/mapbox-gl-draw.css' type='text/css'/>
	<style type="text/css">
		section {
			display: grid;
			grid-template: 48px auto 32px / auto;
			min-height: 100vh;
		}
		#map {
			height: 100%;
			width: 100%;
		}
	</style>