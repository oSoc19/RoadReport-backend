<br />
<p align="center">
	<h3 align="center">Road Report (Server Part)</h3>
	<p align="center">This part of the project includes the API and the website
		<br />
		<br />
		<a href="https://github.com/oSoc19/RoadReport">Application</a>
		.
		<a href="https://roadreport.osoc.be/">View Demo</a>
		.
		<a href="https://github.com/oSoc19/RoadReport-backend/issues">Report Bug</a>
		.
		<a href="https://github.com/oSoc19/RoadReport-backend/issues">Request Feature</a>
	</p>
</p>

<!-- TABLE OF CONTENTS -->
## Table of Contents

* [About the Project](#about-the-project)
  * [Built With](#built-with)
* [Getting Started](#getting-started)
  * [Prerequisites](#prerequisites)
  * [Installation](#installation)
* [Usage](#usage)
* [Contact](#contact)
* [License](#license)
* [Acknowledgements](#acknowledgements)


<!-- ABOUT THE PROJECT -->
## About The Project
[![Road Report][product-screenshot]](https://roadreport.osoc.be/om)

<p>[![Product Name Screen Shot][product-screenshot]](https://example.com)</p>

### Built With
* [Bootstrap](https://getbootstrap.com)
* [mapbox](https://mapbox.com)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [BeSt@](https://github.com/oSoc19/best)
* [love](https://en.wikipedia.org/wiki/Love)


<!-- GETTING STARTED -->
## Getting Started

This is an example of how you may give instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

Basically you need to have a NGINX server(manage website and proxy), MySQL Server (store data), and PHP≥5 (process the data) installed, MapBox API Key and BeSt@ Service.

Website configuration `/etc/nginx/sites-available/roadreport.conf`:
```
server {
	listen 80;
    server_name  example.com www.example.com; #your domain(s)
    root         /var/www/html; # directory of the roadreport website
    index        index.php index.html index.htm;

    access_log /var/log/nginx/access.roadreport.log;
    error_log /var/log/nginx/error.roadreport.log;
    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string&query=$uri;
        autoindex off;
    }

    location /best@ {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_pass https://best.osoc.be/v1/; #Address to the BeSt@ service
        proxy_redirect off;
    }

    location ^\.git {
    	deny all;
    }

    # include your php configuration
}
```
make a symbolic link for nginx
```sh
ln -s /etc/nginx/sites-available/roadreport.conf /etc/nginx/sites-enabled/roadreport.conf
```


### Installation

1. Go on your SSH server et select the directory where you want install the website and the API
```sh
cd /var/www/html
```
2. Clone the repo
```sh
git clone https://github.com/oSoc19/RoadReport-backend.git .
```
3. Install database with `setup/database.sql`
```sh
mysql -u <your-database-user> -p <your-database-name> < setup/database.sql
```
4. Remove setup folder
```sh
rm -r setup
```
5. Edit the `inc.d/settings.php` file
```php
	$settings = array(
		...

		"my" => array(
			"hostname" => "< your database host >",
			"database" => "< your database name >",
			"username" => "< your database user >",
			"password" => "< your database pass >",
			"instance" => null
		),
		"mail" => array(
			"ReportTo"	=> "< email that will receive all reports >",
			"from"		=> "< sender's mail >",
			"SMTPDebug"	=> 0,
			"isSMTP"	=> true,
			"Host"		=> "< stmp server host >",
			"SMTPAuth"	=> true,
			"Username"	=> "< stmp server user >",
			"Password"	=> "< stmp server pass >",
			"SMTPSecure"=> "ssl",
			"CharSet"	=> "UTF-8",
			"Encoding"	=> "base64",
			"Port" 		=> 465,
			"instance"	=> null
		),

		"api" => array(
			"mapbox"	=> "< your mapbox api key >"
		)
	);
```

<!-- STRUCTURE -->
## Structure

### Object Report
| Field    | Type     | Description                         | Required |
|----------|----------|-------------------------------------|----------|
| problem  | number   | The id of the problem that happened |    Yes   |
| comment  | text     | Comment describing the problem      |    Yes   |
| location | location | Object describing the location      |    Yes   |

### Object Location
| Field     | Type   | Description                                              | Required |
|-----------|--------|----------------------------------------------------------|----------|
| street    | Text   | The street where the problem is                          |    Yes   |
| number    | text   | The house number where the problem is                    |    No    |
| city      | text   | The city where the problem is                            |    Yes   |
| longitude | number | The longitude of the location (like: X.XXXXXXXXXXXXXXXX) |    No    |
| latitude  | number | The latitude of the location (like: XX.XXXXXXXXXXXXXXXX) |    No    |

### List of problems (default)
| ## | Problem                                      | ## | Problem                                     | ## | Problem                                      | ## | Problem                  |
|----|----------------------------------------------|----|---------------------------------------------|----|----------------------------------------------|----|--------------------------|
|  1 | Hole in footpath                             |  2 | Hole in bicycle path                        |  3 | Damaged footpath                             |  4 | Damaged bicycle path     |
|  5 | Unclear marking pedestrian crossing          |  6 | Unclear marking bicycle path                |  7 | Snow, frost, slipperiness                    |  8 | Glass                    |
|  9 | Blocked guiding lines for blind people       | 10 | Obstructive intake of footpath              | 11 | Obstructive intake of bicycle path           | 12 | Sign gone/moved          |
| 13 | Traffic light defect                         | 14 | Traffic light button defect                 | 15 | Problem with VMS sign                        | 16 | Bicycle counter defect   |
| 17 | Always full                                  | 18 | In need of reparation                       | 19 | Weed / trash                                 | 20 | Left bicycles            |
| 21 | Suggestion for sheltered/indoor bicycle rack | 22 | Broken cycling pump                         | 23 | Broken/empty cycling lights vending machine  | 24 | Broken repair machine    |
| 25 | Unaccessible footpath for wheelchairs        | 26 | Dangerous crossing for cyclists/pedestrians | 27 | Dangerous situation for cyclists/pedestrians | 28 | Crowdedness on footpaths |
| 29 | Missing/defect street lights                 | 30 | Other                                       |

### Example of json report
```json
{
	"report" : 
	{
		"problem" : "10",
		"comment" : "Something happened",
		"location":
		{
			"street" : "Randomstreet",
			"number" : "1-12b",
			"city"   : "Brussel",
			"longitude":  4.3496181131285745,
			"latitude" : 50.86576889787819
		}
	}
}
```

<!-- USAGE -->
## Usage
* Access to the website, just go on the dedicated domain:
	```
	GET http://example.com
	```
* Access to the dashboard (default login:: username: `moderator`; password: `TheGuardians!`)
	```
	GET /dashboard
	```
* See the list of problems for a specific day (ie: the `19th July 2019`)
	```
	GET /problem/2019-07-19?page=1
	```
* See a specific report (ie: report `#42`)
	```
	GET /problem/42
	```
* Update the status of a report (possible values: `REPORTED`, `FIXED` or `REMOVE`)
	```
	POST /problem/update
	```
	Request body (ie: The report `#12` is `fixed`)
	```json
	{
		"id"    : 12,
		"status": "FIXED"
	}
	```


<!-- LICENSE -->
## Contact


<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.

<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements
* [Open Summer of Code](https://2019.summerofcode.be)
* [TMaaS](https://drive.tmaas.eu/)
* [City of Ghent](https://visit.gent.be/fr)
* [Best README Template](https://github.com/othneildrew/Best-README-Template)



<!-- MARKDOWN LINKS & IMAGES -->
[product-screenshot]: https://raw.githubusercontent.com/oSoc19/RoadReport-backend/master/setup/web-screenshot.png