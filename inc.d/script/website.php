<!DOCTYPE html>
<html>
<head>
	<title>Road Report</title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<!--viewport-->
	<!--ogp-->
	<link rel="icon" type="image/png" href="/favicon.png" />
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="/css/global.css?t=<?=time()?>"/>
	<link href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.css' rel='stylesheet' />
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/chart.min.js"></script>
	<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v1.0.0/mapbox-gl.js'></script>
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
						<a class="nav-link" href="#report">Report</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="statsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Statistics</a>
						<div class="dropdown-menu" aria-labelledby="statsDropdown">
							<a class="dropdown-item">Basic</a>
						</div>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#about">About</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#contact">Contact</a>
					</li>
					<li class="nav-item">
						<a href="#downlaod"><button>Download</button></a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<section id="report">
		<form id="app">
			<fieldset>
				<legend>Problem</legend>
				<select>
					<optgroup label="Bicycle path/footpath:">
						<option>Hole in footpath</option>
						<option>Hole in bicycle path</option>
						<option>Damaged footpath</option>
						<option>Damaged bicycle path</option>
						<option>Unclear marking pedestrian crossing</option>
						<option>Unclear marking bicycle path</option>
						<option>Snow, frost, slipperiness</option>
						<option>Glass</option>
						<option>Blocked guiding lines for blind people</option>
						<option>Obstructive intake of footpath</option>
						<option>Obstructive intake of bicycle path</option>
					</optgroup>
					<optgroup label="Signalisation:">
						<option>Sign gone/moved</option>
						<option>Traffic light defect</option>
						<option>Traffic light button defect</option>
						<option>Problem with VMS sign</option>
						<option>Bicycle counter defect</option>
					</optgroup>
					<optgroup label="Bicycle rack:">
						<option>Always full</option>
						<option>In need of reparation</option>
						<option>Weed / trash</option>
						<option>Left bicycles</option>
						<option>Suggestion for sheltered/indoor bicycle rack</option>
					</optgroup>
					<optgroup label="Cycling infrastructure:">
						<option>Broken cycling pump</option>
						<option>Broken/empty cycling lights vending machine</option>
						<option>Broken repair machine</option>
					</optgroup>
					<optgroup label="Other:">
						<option>Unaccessible footpath for wheelchairs</option>
						<option>Dangerous crossing for cyclists/pedestrians</option>
						<option>Dangerous situation for cyclists/pedestrians</option>
						<option>Crowdedness on footpaths</option>
						<option>Missing/defect street lights</option>
						<option>Other</option>
					</optgroup>
				</select>
				<!--<select placeholder="Category">
					<option value="0">Bicycle path</option>
					<option>Signalisation</option>
					<option>Bicycle rack</option>
					<option>Cycling infrastructure</option>
					<option>Other</option>
				</select>
				<select placeholder="Event"></select>-->
			</fieldset>
			<fieldset>
				<legend>Address</legend>
				<div style="display: flex;">
					<input type="text" name="street" placeholder="Street" />
					<input type="text" name="numer" placeholder="Number" style="width: 92px; margin-left: 8px;" />
				</div>
				<input type="text" name="city" placeholder="City" />
			</fieldset>
			<fieldset>
				<legend>Comment</legend>
				<input type="text" name="comment" placeholder="Comment"/>
			</fieldset>
			<input type="submit" value="Send Report"/>
		</form>
		<center style="margin-top: 64px;">
			<img src="image/assets/itunes_store_logo.svg" height="48" />
			<img src="image/assets/google_play_logo.svg" height="48" />
		</center>
	</section>
	<section id="statistics">
		<div class="container">
			<h1 class="title">Statistics</h1>
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
		<h1 class="title">About</h1>
		<article class="container">
			<div class="row">
				<div class="col text-justify">Road Report is a project powered by TMaaS and developed by the Guardian of the Road at Open Summer of Code 2019.<br />
		<br />
		Road Report is the name of an app and a website that stimulates citizens to participate in urban mobility in a different way, putting them in contact with local authorities in an easy, effective and fast way.<br />
		<br />
		Citizens can directly communicate with the city of Ghent about public mobility problems, such as potholes, broken paving slabs or loose kerb stones, in order to improve the quality of the streets, cycling paths, and roads in cities.
			</div>
			<div class="col-md-auto">
				<ul>
					<li>Human-centered and inclusive design</li>
					<li>Fast, Scalable and Reliable</li>
					<li>The holistic idea of citizenship</li>
					<li>Crowdsourcing</li>
					<li>Emotional context</li>
					<li>Open Data</li>
					<li>Open Government</li>
					<li>User-friendly</li>
					<li>Easy and effective UX</li>
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h3>Vision</h3>
				<p>Road Report’s vision is to become a new, innovative, user-centered tool that transforms citizen data and information about urban mobility problems into valuable knowledge for the local authorities of small and medium cities.</p>
			</div>
			<div class="w-100 d-lg-none d-xl-none"></div>
			<div class="col">
				<h3>Mission</h3>
				<p>Road Report app and website attempt to transform citizens’ frustrations into reliefs through the use of a service that can help many people  who usually move by bicycle or on foot.</p>
				<p>Road Report appears as an innovative and service-centered solution to collect information from the citizens affected by public mobility problems.</p>
				<p>Road Report attempts to target users who face almost everyday public mobility problems through several online and offline activities putting pressure on and challenging local authorities to act quickly and in an effective way.</p>
			</div>
		</div>
		</article>
	</section>
	<section id="contact">
		<h1 class="title">Contact</h1>
	</section>
	<section id="download">
		<h1 class="title">Download now</h1>
	</section>
	<footer></footer>
</body>
</html>