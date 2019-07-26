<?php
	$dash = new Dashboard();
	$error = '';
	if (isset($_POST['username'])&&isset($_POST['password']))
	{
		if ($dash->login($_POST['username'], $_POST['password']))
		{
			$_SESSION['logged'] = true;
			header("location: /dashboard/area");
		}
		else
		{
			$error = '<div class="alert alert-danger" role="alert">The username and/or the passeword are wrong.</div>';
		}
	}
	$_SESSION['dash_token'] = 'token-'.md5(uniqid());
	if (!isset($_SESSION['logged'])||$_SESSION['logged']!==true):
	?>
<!DOCTYPE html>
<html>
<head>
	<title>Login :: Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<meta charset="utf-8"/>
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Material+Icons"/>
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<style type="text/css">
		form {
			border: 2px solid #007bff;
			border-radius: 16px;
			margin: 40px auto;
		}
		.form-icon {
			background: #007bff;
			border-radius: 100%;
			color: #FFF;
			display: block;
			font-size: 64px;
			margin: -40px auto 0px auto;
			padding: 8px;
			width: 80px;
		}
		input[type='submit'] {
			border-radius: 42px;
			font-weight: bold;
			padding: 8px 24px;
			margin-bottom: 8px;
			text-transform: uppercase;
		}
		@media (max-width: 576px) {
			form {
				border-width: 2px 0px 2px 0px;
				border-radius: 0px;
			}
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row justify-content-md-center">
			<div class="col"></div>
			<div class="w-100 d-lg-none d-xl-none"></div>
			<form method="POST" class="col">
				<i id="icon" class="form-icon material-icons">fingerprint</i>
				<?=$error?>
				<div class="form-group">
					<label for="username">Username:</label>
					<input type="text" name="username" class="form-control" required="" />
				</div>
				<div class="form-group">
					<label for="password">Password:</label>
					<input type="password" name="password" class="form-control" required="" />
				</div>
				<input type="hidden" name="token" value="<?=$_SESSION['dash_token']?>"/>
				<div class="text-center">
					<input type="submit" value="Log In" class="btn btn-primary" />
				</div>
			</form>
			<div class="w-100 d-lg-none d-xl-none"></div>
			<div class="col"></div>
		</div>
	</div>
</body>
</html>
<?php
	else: 
	$dash->isLogged(true);
	?>
<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<meta charset="utf-8"/>
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Material+Icons"/>
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<style type="text/css">
		nav {
			background: #333;
			display: grid;
			grid-template: 48px 48px 48px auto 48px/auto;
			height: 100vh;
			min-height: 100vh;
			max-height: 100vh;
			position: fixed;
			width: 300px;
		}
		nav a {
			background: #FFF0;
			color: #FFF;
			display: block;
			opacity: .8;
			max-height: 48px;
			padding: 8px 32px;
			transition: .2s;
		}
		nav a:hover {
			background: #FFF3;
			color: #FFF;
			opacity: 1;
			text-decoration: none;
		}
		nav a > * {
			vertical-align: middle;
		}
		nav .material-icons {
			font-size: 32px;
		}
		nav span {
			margin-left: 8px;
		}
		.nav-spacer {
			height: 100%;
			width: 100%;
		}
		#board {
			margin-left: 300px;
			padding: 0px 32px;
		}
		.table-action {
			text-align: center;
		}
		.table-action > * {
			vertical-align: middle;
		}
		.table-action label {
			cursor: pointer;
			margin: 0px;
		}
		.table-action input {
			background: none;
			border: none;
			font-size: 24pt;
			margin: 0px;
			padding: 0px;
		}
		.btn-icon {
			padding-left: 32px;
			position: relative;
		}
		.btn-icon::before {
			content: attr(data-icon);
			left: 16px;
			position: absolute;
			top: 50%;
			transform: translate(-50%, -50%);
			font-family: 'Material Icons';
			font-weight: normal;
			font-style: normal;
			font-size: 24px;
			line-height: 1;
			letter-spacing: normal;
			text-transform: none;
			display: inline-block;
			white-space: nowrap;
			word-wrap: normal;
			direction: ltr;
			-moz-font-feature-settings: 'liga';
			-moz-osx-font-smoothing: grayscale;
		}
		.btn-text {
			background: none;
			border: none;
			opacity: .65;
		}
		.btn-text:hover {
			opacity: 1;
		}
		@media (max-width: 768px) {
			nav {
				width: 56px;
			}
			nav a {
				padding: 8px;
			}
			nav span {
				display: none;
			}
			#board {
				grid-template: 48px auto 8px/auto;
				margin-left: 56px;
				padding: 0px 8px;
			}
		}
	</style>
</head>
<body>
	<nav>
		<a href="/dashboard/area"><i class="material-icons">map</i><span>Map Area</span></a>
		<a href="/dashboard/access"><i class="material-icons">lock_open</i><span>API Access</span></a>
		<a href="/dashboard/params"><i class="material-icons">settings</i><span>Parameters</span></a>
		<div class="nav-spacer"></div>
		<a href="/dashboard/logout"><i class="material-icons">exit_to_app</i><span>Log Out</span></a>
	</nav>
	<section id="board">
	<?php
		if (!isset($path[1]))
			$path[1] = 'area';
		switch (strtolower($path[1])) {
			case 'access':
				include 'dashboard-access.php';
				break;
			case 'params':
				include 'dashboard-params.php';
				break;
			case 'logout':
				header("location: /dashboard");
				session_unset();
				session_destroy();
				break;
			default:
				include 'dashboard-area.php';
				break;
		}
		?>
	</section>
</body>
</html>
<?php endif;?>