<?php
	//
	?>
<!DOCTYPE html>
<html lang="<?=Lang::get('ISO6391')?>">
<head>
	<title>Road Report</title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<!--ogp-->
	<link rel="icon" type="image/png" href="/favicon.png" />
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="/css/global.css?t=<?=time()?>"/>
	<link rel="stylesheet" href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.css'/>
	<link rel="stylesheet" type="text/css" href="/css/fork-awesome.min.css"/>
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/chart.min.js"></script>
	<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.js'></script>
	<script type="text/javascript" src="/js/main.js?t=<?=time()?>"></script>
	<script type="text/javascript">var best_area=<?=json_encode(API::getAreaData())?>;</script>
</head>
<body>
	<nav class="navbar navbar-expand-md navbar-dark bg-niceblue">
		<div class="container">
			<a class="navbar-brand" href="/">Road report</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="menu">
				<ul class="navbar-nav mr-auto">
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="nav-item">
						<a class="nav-link" href="#report">{{MENU_REPORT}}</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="statsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{MENU_STATS}}</a>
						<div class="dropdown-menu" aria-labelledby="statsDropdown">
							<a href="#statistics" class="dropdown-item">Basic</a>
							<a href="#map" class="dropdown-item">Map</a>
						</div>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#about">{{MENU_ABOUT}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#contact">{{MENU_CONTACT}}</a>
					</li>
					<li class="nav-item">
						<a href="#download"><button>{{MENU_DOWNLOAD}}</button></a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<section id="report">
		<form id="app" method="POST">
			<fieldset>
				<legend>{{PROBLEM}}</legend>
				<?=API::get_select_tag_problem()?>
			</fieldset>
			<fieldset>
				<legend>{{ADDRESS}}</legend>
				<div style="display: flex;">
					<input type="hidden" name="lon" />
					<input type="hidden" name="lat" />
					<input type="hidden" name="city"/>
					<input type="text" name="street" placeholder="Street" autocomplete="off" />
					<div id="street_search_result"></div>
					<input type="text" name="number" placeholder="{{HOUSENUMBER}}" style="width: 92px; margin-left: 8px;" />
				</div>
			</fieldset>
			<fieldset>
				<legend>{{COMMENT}}</legend>
				<input type="text" name="comment" placeholder="{{COMMENT}}"/>
			</fieldset>
			<fieldset>
				<legend>{{PICTURE}}</legend>
				<input type="file" name="picture" accept="image/*" />
			</fieldset>
			<input type="submit" name="submit" value="{{SEND_REPORT}}"/>
		</form>
		<center style="margin-top: 64px;">
			<img src="image/assets/itunes_store_logo.svg" height="48" />
			<img src="image/assets/google_play_logo.svg" height="48" />
		</center>
	</section>
	<section id="statistics">
		<div class="container">
			<h1 class="title">{{TITLE_STATS}}</h1>
			<div class="row text-center">
				<div class="col">
					<canvas id="stat_problem_render" height="300" width="600"></canvas>
				</div>
				<div class="w-100 d-lg-none d-xl-none"></div>
				<div class="col">
					<canvas id="stat_street_render" height="300" width="600"></canvas>
				</div>
			</div>
			<script type="text/javascript">
				var ctxs = stat_street_render.getContext('2d'),
					ctxp = stat_problem_render.getContext('2d');
				var colors = ["#e74c3c", "#9b59b6", "#95a5a6", "#a5c63b", "#e67e22", "#3a6f81", "#345f41", "#f47cc3"];
				var data = [{"street":"646","nb_report":"22"},{"street":"Mietstraat","nb_report":"17"},{"street":"Ybynybuk","nb_report":"11"},{"street":"Dvdjz","nb_report":"7"},{"street":"Rbeb","nb_report":"5"},{"street":"Marsstreet","nb_report":"4"}];
				charts = new Chart(ctxs, {
					type: 'bar',
					data: {
						labels: data.map(s => s.street),
						datasets: [{
							label: '# of Problems',
							data: data.map(s => s.nb_report),
							backgroundColor: "#51B5CD"
						}]
					},
					options : {
						legend: {
							labels: {
								defaultFontFamily: "'Roboto', sans-serif"
							}
						}
					}
				});
				data = [{"problem":"Suggestion for sheltered\/indoor bicycle rack","nb_problem":"28"},{"problem":"Trash \/ Weed","nb_problem":"22"},{"problem":"Weed \/ trash","nb_problem":"9"},{"problem":"Broken repair machine","nb_problem":"8"},{"problem":"In need of reparation","nb_problem":"7"},{"problem":"Broken\/empty cycling lights vending machine","nb_problem":"6"}];
				chartp = new Chart(ctxp, {
					type: 'doughnut',
					data: {
						labels: data.map(p => p.problem),
						datasets: [{
							label: '# of Problems',
							data: data.map(p => p.nb_problem),
							backgroundColor: colors
						}]
					}
				});
			</script>
		</div>
		<div id="map"></div>
		<script type="text/javascript">
			mapboxgl.accessToken = '<?=API::getAPIKey('mapbox')?>';
			var map = new mapboxgl.Map({
				container: 'map',
				style: 'mapbox://styles/la179331/cjy5kdw570s601cpkb740uatr',
				center: [3.727194, 51.056457],
				zoom: 13.5
			});
			
		</script>
	</section>
	<section id="about">
		<h1 class="title">{{TITLE_ABOUT}}</h1>
		<article class="container">
		<div class="row">{{CONTENT_ABOUT}}</div>
		<div class="row">
			<div class="col">{{CONTENT_VISION}}</div>
			<div class="w-100 d-lg-none d-xl-none"></div>
			<div class="col">{{CONTENT_MISSION}}</div>
		</div>
		</article>
	</section>
	<section id="contact">
		<h1 class="title">{{TITLE_CONTACT}}</h1>
	</section>
	<section id="download">
		<h1 class="title">{{TITLE_DOWNLOAD}}</h1>
	</section>
	<footer>
		<div class="container">
			<div class="row">
				<div class="col">
					<p>This project is co-financed by the European Regional and Development Fund through the Urban Innovative Actions Initiative. Urban Innovative Actions is an Initiative of the European Union that provides urban areas throughout Europe with resources to test new and unproven solutions to address urban challenges. Based on article 8 of ERDF, the Initiative has a total ERDF budget of EUR 372 million for 2014-2020</p>
					<div style="background: #FFF;text-align: center;"><img src="image/assets/logo-uia_eu.png" height="120" /><img src="image/assets/logo-tmaas.svg" height="120" /></div>
				</div>
				<!--<div class="w-100 d-lg-none d-xl-none"></div>-->
				<div class="col-md-auto text-right">
					<a href="https://github.com/oSoc19/RoadReport-backend" title="GitHub" target="_blank" class="social"><i class="fa fa-github-alt" aria-hidden="true"></i></a>
					<a href="https://twitter.com/tmaas_eu" target="_blank" title="Twitter" class="social"><i class="fa fa-twitter" aria-hidden="true"></i></a>
					<a href="https://drive.tmaas.eu/" target="_blank" title="TMaaS Website" class="social"><i class="fa fa-link" aria-hidden="true"></i></a>
					<a href="https://www.linkedin.com/company/tmaas/" title="LinkedIn" target="_blank" class="social"><i class="fa fa-linkedin" aria-hidden="true"></i></a>
					<br />
					<a href="#lang" data-lang="en" title="English Version">EN</a> |
					<a href="#lang" data-lang="nl" title="Nederlandse Versie">NL</a> -
					<a href="https://drive.tmaas.eu/privacy-policy/" target="_blank">Disclaimer</a> - <a href="https://drive.tmaas.eu/privacy-policy/" target="_blank">Terms of use</a>
					<br />
					<span style="font-size:10px;">Created with <i id="heart" class="fa fa-heart" aria-hidden="true"></i> by The Guardians of the Road at <a href="https://2019.summerofcode.be/project/road-report" target="_blank">Open Summer of Code 2019</a></span>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>