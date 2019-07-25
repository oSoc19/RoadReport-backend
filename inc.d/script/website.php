<?php
	global $settings;
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
	<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-nicepurple">
		<div class="container">
			<a class="navbar-brand" href="/">Road Report</a>
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
							<a href="#map" class="dropdown-item">{{MENU_MAP}}</a>
						</div>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#about">{{MENU_ABOUT}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#contact">{{MENU_CONTACT}}</a>
					</li>
					<?=$settings['download']['visible']?'<li class="nav-item">
						<a href="#download"><button>{{MENU_DOWNLOAD}}</button></a>
					</li>':''?>
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
				<input type="hidden" name="lon" />
				<input type="hidden" name="lat" />
				<input type="hidden" name="city"/>
				<table cellpadding="0" cellspacing="0" style="width: 100%">
				<tr>
					<td><label for="street">{{STREET}}</label></td>
					<td width="92"><label for="numer">{{HOUSENUMBER}}</label></td>
				</tr>
				<tr style="position: relative;">
					<td><input type="text" name="street" autocomplete="off" style="width: 100%" /><div id="street_search_result"></div></td>
					<td><input type="text" name="number" style="width: 92px; margin-left: 2px;" /></td>
				</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend>{{COMMENT}}</legend>
				<input type="text" name="comment"/>
			</fieldset>
			<fieldset>
				<legend>{{PICTURE}}</legend>
				<input type="file" name="picture" accept="image/*" />
			</fieldset>
			<input type="submit" name="submit" value="{{SEND_REPORT}}"/>
		</form>
	<?php if ($settings['download']['visible']) : ?>
		<center style="margin-top: 64px;">
			<a href="<?=$settings['download']['appstore']?>" target="_blank"><img src="image/assets/itunes_store_logo.svg" height="48" /></a>
			<a href="<?=$settings['download']['playstore']?>" target="_blank"><img src="image/assets/google_play_logo.svg" height="48" /></a>
		</center>
	<?php endif; ?>
	</section>
	<section id="statistics">
		<div class="container">
			<h1 class="title">
				{{TITLE_STATS}}
			</h1>
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
				charts = new Chart(ctxs, {
					type: 'bar',
					data: {
						labels: [],
						datasets: [{
							label: '# of Problems',
							data: [],
							backgroundColor: "#51B5CD"
						}]
					},
					options: {
						legend: {
							display: false
						}
					}
				});
				chartp = new Chart(ctxp, {
					type: 'doughnut',
					data: {
						labels: [],
						datasets: [{
							label: '# of Problems',
							data: [],
							backgroundColor: colors
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
				var chrp, chrs;
				chrp = new XMLHttpRequest();
				chrs = new XMLHttpRequest();
				chrp.open("GET", "/stats/problem/month");
				chrs.open("GET", "/stats/street/month");
				chrp.onreadystatechange =  _ => {
					if (chrp.readyState==4&&chrp.status==200) {
						if (c=JSON.parse(chrp.response)) {
							let data = c['content']['data'];
							chartp.data = {
								labels: data.map(p => p.tag_name),
								datasets: [{
									label: '# of Problems',
									data: data.map(p => p.nb_problem),
									backgroundColor: colors
								}]
							};
							chartp.update();
						}
					}
				}
				chrs.onreadystatechange =  _ => {
					if (chrs.readyState==4&&chrs.status==200) {
						if (c=JSON.parse(chrs.response)) {
							let data = c['content']['data'];
							charts.data = {
								labels: data.map(p => p.street),
								datasets: [{
									label: '# of Problems',
									data: data.map(p => p.nb_report),
									backgroundColor: "#51B5CD"
								}]
							};
							charts.update();
						}
					}
				}
				chrp.send();
				chrs.send();
			</script>
		</div>
		<div id="map"></div>
		<script type="text/javascript">
			mapboxgl.accessToken = '<?=API::getAPIKey('mapbox')?>';
			var map = new mapboxgl.Map({
				container: 'map',
				style: 'mapbox://styles/la179331/cjy5kdw570s601cpkb740uatr',
				center: [3.727194, 51.056457],
				zoom: 10.5
			});
			var xmap = new XMLHttpRequest();
			xmap.open("GET", "/problem/map");
			xmap.onreadystatechange=_=>{
				if (xmap.readyState==4&&xmap.status==200){
					if (d = JSON.parse(xmap.response)) {
						for (let p of d) {
							if (p.longitude <= 0 || p.latitude <= 0)
								continue;
							let m = new mapboxgl.Marker({
								color: p.status=='REPORTED'?'red':'blue'
							});
							m.setLngLat([p.longitude, p.latitude]);
							m.addTo(map);
						}
					}
				}
			};
			xmap.send();
		</script>
	</section>
	<section id="about">
		<h1 class="title">{{TITLE_ABOUT}}</h1>
		<article class="container">
		<div class="row">{{CONTENT_ABOUT}}</div>
		</article>
	</section>
	<section id="contact">
		<h1 class="title">{{TITLE_CONTACT}}</h1>
		<article class="container">{{CONTENT_CONTACT}}</article>
	</section>
<?php if ($settings['download']['visible']) : ?>
	<section id="download">
		<h1 class="title">{{TITLE_DOWNLOAD}}</h1>
	</section>
<?php endif; ?>
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