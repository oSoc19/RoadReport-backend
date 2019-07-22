window.onload=_=>{
	var bhx;
	app.street.onclick=
	app.street.onkeyup=e=> {
		let t = e.target;
		if (bhx!=null)
			bhx.abort();
		bhx = new XMLHttpRequest();
		let max_lon = parseFloat(best_area['offsetX'])+parseFloat(best_area['width']),
			max_lat = parseFloat(best_area['offsetY'])+parseFloat(best_area['height']);
		let query = 
			`?text=${escape(t.value.trim())}&layers=street&sources=osm,gn`+
			`&boundary.rect.min_lon=${best_area['offsetX']}&boundary.rect.min_lat=${best_area['offsetY']}`+
			`&boundary.rect.max_lon=${max_lon}&boundary.rect.max_lat=${max_lat}&lang=`+document.documentElement.lang;
		bhx.open("GET", "/best@/autocomplete"+query);
		bhx.onreadystatechange=_=>{
			if (bhx.readyState==4&&bhx.status==200){
				if (d = JSON.parse(bhx.response)){
					let ssr = street_search_result;
					ssr.style.display = 'block';
					if (e.type=='click')
						return;
					ssr.innerText = '';
					ssr.style.top = t.offsetTop+t.offsetHeight+'px';
					ssr.style.width = t.offsetWidth+'px';
					for (let r of d['features']){
						let lon = r['geometry']['coordinates'][0],
							lat = r['geometry']['coordinates'][1];
						if (!inArea(lon, lat))
							continue;
						let rt = document.createElement('A');
						rt.dataset.lon = lon;
						rt.dataset.lat = lat;
						rt.dataset.city = r['properties']['localadmin'];
						rt.dataset.street = r['properties']['name'];
						rt.classList.add('result');
						rt.innerText = r['properties']['name']+', '+r['properties']['localadmin'];
						rt.onclick=e=>{
							let t = e.target;
							ssr.style.display = 'none';
							app.lon.value = t.dataset.lon;
							app.lat.value = t.dataset.lat;
							app.city.value = t.dataset.city;
							app.street.value = t.dataset.street;
						};
						ssr.append(rt);
					}
				}
			}
		};
		bhx.send();
	};
	var hit;
	function inArea(lon, lat) {
		if (hit==null) {
			let im = new Image;
			im.onload =_=>{
				let cv = document.createElement('CANVAS');
				cv.width = im.width;
				cv.height = im.height;
				hit = cv.getContext('2d');
				hit.drawImage(im, 0, 0);
			};
			im.src = '/image/area_cache.png';
			return false;
		}
		let dx = lon-best_area['offsetX'], dy = lat-best_area['offsetY'];
		dx*=best_area['ratio'];
		dy*=best_area['ratio'];
		if (dx < 0 || dy < 0 || dx > hit.canvas.width || dy > hit.canvas.height)
			return false;
		dy = hit.canvas.height - dy; // flip Y
		return hit.getImageData(Math.floor(dx), Math.floor(dy), 1, 1).data[0] > 0;
	}
	inArea(-1, -1); // init area
	document.body.onclick = e => {
		if (!e.target.classList.contains('result')&&e.target.name!='street')
			street_search_result.style.display='none';
	};
	app.onsubmit = e => {
		e.preventDefault();
		e.stopPropagation();
		app.submit.classList.add('load');
		let xhr = new XMLHttpRequest();
		xhr.open("POST", "/problem/send");
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange=_=>{
			if (xhr.readyState==4&&xhr.status==200){
				console.log(xhr);
			}
			switch (xhr.readyState)
			{
				//
			}
		}
		let fd = new FormData();
		fd.append('data', JSON.stringify({
			report: {
				problem : app.problem.value,
				comment : app.comment.value,
				location: {
					street : app.street.value,
					number : app.number.value,
					city   : app.city.value,
					longitude: app.lon,
					latitude : app.lat
				}
			}
		}));
		fd.append('file', app.picture.files[0], 'reported picture');
		xhr.send(fd);
		//setTimeout(_=>e.target.submit.classList.add('loading'), 2000);
		return false;
	};
	for (let lg  of document.querySelectorAll("a[data-lang]")){
		lg.onclick=e=>{
			document.cookie = "sLang="+e.target.dataset.lang;
			document.location.hash = "";
			document.location.reload();
		};
	}
	window.onscroll(); // refresh nav size on load
};
window.onscroll =_=> {
	if (nav = document.querySelector("nav"))
		nav.style.minHeight = Math.max(120-this.scrollY, 64) + 'px';
};