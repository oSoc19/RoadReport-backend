<!DOCTYPE html>
<html>
<head>
	<title>Monitor</title>
	<meta charset="utf-8"/>
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" /> 
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
	<style type="text/css">
		body {
			/*font-family: 'Helvetica', 'Sans';*/
			font-family: 'Roboto', sans-serif;
		}
		table {
			border: 4px solid #95CDAA;
			border-radius: 4px;
			box-shadow: 8px 8px 0px #FEF100;
			border-width: 0px 4px 4px 4px;
			color: #3A3A3A;
			display: inline-block;
			margin: 8px 16px;
			overflow: hidden;
			vertical-align: top;
		}
		#output {
			background: #FFF;
		}
		th {
			background: #95CDAA;
			color: #FFF;
			font-weight: normal;
			padding: 8px 16px;
			/*text-shadow: 2px 2px 0px #75A285;*/
			text-transform: uppercase;
		}
		tbody.list tr:nth-of-type(2n) {
			background: #F0F0F0;
		}
		tbody.list tr:hover {
			background: #51B5CD;
			color: #FFF;
		}
		tbody.list td {
			padding: 4px 8px;
		}
		select {
			appearance: none;
			-moz-appearance: none;
			-webkit-appearance: none;
			background: #95CDAA url('data:image/type;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAHBAMAAADOnLEXAAAAFVBMVEUAAAD///////////////////////9Iz20EAAAABnRSTlMAMO9gzxBYmf68AAAAL0lEQVR4XmMIMWBgYHZlEEpmYDBTZGBUM2BOEmAAcoEcBiAXyGEAcoEcBiAXyAEAfGQEDisLyrYAAAAASUVORK5CYII=') no-repeat right 8px center;
			border: 1px solid #FFF;
			border-radius: 3px;
			color: #FFF;
			font-size: 12pt;
			margin: -4px 0px;
			padding: 4px 28px 4px 8px;
			/*text-shadow: 2px 2px 0px #75A285;*/
		}
		canvas {
			display: block;
		}
	</style>
</head>
<body>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th colspan="5">LAST REPORTS</th>
			</tr>
			<tr>
				<th>#</th>
				<th>Problem</th>
				<th>City</th>
				<th>Street</th>
				<th>ΔT</th>
			</tr>
		</thead>
		<tbody id="output" class="list"></tbody>
	</table>
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th width="100">&nbsp;</th>
				<th>Statistics</th>
				<th align="right" width="100"><select id="stat_time">
					<option selected="">Today</option>
					<option>Week</option>
					<option>Month</option>
				</select></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3"><canvas id="stat_problem_render" height="200" width="600"></canvas><canvas id="stat_street_render" height="240" width="600"></canvas></td>
			</tr>
			<tr id="stat_output"></tr>
		</tbody>
	</table>
	<script type="text/javascript">
		var ctxs = stat_street_render.getContext('2d'/*, {alpha: false, antialias: false}*/),
			ctxp = stat_problem_render.getContext('2d'/*, {alpha: false, antialias: false}*/);
		var colors = ["#e74c3c", "#9b59b6", "#95a5a6", "#a5c63b", "#e67e22", "#3a6f81", "#345f41", "#f47cc3"];
		var last = (+new Date / 1000) - 3600*24;
		var cache_rid = []
		function getReports() {
			let xhttp = new XMLHttpRequest;
			xhttp.open('GET', "/problem/last/"+last, false);
			last = Math.round(+new Date / 1000);
			xhttp.send();
			if (xhttp.status == 200) {
				try {
					if (res = JSON.parse(xhttp.response)) {
						for (let report  of res) {
							if (cache_rid.indexOf(report.rid)>=0)
								continue;
							let tr = document.createElement("TR");
							tr.innerHTML = (`<tr><td align="right">${report.rid}</td><td>${report.problem}</td><td>${report.location.city}</td><td>${report.location.street}</td><td data-timer="${report.timestamp}">...</td></tr>`);
							output.insertBefore(tr, output.firstChild);
							cache_rid.push(report.rid);
						}
					}
				} catch (e) {}
				let timers = document.querySelectorAll("[data-timer]");
				for (let t of timers) {
					t.innerText = formatDelta(Math.round(+new Date / 1000) - t.dataset.timer);
				}
			}
			getStatsStreet();
			//getStatsProblem();
		}
		function formatDelta(seconds) {
			if (seconds < 10)
				return "now";
			else if (seconds < 60)
				return seconds+"s";
			else if (seconds < 3600)
				return Math.floor(seconds/60)+"m";
			else if (seconds/3600 < 24)
				return Math.floor(seconds/3600)+"h "+(''+Math.floor(seconds/60)%60).padStart(2, '0');
			else
				return Math.floor(seconds/(3600*24))+" days";
		}
		function getStatsStreet() {
			xhttp = new XMLHttpRequest;
			xhttp.open('GET', "/stats/street/"+stat_time.value, false);
			xhttp.send();
			if (xhttp.status == 200) {
				try {
					if (res = JSON.parse(xhttp.response)) {
						if (res['result'] == "success")
							drawStatsStreet(res['content']['data']);
						return;
					}
				} catch (e) {}
			}
			drawStatsStreet([]);
		}
		function getStatsProblem() {
			xhttp = new XMLHttpRequest;
			xhttp.open('GET', "/stats/problem/"+stat_time.value, false);
			xhttp.send();
			if (xhttp.status == 200) {
				try {
					if (res = JSON.parse(xhttp.response)) {
						if (res['result'] == "success")
							drawStatsProblem(res['content']['data']);
						return;
					}
				} catch (e) {}
			}
		}
		stat_time.onchange = () => {getStatsStreet(); getStatsProblem();};
		var osdata = [], charts;
		function drawStatsStreet(data) {
			if (data_equals(osdata, data))
				return;
			osdata = data;
			/*ctxs.fillStyle = '#FFF';
			ctxs.fillRect(0, 0, ctxs.canvas.width, ctxs.canvas.height);
			ctxs.fillStyle = '#3A3A3A';
			ctxs.textBaseline = "middle";
			ctxs.textAlign = "center";
			ctxs.font = "12pt Arial";
			if (data.length == 0) {
				ctxs.fillText("NO DATA", ctxs.canvas.width/2, ctxs.canvas.height/2);
				return;
			}
			let max = data.reduce((a, b) => Math.max(a, b.nb_report), 0),
				ow  = Math.round(data.reduce((a, b) => Math.max(a, ctxs.measureText(b.street).width), 0)+24);
			ctxs.fillText("Number of reports", ow + (ctxs.canvas.width-(ow+16))/2, ctxs.canvas.height-8);
			ctxs.strokeStyle = '#000';
			ctxs.stroke(new Path2D("M"+(ow-1)+",8L"+(ow-1)+","+(ctxs.canvas.height-16)));
			data.map((s, i) => {
				let y = 16+(ctxs.canvas.height-64)/(data.length>1?data.length-1:1)*i,
					w = (ctxs.canvas.width-(ow+16))*(s.nb_report/max),
					tw = ctxs.measureText(s.nb_report).width;
				ctxs.fillStyle = '#51B5CD';
				ctxs.fillRect(ow, y, w,16);
				ctxs.fillStyle = '#0003';
				ctxs.fillRect(ow-1 + w/2 - tw/2, y+1 , tw+8, 14);
				ctxs.fillStyle = '#FFF';
				ctxs.textAlign = "center";
				ctxs.fillText(s.nb_report, ow+w/2+3, y+9);
				ctxs.textAlign = "left";
				ctxs.fillStyle = '#3A3A3A';
				ctxs.fillText(s.street, 16, y+8);
			});*/
			if (charts == null) {
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
			} else {
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
		var opdata, chartp;
		function drawStatsProblem(data) {
			if (data == opdata)
				return;
			opdata = data;
			/*ctxp.fillStyle = '#FFF';
			ctxp.fillRect(0, 0, ctxp.canvas.width, ctxp.canvas.height);
			ctxp.fillStyle = '#3A3A3A';
			ctxp.font = "12pt Arial";
			if (data.length == 0) {
				ctxp.textBaseline = "middle";
				ctxp.textAlign = "center";
				ctxp.fillText("NO DATA", ctxp.canvas.width/2, ctxp.canvas.height/2);
				return;
			}
			let max = data.reduce((a, b) => Math.max(a, b.nb_problem), 0);
			var od = -Math.PI/3;
			ctxp.lineWidth = 40;
			data.map((p, i) => {
				let a = (p.nb_problem/max)*2*Math.PI;
				let dx = Math.cos(od+a/2)*100, 
					dy = Math.sin(od+a/2)*100;
				console.log(od, a, dx, dy);
				ctxp.textAlign = dx < 0 ? "right" : "left";
				ctxp.baseline = dy < 0 ? "bottom" : "top";
				ctxp.fillText(`${p.problem} (${p.nb_problem})`, ctxp.canvas.width/2 + dx, ctxp.canvas.height/2 + dy);
				ctxp.beginPath();
				ctxp.arc(ctxp.canvas.width/2, ctxp.canvas.height/2, 60, od, od+a);
				od+=a;
				ctxp.strokeStyle = colors[i];
				ctxp.stroke();
			});*/
			//
			if (chartp == null) {
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
			} else {
				chartp.data = {
					labels: data.map(p => p.problem),
					datasets: [{
						label: '# of Problems',
						data: data.map(p => p.nb_problem),
						backgroundColor: colors
					}]
				};
				chartp.update();
			}
		}
		function data_equals(d1, d2) {
			if (d1.length != d2.length)
				return false;
			for (var i = 0; i < d1.length; i++)
				for (let k of Object.keys(d1[i]))
					if (typeof d2[i][k] == 'undefined' || d1[i][k]!=d2[i][k])
						return false;
			return true;
		}
		setInterval(getReports, 1000);
		getReports();
		getStatsStreet();
		getStatsProblem();
	</script>
</body>
</html>