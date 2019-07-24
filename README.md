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
* [License](#license)
* [Contact](#contact)
* [Acknowledgements](#acknowledgements)


<!-- ABOUT THE PROJECT -->
## About The Project


### Built With
* [Bootstrap](https://getbootstrap.com)
* [mapbox](https://mapbox.com)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [BeSt@](https://github.com/oSoc19/best)


<!-- GETTING STARTED -->
## Getting Started

This is an example of how you may give instructions on setting up your project locally.
To get a local copy up and running follow these simple example steps.

### Prerequisites

Basically you need to have a NGINX (manage website and proxy), MySQL Server (store data), and PHP≥5 (process the data) installed, MapBox API Key and BeSt@ Service.

Website configuration `/etc/nginx/sites-available/roadreport.conf`:
```
server {
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

    # include your php configuration
}
```
make a symbolic link for nginx
```sh
ln -s /etc/nginx/sites-available/roadreport.conf /etc/nginx/sites-enabled/roadreport.conf
```


### Installation

1. Open your terminal and go in the website 
```sh
cd /var/wwww/html
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
			"ReportTo"	=> "< Email which receive every report >",
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

<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.

<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements
* [Open Summer of Code](https://2019.summerofcode.be)
* [TMaaS](https://drive.tmaas.eu/)
* [City of Ghent](https://visit.gent.be/fr)
* [Best Readme Templace](https://github.com/othneildrew/Best-README-Template)